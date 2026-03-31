<fieldset>
      <legend>Ingrese la informacion solicitada</legend>
      <label for="fecha">Fecha</label>
      <input type="date" id="fecha" name="fecha" value="<?php echo escaparValores($reporte->fecha); ?>">

      <label for="client">Cliente</label>
      <select id="client" name="cliente">
        <option value="" disabled selected>Seleccione</option>
        <?php while($row = mysqli_fetch_assoc($clientes)): ?>
          <option <?php echo $reporte->cliente === $row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo escaparValores($row['nombre']); ?></option>
        <?php endwhile; ?>
      </select>

      <label for="producto">Producto</label>
      <select id="producto" name="producto">
        <option value="" disabled selected>Seleccione</option>
        <?php while($row = mysqli_fetch_assoc($productos)): ?>
          <option <?php echo $reporte->producto === $row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo escaparValores($row['nombre']); ?></option>
        <?php endwhile; ?>
      </select>

      <label for="cantidad">Cantidad</label>
      <input type="number" id="cantidad" name="cantidad" min="1" value="<?php echo escaparValores($reporte->cantidad); ?>">

      <label for="ubicacion">Ubicacion de entrega</label>
      <select id="ubicacion" name="ubicacion">
        <option value="" disabled selected>Seleccione</option>
        <?php while($row = mysqli_fetch_assoc($ubicacion)): ?>
          <option <?php echo $reporte->ubicacion == $row['id'] ? 'selected' : ''; ?> value="<?php echo $row['id']; ?>"><?php echo escaparValores($row['nombre']); ?></option>
        <?php endwhile; ?>
      </select>

      <label for="maps">Ubicacion de google maps</label>
      <input type="text" id="maps" name="maps" value="<?php echo escaparValores($reporte->maps); ?>">

      <label for="lugar">Lugar de entrega</label>
      <input type="text" id="lugar" name="lugar" value="<?php echo escaparValores($reporte->lugar); ?>">

      <label for="imagen">Imagen de entrega</label>
      <input type="file" id="imagen" name="imagen">

      <?php if($reporte->imagen): ?>
        <div class="foto-entrega-container">
          <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
        </div>
      <?php endif; ?>

      <label for="comentarios">Comentarios</label>
      <textarea id="comentarios" name="comentarios"><?php echo escaparValores($reporte->comentarios); ?></textarea>
    </fieldset>