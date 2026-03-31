# Filtros de Pedidos (admin) y Reportes del Cliente

---

## Parte 1 — Filtros de Pedidos (`admin/pedidos/index.php`)

Los pedidos tienen tres filtros: **año**, **estatus** y **días en tránsito**.

---

### Bloque PHP del inicio — leer filtros y preparar datos

```php
// Leer los filtros de la URL
$anioFiltro         = (isset($_GET['anio'])          && $_GET['anio']          !== '') ? $_GET['anio']          : null;
$estatusFiltro      = (isset($_GET['estatus'])        && $_GET['estatus']        !== '') ? $_GET['estatus']        : null;
$diasTransitoFiltro = (isset($_GET['dias_transito'])  && $_GET['dias_transito']  !== '') ? $_GET['dias_transito']  : null;
```

Cada línea hace lo mismo con un parámetro distinto de la URL:
- `isset($_GET['anio'])` — verifica si el parámetro existe en la URL.
- `$_GET['anio'] !== ''` — verifica que no esté vacío (cuando el usuario deja "-- Todos --").
- Si ambas condiciones son verdaderas → guarda el valor. Si no → guarda `null`.

Cuando la URL es `/admin/pedidos/index.php` sin nada, los tres quedan en `null` y se muestran todos los pedidos.
Cuando la URL es `?anio=2025&estatus=2`, `$anioFiltro = "2025"` y `$estatusFiltro = "2"`.

---

```php
$anioActual = (int) date('Y');
$anios      = range($anioActual, 2023);
```

- `date('Y')` devuelve el año actual como string, por ejemplo `"2026"`.
- `(int)` lo convierte a entero: `2026`.
- `range(2026, 2023)` genera el array `[2026, 2025, 2024, 2023]` automáticamente.
  Cada año que pase, `date('Y')` sube solo sin tocar el código.

---

```php
$estatusOpciones = [
    1 => 'Pendiente',
    2 => 'En tránsito',
    3 => 'Entregado',
    4 => 'Cancelado',
    5 => 'Pagado'
];
```

El estatus **no es una tabla de la base de datos** — es un número entero guardado
directamente en la columna `estatus` de la tabla `pedidos`.
Este array PHP mapea ese número a un texto legible.
La clave (`1`, `2`, etc.) es el valor que se guarda en la BD y el que viajará en la URL.
El valor (`'Pendiente'`, etc.) es solo para mostrarlo en pantalla.

---

```php
$estadoTransitoOpciones = [
    1 => '0 a 7 días',
    2 => '8 a 15 días',
    3 => 'Más de 15 días'
];
```

Los días en tránsito tampoco son un campo directo — se calculan con `DATEDIFF`
entre `fechaEmbarque` y `fechaRecibo`. Este array define tres **rangos**:
- Clave `1` → pedidos que tardaron entre 0 y 7 días.
- Clave `2` → pedidos que tardaron entre 8 y 15 días.
- Clave `3` → pedidos que tardaron más de 15 días.

El número `1`, `2` o `3` es lo que la URL envía. La clase lo interpreta para
construir el `BETWEEN` correspondiente.

---

```php
$pedidos = Pedidos::mostrarTodos($anioFiltro, $estatusFiltro, $diasTransitoFiltro);
```

Llama al método de la clase pasando los tres filtros.
Si alguno es `null`, ese filtro no se aplica y se muestran todos los pedidos para ese criterio.

---

### El formulario HTML

```html
<form action="" method="GET" class="filtros contenedor">
```

- `action=""` → envía el formulario a la misma página.
- `method="GET"` → los filtros viajan por la URL (`?anio=2025&estatus=2`).

---

#### Select de año

```html
<select class="filtro-grupo" name="anio">
  <option value="">--Filtro por año--</option>
  <?php foreach($anios as $year): ?>
    <option value="<?php echo $year; ?>" <?php echo ($anioFiltro == $year) ? 'selected' : ''; ?>>
      <?php echo $year; ?>
    </option>
  <?php endforeach; ?>
</select>
```

- `name="anio"` → es la clave que aparece en la URL: `?anio=2025`.
- `foreach($anios as $year)` → recorre el array `[2026, 2025, 2024, 2023]`.
- `value="<?php echo $year; ?>"` → el valor que PHP recibirá en `$_GET['anio']`.
- `($anioFiltro == $year) ? 'selected' : ''` → si el año de la URL coincide con
  esta opción, agrega `selected` para que el formulario recuerde qué tenía elegido.

---

#### Select de estatus

```html
<select class="filtro-grupo" name="estatus">
  <option value="">--Filtro por Estatus--</option>
  <?php foreach($estatusOpciones as $id => $nombre): ?>
    <option value="<?php echo $id; ?>" <?php echo ($estatusFiltro == $id) ? 'selected' : ''; ?>>
      <?php echo $nombre; ?>
    </option>
  <?php endforeach; ?>
</select>
```

- `foreach($estatusOpciones as $id => $nombre)` → recorre el array con clave y valor.
  `$id` es el número (`1`, `2`...) y `$nombre` es el texto (`'Pendiente'`, `'Entregado'`...).
- `value="<?php echo $id; ?>"` → envía el número a la URL: `?estatus=3`.
- El texto visible en el select es `$nombre`, pero lo que PHP recibe es `$id`.

Por qué así: la base de datos guarda `3` (no `"Entregado"`), así que el `WHERE`
que se construye es `pedidos.estatus = 3`, que es lo correcto.

---

#### Select de días en tránsito

```html
<select class="filtro-grupo" name="dias_transito">
  <option value="">--Filtro por Días en Tránsito--</option>
  <?php foreach($estadoTransitoOpciones as $id => $nombre): ?>
    <option value="<?php echo $id; ?>" <?php echo ($diasTransitoFiltro == $id) ? 'selected' : ''; ?>>
      <?php echo $nombre; ?>
    </option>
  <?php endforeach; ?>
</select>
```

Funciona igual que el de estatus: envía `1`, `2` o `3` a la URL.
La clase interpreta ese número como un rango de días.

---

### El método `mostrarTodos` en `Pedidos.php`

```php
public static function mostrarTodos($anio = null, $estatus = null, $diasTransito = null) {
    $query = "SELECT * FROM pedidos WHERE 1=1";
```

`WHERE 1=1` es un truco: `1=1` siempre es verdadero, así que no filtra nada.
Su utilidad es que todos los filtros que se agreguen después pueden usar `AND`
sin importar si son el primero o el décimo.
Sin este truco tendrías que saber si el `WHERE` ya fue agregado o no.

---

```php
    if($anio) {
        $anio = intval($anio);
        $query .= " AND YEAR(pedidos.fecha) = $anio";
    }
```

- `if($anio)` → solo entra si `$anio` no es `null` ni `""` ni `0`.
- `intval($anio)` → convierte el string `"2025"` al entero `2025` y descarta
  cualquier texto malicioso (protección contra SQL injection).
- `YEAR(pedidos.fecha)` → función de MySQL que extrae el año de una fecha `YYYY-MM-DD`.
  Así `2025-03-28` se convierte en `2025` para poder compararlo.

---

```php
    if($estatus) {
        $estatus = intval($estatus);
        $query .= " AND pedidos.estatus = $estatus";
    }
```

- `pedidos.estatus` es un entero en la BD (`1`, `2`, `3`, `4` o `5`).
- La comparación es directa: `WHERE estatus = 3` muestra solo los entregados.

---

```php
    if($diasTransito) {
        $diasTransito = intval($diasTransito);
        if($diasTransito === 1) {
            $query .= " AND DATEDIFF(fechaRecibo, fechaEmbarque) BETWEEN 0 AND 7";
        } elseif($diasTransito === 2) {
            $query .= " AND DATEDIFF(fechaRecibo, fechaEmbarque) BETWEEN 8 AND 15";
        } elseif($diasTransito === 3) {
            $query .= " AND DATEDIFF(fechaRecibo, fechaEmbarque) > 15";
        }
    }
```

- `DATEDIFF(fechaRecibo, fechaEmbarque)` → función de MySQL que resta dos fechas
  y devuelve el número de días de diferencia.
  Ejemplo: `DATEDIFF('2026-03-18', '2026-03-10')` devuelve `8`.
- `BETWEEN 0 AND 7` → filtra pedidos donde esa diferencia está entre 0 y 7 días.
- `> 15` → filtra pedidos que tardaron más de 15 días.
- Se usa `===` (comparación estricta de tipo) porque `intval` devuelve un entero,
  y se quiere asegurar que `1` entero no sea igual a `true` o `"1"` string.

---

```php
    $query .= " ORDER BY pedidos.fecha DESC";
    return self::consultaSQL($query);
```

- `ORDER BY fecha DESC` → ordena de más reciente a más antiguo.
- `consultaSQL` ejecuta la query y devuelve un array de objetos `Pedidos`.

---

## Parte 2 — Filtros de Reportes del Cliente (`public/cliente/index.php`)

El cliente ve solo sus propios reportes y puede filtrar por **año** y **ubicación**.

---

### Bloque PHP del inicio

```php
$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null;
$ubicacionFiltro = (isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '') ? $_GET['ubicacion'] : null;
```

Igual que en pedidos: lee los parámetros de la URL y los convierte a `null`
si no existen o están vacíos.

---

```php
$ubicaciones = $db->query("SELECT id, nombre FROM ubicacion");
```

Las ubicaciones **sí están en la base de datos** (tabla `ubicacion`), a diferencia
del estatus de pedidos que era un array fijo.
`$db->query()` devuelve un `mysqli_result` — no un array, sino un objeto que
se recorre con `while/fetch_assoc()` en el HTML.

---

```php
$reportes = Reportes::mostrarPorCliente($_SESSION['id'], $anioFiltro, $ubicacionFiltro);
```

- `$_SESSION['id']` → el ID del cliente logueado. Siempre se filtra por él
  para que el cliente solo vea sus propios reportes.
- Los dos filtros opcionales se pasan a continuación.

---

### El formulario HTML

```html
<select class="filtro-grupo" name="ubicacion">
  <option value="">--Filtro por ubicacion--</option>
  <?php while($ubicacion = $ubicaciones->fetch_assoc()): ?>
    <option value="<?php echo $ubicacion['id']; ?>"
      <?php echo ($ubicacionFiltro == $ubicacion['id']) ? 'selected' : ''; ?>>
      <?php echo $ubicacion['nombre']; ?>
    </option>
  <?php endwhile; ?>
</select>
```

- `while($ubicacion = $ubicaciones->fetch_assoc())` → a diferencia del `foreach`,
  aquí se usa `while` porque `$ubicaciones` es un `mysqli_result`, no un array.
  `fetch_assoc()` lee una fila a la vez y devuelve `null` cuando ya no hay más,
  lo que termina el `while`.
- `$ubicacion['id']` y `$ubicacion['nombre']` → cada fila llega como array asociativo.
  Se accede con corchetes `[]`, no con `->` que sería para objetos.

---

### El método `mostrarPorCliente` en `Reportes.php`

```php
public static function mostrarPorCliente($clienteId, $anio = null, $ubicacionId = null) {
    $clienteId = intval($clienteId);

    $query = "SELECT reportes.*, ...
              FROM reportes
              LEFT JOIN usuarios ON reportes.cliente = usuarios.id
              LEFT JOIN productos ON reportes.producto = productos.id
              LEFT JOIN ubicacion ON reportes.ubicacion = ubicacion.id
              WHERE reportes.cliente = $clienteId";
```

A diferencia de `Pedidos::mostrarTodos` que usa `WHERE 1=1`, aquí el `WHERE`
ya existe y es fijo: siempre filtra por el cliente logueado.
Por eso no se necesita el truco de `1=1` — los filtros extras simplemente
se agregan con `AND` directamente.

---

```php
    if($anio) {
        $anio = intval($anio);
        $query .= " AND YEAR(reportes.fecha) = $anio";
    }

    if($ubicacionId) {
        $ubicacionId = intval($ubicacionId);
        $query .= " AND reportes.ubicacion = $ubicacionId";
    }

    $query .= " ORDER BY reportes.fecha DESC";
    return self::consultaSQL($query);
```

- `AND YEAR(reportes.fecha) = $anio` → se encadena al `WHERE` existente.
- `AND reportes.ubicacion = $ubicacionId` → la columna `ubicacion` en `reportes`
  guarda el ID numérico de la ubicación. Se compara directamente con el ID
  que el usuario seleccionó en el `<select>`.
- Ambos usan `intval()` para protección contra SQL injection.

---

## Comparación entre los dos enfoques

| | `Pedidos::mostrarTodos` | `Reportes::mostrarPorCliente` |
|---|---|---|
| WHERE base | `WHERE 1=1` (siempre verdadero) | `WHERE cliente = $id` (fijo) |
| ¿Por qué? | No hay filtro obligatorio | El cliente siempre filtra por su ID |
| Primer filtro adicional | `AND ...` (ya funciona por el `1=1`) | `AND ...` (ya funciona por el WHERE fijo) |
| Datos de selects | Arrays PHP (`$estatusOpciones`) | BD con `$db->query()` + `fetch_assoc()` |
| Filtro especial | Días en tránsito con `DATEDIFF + BETWEEN` | No tiene |

---

## ¿Cuándo usar `WHERE 1=1` vs `WHERE campo = valor`?

- Usa **`WHERE 1=1`** cuando **todos** los filtros son opcionales y ninguno es
  obligatorio. El `1=1` garantiza que el primer `AND` no rompa la sintaxis SQL.

- Usa **`WHERE campo = valor`** cuando hay **al menos una condición fija**
  (como el ID del cliente). En ese caso el `WHERE` siempre está presente y
  los filtros extras se encadenan con `AND` sin problema.
