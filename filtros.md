# Cómo funcionan los filtros en PHP + MySQL

## ¿Qué es un filtro?

Un filtro es una forma de mostrar solo los registros que cumplan una condición. Por ejemplo: "muéstrame solo los reportes del año 2025" o "muéstrame solo los reportes del cliente Juan".

El filtro viaja desde el navegador hasta la base de datos en tres pasos:

```
[Formulario HTML]  →  [PHP lee $_GET]  →  [MySQL ejecuta WHERE]
```

---

## Paso 1 — El formulario HTML

El formulario es lo que el usuario ve y usa para elegir el filtro.

```html
<form action="" method="GET">
  <select name="anio">
    <option value="">-- Todos los años --</option>
    <option value="2026">2026</option>
    <option value="2025">2025</option>
  </select>
  <button type="submit">Filtrar</button>
</form>
```

### Puntos importantes:

**`method="GET"`**
El formulario envía los datos por la URL, no por `$_POST`.
Cuando el usuario hace clic en "Filtrar", la URL queda así:
```
index.php?anio=2025
```
Esto es mejor que POST para filtros porque:
- El usuario puede copiar y pegar la URL con el filtro aplicado.
- Al recargar la página el filtro se mantiene.
- Al hacer clic en "atrás" el filtro se mantiene.

**`action=""`**
Significa que el formulario se envía a la misma página donde está.

**`name="anio"`**
El `name` del select es la clave que aparece en la URL (`?anio=2025`).
PHP la leerá con `$_GET['anio']`.

---

## Paso 2 — Generar las opciones dinámicamente con PHP

En lugar de escribir cada año a mano, PHP puede generarlos:

```php
$anioActual = (int) date('Y');     // Obtiene el año actual: 2026
$anios = range($anioActual, 2023); // Genera [2026, 2025, 2024, 2023]
```

`range($inicio, $fin)` crea un array con todos los números entre los dos valores.
Cada año que pase, `date('Y')` sube solo, así nunca hay que editar el código.

El select queda así:

```html
<select name="anio">
  <option value="">-- Todos los años --</option>
  <?php foreach($anios as $a): ?>
    <option value="<?php echo $a; ?>" <?php echo ($anioFiltro == $a) ? 'selected' : ''; ?>>
      <?php echo $a; ?>
    </option>
  <?php endforeach; ?>
</select>
```

El `selected` en el `<option>` es para que al recargar la página, el select
recuerde qué opción estaba elegida. Compara el valor de la opción con el
filtro que viene de la URL (`$anioFiltro`).

---

## Paso 3 — PHP lee los filtros de la URL

Antes de ejecutar la consulta, PHP lee los parámetros que viajan en `$_GET`:

```php
$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null;
$clienteFiltro   = (isset($_GET['cliente'])   && $_GET['cliente']   !== '') ? $_GET['cliente']   : null;
$ubicacionFiltro = (isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '') ? $_GET['ubicacion'] : null;
```

### Desglose de esa línea:

```php
$anioFiltro = (isset($_GET['anio']) && $_GET['anio'] !== '') ? $_GET['anio'] : null;
//             ──────────────────────────────────────────────  ──────────────  ────
//             Condición: ¿existe en la URL Y no está vacío?   Si sí: úsalo   Si no: null
```

- `isset($_GET['anio'])` — verifica que el parámetro `anio` exista en la URL.
- `$_GET['anio'] !== ''` — verifica que no sea una cadena vacía (cuando el usuario deja "-- Todos --").
- Si ambas condiciones son verdaderas, guarda el valor. Si no, guarda `null`.

Cuando la URL es `index.php` sin parámetros, los tres quedan en `null`.
Cuando la URL es `index.php?anio=2025`, `$anioFiltro` vale `"2025"` y los otros dos son `null`.

---

## Paso 4 — El método en la clase recibe los filtros

El método se modifica para aceptar parámetros opcionales:

```php
public static function mostrarTodos($anio = null, $clienteId = null, $ubicacionId = null) {
```

`= null` hace que cada parámetro sea **opcional**. Si no se pasa nada, funciona igual que antes.

```php
// Sin filtros — muestra todo
Reportes::mostrarTodos();

// Con un filtro
Reportes::mostrarTodos(2025);

// Con varios filtros
Reportes::mostrarTodos(2025, 3, 1);
```

---

## Paso 5 — Construir la query condicionalmente

Esta es la parte más importante. La query se construye en partes:

```php
public static function mostrarTodos($anio = null, $clienteId = null, $ubicacionId = null) {

    // Base de la query — siempre se ejecuta
    $query = "SELECT reportes.*, usuarios.nombre AS nombre_cliente, ...
              FROM reportes
              LEFT JOIN usuarios ON reportes.cliente = usuarios.id
              ...";

    // Array donde se guardan las condiciones activas
    $condiciones = [];

    // Solo se agrega si el filtro tiene valor
    if($anio) {
        $anio = intval($anio); // Convierte a entero para evitar SQL injection
        $condiciones[] = "YEAR(reportes.fecha) = $anio";
    }

    if($clienteId) {
        $clienteId = intval($clienteId);
        $condiciones[] = "reportes.cliente = $clienteId";
    }

    if($ubicacionId) {
        $ubicacionId = intval($ubicacionId);
        $condiciones[] = "reportes.ubicacion = $ubicacionId";
    }

    // Si hay condiciones, agregar WHERE + todas unidas con AND
    if(!empty($condiciones)) {
        $query .= " WHERE " . implode(" AND ", $condiciones);
    }

    $query .= " ORDER BY reportes.fecha DESC";

    return self::consultaSQL($query);
}
```

### ¿Por qué usar un array de condiciones?

Porque no sabemos cuántos filtros va a aplicar el usuario.
`implode(" AND ", $condiciones)` une todos los que existan automáticamente:

| `$condiciones` contiene | Query generada |
|---|---|
| `[]` (vacío) | Sin `WHERE` — trae todo |
| `["YEAR(fecha) = 2025"]` | `WHERE YEAR(fecha) = 2025` |
| `["YEAR(fecha) = 2025", "cliente = 3"]` | `WHERE YEAR(fecha) = 2025 AND cliente = 3` |
| Los tres | `WHERE YEAR(fecha) = 2025 AND cliente = 3 AND ubicacion = 1` |

Si no usaras el array tendrías que hacer condicionales complicadas para saber
si el `WHERE` ya fue agregado o si debes usar `AND`.

---

## Paso 6 — `YEAR()`, función de MySQL para filtrar por año

La columna `fecha` en la base de datos se guarda como `YYYY-MM-DD`, por ejemplo `2025-03-28`.
Si filtras directamente con `WHERE fecha = 2025` no funciona porque la fecha completa
no es igual al número 2025.

`YEAR(reportes.fecha)` extrae solo el año de esa columna:

```sql
-- fecha guardada: 2025-03-28
-- YEAR(fecha) devuelve: 2025
WHERE YEAR(reportes.fecha) = 2025
```

Así el usuario filtra por año sin importar el mes ni el día.

---

## Paso 7 — `intval()` para seguridad (SQL Injection)

Siempre que un valor venga de `$_GET` y se use en una query SQL,
hay que sanearlo antes. Para IDs y años usamos `intval()`:

```php
$anio = intval($anio);
// Si $anio es "2025"        → devuelve 2025  (correcto)
// Si $anio es "2025; DROP TABLE reportes" → devuelve 2025  (el texto malicioso se descarta)
```

`intval()` convierte cualquier valor a número entero, descartando todo lo que no sea un dígito.

---

## Paso 8 — Recuperar datos para llenar los selects

Los selects de cliente y ubicación necesitan los datos de la base de datos.
Se consultan al inicio de la vista, antes de llamar al template:

```php
$clientes    = $db->query("SELECT id, nombre FROM usuarios WHERE rol = 2");
$ubicaciones = $db->query("SELECT id, nombre FROM ubicacion");
```

`$db->query()` devuelve un objeto `mysqli_result`. Para recorrerlo en el HTML
se usa `while` con `fetch_assoc()`, que devuelve cada fila como array:

```html
<select name="cliente">
  <option value="">-- Todos los clientes --</option>
  <?php while($cliente = $clientes->fetch_assoc()): ?>
    <option value="<?php echo $cliente['id']; ?>"
      <?php echo ($clienteFiltro == $cliente['id']) ? 'selected' : ''; ?>>
      <?php echo $cliente['nombre']; ?>
    </option>
  <?php endwhile; ?>
</select>
```

### ¿Por qué `fetch_assoc()` y no `foreach`?

`$db->query()` no devuelve un array normal, devuelve un `mysqli_result`.
`foreach` no sabe cómo recorrerlo directamente.
`fetch_assoc()` lee una fila a la vez y devuelve un array asociativo:
`$cliente['id']`, `$cliente['nombre']`, etc.

---

## Paso 9 — El enlace "Limpiar filtros"

Es simplemente un enlace a la misma página sin parámetros:

```html
<a href="index.php">Limpiar filtros</a>
```

Al hacer clic, la URL queda `index.php` sin nada, PHP no recibe filtros,
todos quedan en `null`, y la query trae todos los registros.

---

## Resumen del flujo completo

```
1. Usuario abre index.php           → No hay filtros → Se muestran todos los reportes

2. Usuario elige año 2025           → Hace clic en Filtrar
   URL: index.php?anio=2025

3. PHP lee: $anioFiltro = "2025"    → Llama a mostrarTodos("2025", null, null)

4. En la clase:
   $condiciones[] = "YEAR(fecha) = 2025"
   $query .= " WHERE YEAR(fecha) = 2025"
   $query .= " ORDER BY fecha DESC"

5. MySQL ejecuta la query           → Devuelve solo reportes del 2025

6. PHP genera el HTML con esos datos → El select muestra "2025" como seleccionado
```

---

## Checklist para crear un filtro nuevo

- [ ] Agregar un `<select name="nombreFiltro">` al formulario en la vista.
- [ ] Cargar los datos del select con `$db->query("SELECT id, nombre FROM tabla")`.
- [ ] Leer el filtro de la URL: `$filtro = isset($_GET['nombreFiltro']) && $_GET['nombreFiltro'] !== '' ? $_GET['nombreFiltro'] : null;`
- [ ] Agregar el parámetro al método de la clase: `$nombreFiltro = null`.
- [ ] Dentro del método, agregar la condición al array: `if($nombreFiltro) { $condiciones[] = "tabla.columna = " . intval($nombreFiltro); }`
- [ ] Pasar el filtro al llamar al método: `Clase::metodo($filtro1, $filtro2)`.
- [ ] Marcar el `selected` en el option correspondiente para que el form recuerde el estado.
