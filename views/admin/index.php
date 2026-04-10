<?php
use App\Reportes;

// Leer los filtros de la URL
$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null;
$clienteFiltro   = (isset($_GET['cliente'])   && $_GET['cliente']   !== '') ? $_GET['cliente']   : null;
$ubicacionFiltro = (isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '') ? $_GET['ubicacion'] : null;

// Datos para los selects
$clientes    = $db->query("SELECT id, nombre FROM usuarios WHERE rol = 2");
$ubicaciones = $db->query("SELECT id, nombre FROM ubicacion");
$anioActual  = (int) date('Y');
$anios       = range($anioActual, 2023);

$paginacion = paginacion(10, Reportes::contarNumReportes($anioFiltro, $clienteFiltro, $ubicacionFiltro));
$reportes = Reportes::mostrarTodos($anioFiltro, $clienteFiltro, $ubicacionFiltro, $paginacion['limite'], $paginacion['offset']);

$mensaje = (int) ($_GET['st'] ?? 0);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $id = filter_var($id, FILTER_VALIDATE_INT);
  $reporte = Reportes::buscarID($id);
  if($reporte) {
    $reporte->eliminar();
    header('Location: /admin?st=3');
  } else {
    header('Location: /admin');
  }
}



template('headerHTML');
?>

<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="navbar links-admin">
    <a class="button" href="/admin/reportes/crear">Reporte</a>
    <a class="button" href="/admin/pedidos">Pedido</a>
    <a class="button hMovil" href="/admin/usuarios">Usuario</a>
    <a class="button hMovil" href="/admin/productos">Productos</a>
    <a class="button hMovil" href="/admin/agenda/index">Agenda</a>
    <a class="button hMovil" href="/logout">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajes contenedor">
  <?php
    if($mensaje === 1) {
      echo '<p class="alerta exito">Reporte Creado Correctamente</p>';
    } else if($mensaje === 2) {
      echo '<p class="alerta actualizacion">Reporte Actualizado Correctamente</p>';
    } else if($mensaje === 3) {
      echo '<p class="alerta eliminacion">Reporte Eliminado Correctamente</p>';
    }
   ?>
</section>

<form action="" method="get" class="filtros contenedor">
  <div class="hMovil">
    <select class="filtro-grupo" name="anio" id="">
    <option value="">--Filtro por año--</option>
    <?php foreach($anios as $year): ?>
      <option value="<?php echo $year; ?>" <?php echo ($anioFiltro == $year) ? 'selected' : ''; ?>>
        <?php echo $year; ?>
      </option>
      <?php endforeach; ?>
    </select>

  <select class="filtro-grupo" name="cliente">
    <option value="">--Filtro por cliente--</option>
    <?php while($cliente = $clientes->fetch_assoc()): ?>
      <option value="<?php echo $cliente['id']; ?>" <?php echo ($clienteFiltro == $cliente['id']) ? 'selected' : ''; ?>>
        <?php echo $cliente['nombre']; ?>
      </option>
    <?php endwhile; ?>
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

  <div class="hMovil">
    <button type="submit" class="button">Filtrar</button>
    <a href="/admin" class="button">Limpiar Filtro</a>
  </div>
</form>

<section>
  <h1>Ultimas entregas</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Cliente</th>
        <th class="info hMovilTablas">Producto</th>
        <th class="hMovilTablas">Cantidad</th>
        <th class="hMovilTablas">Imagen</th>
        <th class="hMovilTablas">Estatus</th>
        <th>Reporte</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($reportes as $reporte): ?>
        <tr>
          <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
          <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
          <td class="info"><?php echo $reporte->nombre_cliente; ?></td>
          <td class="info hMovilTablas"><?php echo $reporte->nombre_producto; ?></td>
          <td class="hMovilTablas"><?php echo $reporte->cantidad; ?></td>
          <td class="foto-entrega-container hMovilTablas">
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
          <td class="hMovilTablas acciones">
            <a class="button" href="/admin/reportes/detalle?id=<?php echo $reporte->id; ?>">Reporte</a>
            <a href="/admin/reportes/recibo?id=<?php echo $reporte->id; ?>" class="button">Imprimir Recibo</a>
          </td>
          <td>
            <div class="acciones">
                <a class="button editar" href="/admin/reportes/editar?id=<?php echo $reporte->id; ?>">Editar</a>
                <form action="" method="POST">
                  <input type="hidden" name="id" value="<?php echo $reporte->id; ?>">
                <button type="submit" class="button eliminar">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php require TEMPLATES_URL . '/paginacion.php'; ?>
</section>


<?php
 template('footer');
?>
