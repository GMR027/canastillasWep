# Paginación de reportes en PHP + MySQL

---

## ¿Qué es la paginación y cómo funciona?

La paginación divide los resultados en bloques (páginas) para no cargar todos
los registros de una vez. En lugar de traer 500 reportes, traes 10 por página.

En SQL se hace con dos palabras clave:

```sql
SELECT * FROM reportes
ORDER BY fecha DESC
LIMIT 10 OFFSET 0
```

- **`LIMIT 10`** → trae solo 10 registros.
- **`OFFSET 0`** → empieza desde el registro 0 (el primero).

La fórmula para calcular el `OFFSET` según la página:

```
OFFSET = (página_actual - 1) × registros_por_página
```

| Página | Cálculo | OFFSET | Registros que trae |
|---|---|---|---|
| 1 | (1-1) × 10 | 0 | registros 1 al 10 |
| 2 | (2-1) × 10 | 10 | registros 11 al 20 |
| 3 | (3-1) × 10 | 20 | registros 21 al 30 |

Para saber cuántas páginas existen en total necesitas saber cuántos registros
hay en total. Para eso se usa `COUNT(*)` de MySQL (cuenta filas sin traerlas).

---

## Paso 1 — Agregar `contarTodos()` en `clases/Reportes.php`

Este método cuenta cuántos reportes coinciden con los filtros activos.
Debe ir **antes** del método `mostrarTodos`, dentro de la clase.

```php
public static function contarTodos($anio = null, $clienteId = null, $ubicacionId = null) {
    // COUNT(*) cuenta el número total de filas que coinciden con los filtros.
    // No trae los datos de los reportes, solo el número.
    $query = "SELECT COUNT(*) AS total FROM reportes";

    // Mismo sistema de condiciones que mostrarTodos
    $condiciones = [];

    if($anio) {
        $anio = intval($anio);
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

    if(!empty($condiciones)) {
        $query .= " WHERE " . implode(" AND ", $condiciones);
    }

    // Esta query no usa consultaSQL() porque solo devuelve UN número,
    // no un array de objetos Reportes.
    $resultado = self::$db->query($query);
    $fila = $resultado->fetch_assoc(); // Devuelve ['total' => 47]
    return (int) $fila['total'];       // Retorna solo el número: 47
}
```

### ¿Por qué no usar `consultaSQL()`?

`consultaSQL()` convierte cada fila en un objeto `Reportes`. Pero `COUNT(*)`
devuelve una sola fila con un solo campo `total`. No necesitas un objeto,
solo el número. Por eso se llama directo a `fetch_assoc()` y se extrae `['total']`.

---

## Paso 2 — Modificar `mostrarTodos()` en `clases/Reportes.php`

Agrega `$limite` y `$offset` como parámetros al final de la firma del método,
y agrégalos también al final de la query.

```php
// ANTES:
public static function mostrarTodos($anio = null, $clienteId = null, $ubicacionId = null) {

// DESPUÉS:
public static function mostrarTodos($anio = null, $clienteId = null, $ubicacionId = null, $limite = 10, $offset = 0) {
```

Y al final de la query, reemplaza:

```php
// ANTES:
$query .= " ORDER BY reportes.fecha DESC";

// DESPUÉS:
$limite = intval($limite); // Seguridad: convertir a entero
$offset = intval($offset); // Seguridad: convertir a entero
$query .= " ORDER BY reportes.fecha DESC LIMIT $limite OFFSET $offset";
```

### ¿Por qué `$limite = 10` y `$offset = 0` como valores por defecto?

Si alguien llama a `Reportes::mostrarTodos()` sin pasar `$limite` ni `$offset`,
el método funciona igual que antes: trae 10 registros desde el inicio.
Esto evita romper otras partes del código que ya usaban este método.

---

## Paso 3 — Calcular la paginación en `admin/index.php`

Agrega este bloque **después de leer los filtros** y **antes de llamar a `mostrarTodos`**.
Reemplaza la línea `$reportes = Reportes::mostrarTodos(...)` con todo esto:

```php
// Cuántos reportes mostrar por página
$porPagina = 10;

// Leer la página actual de la URL (?pagina=2)
// max(1, ...) evita que el usuario ponga pagina=0 o pagina=-3
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;

// Contar el total de reportes que coinciden con los filtros activos
$totalReportes = Reportes::contarTodos($anioFiltro, $clienteFiltro, $ubicacionFiltro);

// ceil() redondea hacia arriba: 23 reportes / 10 = 2.3 → 3 páginas
$totalPaginas = ceil($totalReportes / $porPagina);

// Si la página pedida es mayor al total (URL manipulada), la corregimos
if($paginaActual > $totalPaginas && $totalPaginas > 0) {
    $paginaActual = $totalPaginas;
}

// Calcular desde qué registro empezar
$offset = ($paginaActual - 1) * $porPagina;

// Obtener solo los reportes de esta página
$reportes = Reportes::mostrarTodos($anioFiltro, $clienteFiltro, $ubicacionFiltro, $porPagina, $offset);
```

### Desglose línea por línea:

**`max(1, intval($_GET['pagina']))`**
- `intval()` convierte `"2"` a `2` y descarta texto malicioso.
- `max(1, ...)` garantiza que el número sea al menos 1.
  Sin esto, si alguien pone `?pagina=0` el offset sería `-10`, rompiendo la query.

**`ceil($totalReportes / $porPagina)`**
- Si hay 23 reportes y 10 por página: `23 / 10 = 2.3` → `ceil(2.3) = 3`.
- `ceil()` siempre redondea hacia arriba porque la última página puede estar
  incompleta (tener menos de 10 registros), pero sigue siendo una página.

**`$offset = ($paginaActual - 1) * $porPagina`**
- Página 1: `(1-1) * 10 = 0` → empieza desde el primer registro.
- Página 2: `(2-1) * 10 = 10` → salta los primeros 10, empieza en el 11.

---

## Paso 4 — Botones de navegación en el HTML de `admin/index.php`

Agrega esto **después del cierre de `</table>`** y **antes de `</section>`**:

```html
<div class="paginacion">

  <?php if($paginaActual > 1): ?>
    <a href="?pagina=<?php echo $paginaActual - 1; ?>&anio=<?php echo $anioFiltro; ?>&cliente=<?php echo $clienteFiltro; ?>&ubicacion=<?php echo $ubicacionFiltro; ?>" class="button">
      Anterior
    </a>
  <?php endif; ?>

  <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
    <a href="?pagina=<?php echo $i; ?>&anio=<?php echo $anioFiltro; ?>&cliente=<?php echo $clienteFiltro; ?>&ubicacion=<?php echo $ubicacionFiltro; ?>"
      class="button <?php echo ($i === $paginaActual) ? 'paginacion-activa' : ''; ?>">
      <?php echo $i; ?>
    </a>
  <?php endfor; ?>

  <?php if($paginaActual < $totalPaginas): ?>
    <a href="?pagina=<?php echo $paginaActual + 1; ?>&anio=<?php echo $anioFiltro; ?>&cliente=<?php echo $clienteFiltro; ?>&ubicacion=<?php echo $ubicacionFiltro; ?>" class="button">
      Siguiente
    </a>
  <?php endif; ?>

  <p>Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?> — <?php echo $totalReportes; ?> reportes en total</p>
</div>
```

### ¿Por qué los links incluyen `&anio=...&cliente=...&ubicacion=...`?

Porque si estás en la página 2 con el filtro `anio=2025` y haces clic en
"Siguiente", la URL debe quedar:

```
?pagina=3&anio=2025
```

Si el link fuera solo `?pagina=3`, perderías el filtro al cambiar de página.
Al incluir todos los parámetros de filtro en cada link, la página y los
filtros viajan juntos en la URL.

### ¿Qué hace `($i === $paginaActual) ? 'paginacion-activa' : ''`?

Agrega la clase CSS `paginacion-activa` al botón de la página en la que estás,
para que se vea diferente (resaltado) visualmente.

### ¿Qué hace el `if($paginaActual > 1)` antes del botón "Anterior"?

Oculta el botón "Anterior" cuando estás en la primera página, porque no hay
página anterior a la que ir. Lo mismo con "Siguiente" en la última página.

---

## Paso 5 — Estilos SCSS para la paginación

En `src/scss/layout/_filtros.scss` (o en un archivo `_paginacion.scss` nuevo)
agrega:

```scss
.paginacion {
    display: flex;
    align-items: center;
    gap: .8rem;
    flex-wrap: wrap;
    margin-top: 2rem;
    justify-content: center;

    .button {
        min-width: 4rem;
        text-align: center;

        &.paginacion-activa {
            background-color: $azul;
            color: $blanco;
            pointer-events: none; // Desactiva el clic en la página actual
        }
    }

    p {
        width: 100%;
        text-align: center;
        color: $gris;
        font-size: 1.2rem;
    }
}
```

---

## Resumen del flujo completo

```
1. Usuario abre admin/index.php             → URL: index.php
   $paginaActual = 1, $offset = 0
   MySQL: SELECT ... LIMIT 10 OFFSET 0      → registros 1 al 10

2. Usuario hace clic en página 3            → URL: ?pagina=3
   $paginaActual = 3, $offset = 20
   MySQL: SELECT ... LIMIT 10 OFFSET 20     → registros 21 al 30

3. Usuario filtra por año 2025 en página 2  → URL: ?pagina=2&anio=2025
   $paginaActual = 2, $offset = 10
   MySQL: SELECT ... WHERE YEAR(fecha) = 2025
          ORDER BY fecha DESC LIMIT 10 OFFSET 10
   Solo reportes del 2025, del 11 al 20.

4. Botones de navegación mantienen siempre
   los filtros activos en la URL.
```

---

## Checklist para implementar paginación en otro módulo

- [ ] Agregar método `contarXxx()` en la clase con los mismos filtros que `mostrarXxx()`.
- [ ] Agregar `$limite = 10, $offset = 0` a la firma de `mostrarXxx()`.
- [ ] Agregar `LIMIT $limite OFFSET $offset` al final de la query en `mostrarXxx()`.
- [ ] Leer `$_GET['pagina']` con `max(1, intval(...))` en la vista.
- [ ] Calcular `$totalPaginas = ceil($total / $porPagina)`.
- [ ] Calcular `$offset = ($paginaActual - 1) * $porPagina`.
- [ ] Pasar `$porPagina` y `$offset` al llamar a `mostrarXxx()`.
- [ ] Agregar los botones de navegación en el HTML manteniendo los filtros en la URL.
- [ ] Agregar estilos para `.paginacion` y `.paginacion-activa`.
