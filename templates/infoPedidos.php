<table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Num Pedido</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th class="hMovilTablas">Costo sin IVA</th>
        <th class="hMovilTablas">Costo con IVA</th>
        <th class="hMovilTablas">Estatus</th>
        <th class="hMovilTablas">Fecha Embarque</th>
        <th class="hMovilTablas">Fecha Recibo</th>
        <th class="hMovilTablas">Numero de Embarque</th>
        <th class="hMovilTablas">Días en Tránsito</th>
        <th class="hMovilTablas">Imagen</th>
        <th class="hMovilTablas">Comentarios</th>
        <th>Detalle</th>
        <?php if((int) $_SESSION['rol'] === 1): ?>
        <th class="hMovilTablas">Acciones</th>
        <?php endif; ?>
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
        <td class="hMovilTablas">$<?php echo $pedido->costoSinIva; ?>.00 MXN</td>
        <td class="hMovilTablas">$<?php echo $pedido->costoConIva; ?>.00 MXN</td>
        <td class="hMovilTablas indicadores">
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
        </td>
        <td class="hMovilTablas"><?php echo $pedido->fechaEmbarque ? date('d/m/Y', strtotime($pedido->fechaEmbarque)) : ''; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->fechaRecibo ? date('d/m/Y', strtotime($pedido->fechaRecibo)) : ''; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->embarque; ?></td>
        <td class="hMovilTablas">
          <?php if($pedido->diasEnTransito() !== ''): ?>
            <span class="dias-transito <?php echo $pedido->claseTransito(); ?>">
              <?php echo $pedido->diasEnTransito() . ' días'; ?>
            </span>
          <?php endif; ?>
        </td>
        <td class="foto-entrega-container hMovilTablas">
          <img src="/public/image/<?php echo $pedido->imagen; ?>" alt="Foto de pedido" class="foto-entrega">
        </td>
        <td class="hMovilTablas"><?php echo escaparValores($pedido->comentarios); ?></td>
        <td>
          <a href="/admin/pedidos/detalle?id=<?php echo $pedido->id; ?>" class="button">Detalle</a>
        </td>
        <?php if((int) $_SESSION['rol'] === 1): ?>
        <td class="hMovilTablas">
          <div class="acciones">
            <a class="button editar" href="/admin/pedidos/editar?id=<?php echo $pedido->id; ?>">Editar</a>
            
            <button type="button" class="button eliminar hMovil" data-id="<?php echo $pedido->id; ?>">Eliminar</button>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<div id="modal-overlay" style="display:none;">
  <div id="modal-box">
    <p>¿Estás seguro de que deseas eliminar este pedido?</p>
    <form action="" id="form-eliminar" method="POST">
      <input type="hidden" name="id" id="modal-id" value="">
      <button type="button" id="btn-cancelar">Cancelar</button>
      <button type="submit">Aceptar</button>
    </form>
  </div>
</div>