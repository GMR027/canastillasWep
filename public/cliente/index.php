<?php
include __DIR__ . '/../../includes/app.php';
 soloCliente();
use App\Reportes;

// Leer los filtros de la URL
$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null; // Si no se especifica un filtro, se asigna null para mostrar todos los reportes. isset verifica si el parametro existe en la URL, y la segunda parte verifica que no esté vacío. Si ambos son verdaderos, se asigna el valor del filtro, de lo contrario se asigna null. Esto permite mostrar todos los reportes cuando no se aplican filtros.
$ubicacionFiltro = (isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '') ? $_GET['ubicacion'] : null; //Lee el parametro ubicacion de la URL y lo asigna a $ubicacionFiltro, o null si no se especifica.

// Datos para los selects
$ubicaciones = $db->query("SELECT id, nombre FROM ubicacion"); //Obtiene la lista de ubicaciones para el filtro, desde la tabla ubicacion.
$anioActual  = (int) date('Y'); //Obtiene el año actual para generar el rango de años en el filtro.
$anios       = range($anioActual, 2023); //Genera un array de años desde el año actual hasta 2023 para el filtro de años.


$reportes = Reportes::mostrarPorCliente($_SESSION['id'], $anioFiltro, $ubicacionFiltro); //Obtiene los reportes del cliente actual aplicando los filtros seleccionados por el usuario. Si un filtro es null, se mostrarán todos los reportes para ese criterio.

// Lee el parametro st de la URL y lo convierte a entero para comparar con ===.
$mensaje = (int) ($_GET['st'] ?? 0);

template('headerHTML');
?>

<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="links">
    <a href="/cerrar-sesion.php">Cerrar Sesion</a>
  </div>
</div>

<form action="" method="get" class="filtros contenedor"> <!-- Formulario para los filtros, con método GET para que los filtros se reflejen en la URL. -->
  <div>
    <select class="filtro-grupo" name="anio" id=""> <!-- Select para filtrar por año, con opción por defecto para mostrar todos los años. -->
    <option value="">--Filtro por año--</option>
    <?php foreach($anios as $year): ?> <!-- Itera sobre el array de años para generar las opciones del select. -->
      <option value="<?php echo $year; ?>" <?php echo ($anioFiltro == $year) ? 'selected' : ''; ?>> <!-- Marca como seleccionado el año que coincide con el filtro actual. -->
        <?php echo $year; ?> <!-- Muestra el año como texto de la opción. -->
      </option>
      <?php endforeach;
      ?>
    </select>

    <select class="filtro-grupo" name="ubicacion">
      <option value="">--Filtro por ubicacion--</option>
      <?php while($ubicacion = $ubicaciones->fetch_assoc()): ?>
        <option value="<?php echo $ubicacion['id']; ?>" <?php echo ($ubicacionFiltro == $ubicacion['id']) ? 'selected' : ''; ?>>
          <?php echo $ubicacion['nombre']; ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div>
    <button type="submit" class="button">Filtrar</button>
    <a href="/public/cliente/index.php" class="button">Limpiar Filtro</a>
  </div>
</form>



<section class="contenedor">
  <h1>Listado de entregas</h1>
  <P>Nombre de cliente: <?php echo escaparValores($_SESSION['nombre']); ?></P>
  <p>Contacto: <?php echo escaparValores(!empty($reportes) ? $reportes[0]->telefono : ''); ?></p> <!-- Muestra el telefono del primer reporte si existe, de lo contrario muestra una cadena vacia. -->
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th>Foto Entrega</th>
        <th class="hMovilTablas">Ver Reporte</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($reportes as $reporte): ?>
      <tr>
        <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
            <!-- Muestra la fecha en formato dia/mes_en_texto/anio (ejemplo: 28/marzo/2026). -->
        <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
        <td class="info"><?php echo $reporte->nombre_producto; ?></td>
        <td><?php echo $reporte->cantidad; ?></td>
        <td class="foto-entrega-container">
          <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
        </td>
        <td class="hMovilTablas">
          <a class="button" href="/public/cliente/detalleReportes.php?id=<?php echo $reporte->id; ?>">Reporte</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="flex-center">
    <a class="button hTabletTablas" href="detalleReportes.php">Reporte</a>
  </div>
</section>


<?php
 template('footer');
?>