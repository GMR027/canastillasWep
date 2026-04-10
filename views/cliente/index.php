<?php
use App\Reportes;

$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null;
$ubicacionFiltro = (isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '') ? $_GET['ubicacion'] : null;

$ubicaciones = $db->query("SELECT id, nombre FROM ubicacion");
$anioActual  = (int) date('Y');
$anios       = range($anioActual, 2023);

$reportes = Reportes::mostrarPorCliente($_SESSION['id'], $anioFiltro, $ubicacionFiltro);

$mensaje = (int) ($_GET['st'] ?? 0);

template('headerHTML');
?>

<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="links">
    <a href="/logout">Cerrar Sesion</a>
  </div>
</div>

<form action="" method="get" class="filtros contenedor">
  <div>
    <select class="filtro-grupo" name="anio" id="">
    <option value="">--Filtro por año--</option>
    <?php foreach($anios as $year): ?>
      <option value="<?php echo $year; ?>" <?php echo ($anioFiltro == $year) ? 'selected' : ''; ?>>
        <?php echo $year; ?>
      </option>
      <?php endforeach; ?>
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
    <a href="/cliente" class="button">Limpiar Filtro</a>
  </div>
</form>



<section class="contenedor">
  <h1>Listado de entregas</h1>
  <P>Nombre de cliente: <?php echo escaparValores($_SESSION['nombre']); ?></P>
  <p>Contacto: <?php echo escaparValores(!empty($reportes) ? $reportes[0]->telefono : ''); ?></p>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th>Foto Entrega</th>
        <th>Estatus</th>
        <th class="hMovilTablas">Ver Reporte</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($reportes as $reporte): ?>
      <tr>
        <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
        <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
        <td class="info"><?php echo $reporte->nombre_producto; ?></td>
        <td><?php echo $reporte->cantidad; ?></td>
        <td class="foto-entrega-container">
          <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
        </td>
        <td class="hMovilTablas">
          <?php
            $estatusPago = [
              0 => ['texto' => 'Pendiente de pago', 'clase' => 'pendiente-pago'],
              1 => ['texto' => 'Pagado', 'clase' => 'pagado']
            ];
            $info = $estatusPago[$reporte->estatus] ?? ['texto' => 'Pagado', 'clase' => 'pagado'];
          ?>
          <span class="<?php echo $info['clase']; ?>"><?php echo $info['texto']; ?></span>
        </td>
        <td class="hMovilTablas">
          <a class="button" href="/cliente/detalle?id=<?php echo $reporte->id; ?>">Reporte</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>


<?php
 template('footer');
?>
