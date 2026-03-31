 <fieldset>
      <legend>Ingrese la informacion solicitada</legend>
      <label for="fecha">Fecha</label>
      <input type="date" id="fecha" name="fecha" value="<?php echo escaparValores($pedido->fecha); ?>">

      <label for="numeroPedido">Numero Pedido</label>
      <input type="number" id="numeroPedido" name="numeroPedido" value="<?php echo escaparValores($pedido->numeroPedido); ?>">

      <label for="producto">Producto</label>
      <textarea name="producto" id="producto"><?php echo escaparValores($pedido->producto); ?></textarea>

      <label for="cantidad">Cantidad</label>
      <input type="number" id="cantidad" name="cantidad" min="1" value="<?php echo escaparValores($pedido->cantidad); ?>">

      <label for="costoSinIva">Costo sin IVA</label>
      <input type="number" id="costoSinIva" name="costoSinIva" step="0.01" value="<?php echo escaparValores($pedido->costoSinIva); ?>">

      <label for="costoConIva">Costo con IVA</label>
      <input type="number" id="costoConIva" name="costoConIva" step="0.01" value="<?php echo escaparValores($pedido->costoConIva); ?>">

      <label for="estatus">Estatus de pedido</label>
      <select id="estatus" name="estatus">
        <option value="" disabled selected>Seleccione</option>
        <option value="1" <?php echo $pedido->estatus == 1 ? 'selected' : ''; ?>>Pendiente</option>
        <option value="2" <?php echo $pedido->estatus == 2 ? 'selected' : ''; ?>>En tránsito (Lepsa)</option>
        <option value="3" <?php echo $pedido->estatus == 3 ? 'selected' : ''; ?>>Entregado</option>
        <option value="4" <?php echo $pedido->estatus == 4 ? 'selected' : ''; ?>>Cancelado</option>
        <option value="5" <?php echo $pedido->estatus == 5 ? 'selected' : ''; ?>>Pagado</option>
      </select>

      <label for="embarque">Numero Embarque</label>
      <input type="text" id="embarque" name="embarque" value="<?php echo escaparValores($pedido->embarque); ?>">

      <label for="fechaEmbarque">Fecha Embarque</label>
      <input type="date" id="fechaEmbarque" name="fechaEmbarque" value="<?php echo escaparValores($pedido->fechaEmbarque); ?>">

      <label for="fechaRecibo">Fecha Recibo</label>
      <input type="date" id="fechaRecibo" name="fechaRecibo" value="<?php echo escaparValores($pedido->fechaRecibo); ?>">

      <label for="imagen">Imagen de pedido</label>
      <input type="file" id="imagen" name="imagen">

      <?php if($pedido->imagen): ?>
        <div class="foto-entrega-container">
          <img src="/public/image/<?php echo $pedido->imagen; ?>" alt="Foto de pedido" class="foto-entrega">
        </div>
      <?php endif; ?>

      <label for="comentarios">Comentarios</label>
      <textarea id="comentarios" name="comentarios"><?php echo escaparValores($pedido->comentarios); ?></textarea>

    </fieldset>