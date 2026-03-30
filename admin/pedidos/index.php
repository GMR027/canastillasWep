
<?php
  require __DIR__ . '/../../includes/app.php';
  use App\Pedidos;
  $pedidos = Pedidos::mostrarTodos();

  $mensaje = (int) ($_GET['st'] ?? 0);

  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $pedido = Pedidos::buscarID($id);
    if($pedido) {
      $pedido->eliminar();
      header('Location: index.php?st=3');
    } else {
      header('Location: index.php');
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
    <a class="button" href="crearPedidos.php">Crear</a>
    <a class="button" href="/admin/index.php">Regresar</a>
    <a class="button" href="cerrar-sesion.php">Cerrar Sesion</a>
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

<section class="contenedor">
  <h1>Ultimos pedidos</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Num Pedido</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th class="hMovilTablas">Estatus</th>
        <th class="hMovilTablas">Imagen</th>
        <th class="hMovilTablas">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pedidos as $pedido): ?>
      <tr>
        <td class="hMovilTablas"><?php echo $pedido->id; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->fechaFormateada(); ?></td>
        <td class="info"><?php echo $pedido->numeroPedido; ?></td>
        <td class="info"><?php echo $pedido->producto; ?></td>
        <td><?php echo $pedido->cantidad; ?></td> 
        <td class="hMovilTablas indicadores">
          <?php 
            $estadusInfo = [
              1 => ['texto' => 'Pendiente', 'clase' => 'pendiente'],
              2 => ['texto' => 'En tránsito (Lepsa)', 'clase' => 'en-transito'],
              3 => ['texto' => 'Entregado', 'clase' => 'entregado'],
              4 => ['texto' => 'Cancelado', 'clase' => 'cancelado'],
              5 => ['texto' => 'Pagado', 'clase' => 'pagado']
            ];
            $info = $estadusInfo[$pedido->estatus] ?? ['texto' => 'Desconocido', 'clase' => 'desconocido'];
          ?>
          <span class="indicadores <?php echo $info['clase']; ?>"><?php echo $info['texto']; ?></span>
        </td>
        <td class="foto-entrega-container">
          <img src="/public/image/<?php echo $pedido->imagen; ?>" alt="Foto de pedido" class="foto-entrega">
        </td>
        <td class="hMovilTablas">
          <a class="button" href="editarPedidos.php?id=<?php echo $pedido->id; ?>">Editar</a>
          <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo $pedido->id; ?>">
            <button type="submit" class="button eliminar">Eliminar</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php
 template('footer');
?>