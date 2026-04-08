<?php
use App\Pedidos;
$idValido = validarID($_GET['id'] ?? null, '/admin/pedidos');
$pedido = Pedidos::buscarID($idValido);

template('headerHTML');
?>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="links">
    <a href="/admin/pedidos">Regresar</a>
    <a href="/logout">Cerrar Sesion</a>
  </div>
</div>


<h1>Detalle del Pedido #<?php echo $pedido->numeroPedido; ?></h1>
<section class="contenedor">
  <p>Fecha: <?php echo $pedido->fechaFormateada(); ?></p>
  <p class="hMovilTablas">Estatus de entrega: 
    <?php 
      $estadusInfo = [ // Agrega un array para mapear los estatus a texto y clases CSS.
        1 => ['texto' => 'Pendiente', 'clase' => 'pendiente'],
        2 => ['texto' => 'En tránsito', 'clase' => 'en-transito'],
        3 => ['texto' => 'Entregado', 'clase' => 'entregado'],
        4 => ['texto' => 'Cancelado', 'clase' => 'cancelado'],
        5 => ['texto' => 'Pagado', 'clase' => 'pagado']
      ];
      $info = $estadusInfo[$pedido->estatus] ?? ['texto' => 'Desconocido', 'clase' => 'desconocido'];
    ?>
    <span class="indicadores <?php echo $info['clase']; ?>"><?php echo $info['texto']; ?></span>
  </p>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">Fecha de embarque</th>
        <th class="hMovilTablas">Fecha de recibo</th>
        <th class="hMovilTablas">Dias de transito</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="hMovilTablas"><?php echo $pedido->fechaEmbarque; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->fechaRecibo; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->diasEnTransito() . ' días'; ?></td>
        <td class=""><?php echo $pedido->producto; ?></td>
        <td class=""><?php echo $pedido->cantidad; ?></td>
    </tbody>
  </table>
</section>


<section class="contenedor">
  <h2>Foto de pedido</h2>
  <div class="foto-entrega-detalle-container">
    <img src="/public/image/<?php echo $pedido->imagen; ?>" alt="Foto de pedido" class="foto-entrega">
  </div>
</section>

<?php if($pedido->comentarios): ?>
  <section class="contenedor">
    <h2>Comentarios</h2>
    <p><?php echo nl2br(escaparValores($pedido->comentarios)); ?></p>
  </section>
<?php endif; ?>




<?php
 template('footer');
?>
