<?php
include __DIR__ . '/../../includes/app.php';
soloProveedor();
template('headerHTML');

use App\Pedidos;
  $pedidos = Pedidos::mostrarTodos();

  $mensaje = (int) ($_GET['st'] ?? 0);

?>
<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="navbar links-admin">
    <a class="button" href="/cerrar-sesion.php">Cerrar Sesion</a>
  </div>
</div>

<section class="contenedor">
  <h1>Listado pedidos</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Num Pedido</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th class="hMovilTablas">Estatus Entrega</th>
        <th class="hMovilTablas">Imagen de pedido</th>
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
        <td class="foto-entrega-container hMovilTablas">
          <img src="/public/image/<?php echo $pedido->imagen; ?>" alt="Foto de pedido" class="foto-entrega">
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php
 template('footer');
?>