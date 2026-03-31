# Fechas de embarque y recibo — Costo sin IVA y con IVA

Estos campos viven en la clase `Pedidos` y en la tabla `pedidos` de la base de datos.

---

## Fecha de embarque (`fechaEmbarque`) y fecha de recibo (`fechaRecibo`)

### ¿Qué representa cada una?

| Campo | ¿Qué es? | Ejemplo |
|---|---|---|
| `fechaEmbarque` | El día en que el proveedor despachó el pedido | `2026-03-10` |
| `fechaRecibo` | El día en que el pedido llegó físicamente | `2026-03-18` |

### ¿Para qué sirven las dos fechas juntas?

La diferencia entre ellas te da los **días en tránsito**: cuánto tiempo tardó
el pedido en llegar desde que salió del proveedor.

En tu clase `Pedidos.php` ya tienes el método que hace ese cálculo:

```php
public function diasEnTransito() {
    if(!$this->fechaEmbarque || !$this->fechaRecibo) return '';

    $embarque = new \DateTime($this->fechaEmbarque);
    $recibo   = new \DateTime($this->fechaRecibo);

    $diferencia = $embarque->diff($recibo);
    return $diferencia->days;
}
```

### Desglose línea por línea:

**`new \DateTime($this->fechaEmbarque)`**
Convierte la cadena de texto `"2026-03-10"` (que viene de la base de datos)
a un objeto `DateTime` de PHP que sabe hacer operaciones de fechas.
Sin esto, PHP trataría la fecha como texto y no podría restar.

**`$embarque->diff($recibo)`**
`diff()` calcula la diferencia entre dos objetos `DateTime`.
Devuelve un objeto `DateInterval` que contiene la diferencia
desglosada en años, meses, días, horas, etc.

**`$diferencia->days`**
De todo el `DateInterval`, solo te interesa el total de días.
`->days` te da ese número como entero, por ejemplo `8`.

### ¿Qué pasa si una de las fechas no existe?

```php
if(!$this->fechaEmbarque || !$this->fechaRecibo) return '';
```

Si el pedido todavía no fue recibido, `fechaRecibo` está vacío.
Este `if` evita que `DateTime` explote con una cadena vacía.
En lugar de dar error, devuelve `''` y la vista no muestra nada.

### ¿Cómo se muestra en la tabla?

En `admin/pedidos/index.php` tienes este código:

```php
<?php if($pedido->diasEnTransito() !== ''): ?>
  <span class="dias-transito <?php echo $pedido->claseTransito(); ?>">
    <?php echo $pedido->diasEnTransito() . ' días'; ?>
  </span>
<?php endif; ?>
```

Y `claseTransito()` le pone un color según qué tan rápido llegó:

```php
public function claseTransito() {
    $dias = $this->diasEnTransito();
    if($dias === '') return '';
    if($dias <= 7)  return 'transito-rapido';  // Verde
    if($dias <= 12) return 'transito-normal';  // Amarillo
    return 'transito-lento';                   // Rojo
}
```

| Días en tránsito | Clase CSS | Significado |
|---|---|---|
| 0 – 7 días | `transito-rapido` | Llegó rápido |
| 8 – 12 días | `transito-normal` | Tiempo normal |
| 13+ días | `transito-lento` | Tardó mucho |

---

## Costo sin IVA (`costoSinIva`) y costo con IVA (`costoConIva`)

### ¿Qué representa cada uno?

| Campo | ¿Qué es? | Ejemplo |
|---|---|---|
| `costoSinIva` | El precio base del pedido antes de impuestos | `10000` |
| `costoConIva` | El precio final incluyendo el 16% de IVA | `11600` |

### ¿Qué es el IVA?

El IVA (Impuesto al Valor Agregado) en México es del **16%**.
Se calcula sobre el precio base (sin IVA):

```
Precio con IVA = Precio sin IVA × 1.16
```

Ejemplo:
```
costoSinIva  = $10,000
IVA (16%)    = $10,000 × 0.16 = $1,600
costoConIva  = $10,000 + $1,600 = $11,600
```

### ¿Por qué guardar los dos en la base de datos?

Porque el precio sin IVA puede cambiar (por ejemplo, si eres empresa y
recuperas el IVA) y necesitas saber el desglose exacto para facturación.
Si solo guardaras uno tendrías que recalcular el otro cada vez, y si
el porcentaje de IVA cambiara en el futuro los cálculos históricos
quedarían incorrectos.

### ¿Cómo calcular `costoConIva` automáticamente en PHP?

Si el usuario solo ingresa el costo sin IVA en el formulario,
puedes calcular el con IVA antes de guardar:

```php
// En el controlador, después de recibir $_POST:
$pedido = new Pedidos($_POST);

// Si se ingresó costo sin IVA pero no el con IVA, calcularlo
if($pedido->costoSinIva && !$pedido->costoConIva) {
    $pedido->costoConIva = round($pedido->costoSinIva * 1.16, 2);
    // round(..., 2) redondea a 2 decimales para evitar resultados como 11600.000000001
}
```

### ¿Cómo se muestran en la tabla?

En `admin/pedidos/index.php`:

```php
<td>$<?php echo $pedido->costoSinIva; ?>.00 MXN</td>
<td>$<?php echo $pedido->costoConIva; ?>.00 MXN</td>
```

Si quisieras formatearlos con separadores de miles (por ejemplo `$10,000.00`):

```php
<td>$<?php echo number_format($pedido->costoSinIva, 2, '.', ','); ?> MXN</td>
```

`number_format($numero, $decimales, $separadorDecimal, $separadorMiles)`

| Parámetro | Valor | Resultado |
|---|---|---|
| `$numero` | `10000` | — |
| `$decimales` | `2` | Muestra centavos |
| `$separadorDecimal` | `'.'` | Punto para decimales |
| `$separadorMiles` | `','` | Coma para miles |
| Resultado final | — | `10,000.00` |

---

## Resumen de los campos en la clase

```php
// Campos en Pedidos.php
public $fechaEmbarque; // Fecha en que salió del proveedor  → YYYY-MM-DD
public $fechaRecibo;   // Fecha en que llegó físicamente    → YYYY-MM-DD
public $costoSinIva;   // Precio base sin impuestos         → número
public $costoConIva;   // Precio final con 16% de IVA       → número

// Métodos relacionados
diasEnTransito()  // Calcula los días entre fechaEmbarque y fechaRecibo
claseTransito()   // Devuelve una clase CSS según qué tan rápido llegó
```
