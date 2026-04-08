<?php
use App\Pedidos;

$anioFiltro         = (isset($_GET['anio'])         && $_GET['anio']         !== '') ? $_GET['anio']         : null;
$estatusFiltro      = (isset($_GET['estatus'])      && $_GET['estatus']      !== '') ? $_GET['estatus']      : null;
$diasTransitoFiltro = (isset($_GET['dias_transito']) && $_GET['dias_transito'] !== '') ? $_GET['dias_transito'] : null;

$anioActual  = (int) date('Y');
$anios       = range($anioActual, 2023);

$estatusOpciones = [
    1 => 'Pendiente',
    2 => 'En tránsito',
    3 => 'Entregado',
    4 => 'Cancelado',
    5 => 'Pagado'
];

$estadoTransitoOpciones = [
    0 => 'Todos',
    1 => '0 a 7 días',
    2 => '9 a 15 días',
    3 => 'Más de 15 días'
];

$paginacion = paginacion(10, Pedidos::contarPedidos($anioFiltro, $estatusFiltro, $diasTransitoFiltro));
$pedidos = Pedidos::mostrarTodos($anioFiltro, $estatusFiltro, $diasTransitoFiltro, $paginacion['limite'], $paginacion['offset']);

$mensaje = (int) ($_GET['st'] ?? 0);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $id = filter_var($id, FILTER_VALIDATE_INT);
  $pedido = Pedidos::buscarID($id);
  if($pedido) {
    $pedido->eliminar();
    header('Location: /admin/pedidos?st=3');
  } else {
    header('Location: /admin/pedidos');
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
    <a class="button" href="/admin/pedidos/crear">Crear</a>
    <a class="button" href="/admin">Regresar</a>
    <a class="button hMovil" href="/logout">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajes contenedor">
  <?php
    if($mensaje === 1) {
      echo '<p class="alerta exito">Pedido Creado Correctamente</p>';
    } else if($mensaje === 2) {
      echo '<p class="alerta actualizacion">Pedido Actualizado Correctamente</p>';
    } else if($mensaje === 3) {
      echo '<p class="alerta eliminacion">Pedido Eliminado Correctamente</p>';
    }
   ?>
</section>

<form action="" method="get" class="hMovil filtros contenedor">
  <div class="hMovil">
    <select class="filtro-grupo" name="anio" id="">
      <option value="">--Filtro por año--</option>
      <?php foreach($anios as $year): ?>
        <option value="<?php echo $year; ?>" <?php echo ($anioFiltro == $year) ? 'selected' : ''; ?>>
          <?php echo $year; ?>
        </option>
        <?php endforeach; ?>
    </select>

    <select class="filtro-grupo" name="estatus">
      <option value="">--Filtro por Estatus--</option>
      <?php foreach($estatusOpciones as $id => $nombre): ?>
        <option value="<?php echo $id; ?>" <?php echo ($estatusFiltro == $id) ? 'selected' : ''; ?>>
          <?php echo $nombre; ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select class="filtro-grupo" name="dias_transito">
      <option value="">--Filtro por Días en Tránsito--</option>
      <?php foreach($estadoTransitoOpciones as $id => $nombre): ?>
        <option value="<?php echo $id; ?>" <?php echo ($diasTransitoFiltro == $id) ? 'selected' : ''; ?>>
          <?php echo $nombre; ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="hMovil">
    <button type="submit" class="button">Filtrar</button>
    <a href="/admin/pedidos" class="button">Limpiar Filtro</a>
  </div>
</form>


<section>
  <h1>Ultimos pedidos</h1>
  <?php require TEMPLATES_URL . '/infoPedidos.php'; ?>
  <?php require TEMPLATES_URL . '/paginacion.php'; ?>
</section>

<?php
 template('footer');
?>
