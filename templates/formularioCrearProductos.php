 <fieldset>
  <legend>Ingrese la informacion solicitada</legend>
  <label for="nombre">Nombre</label>
  <input type="text" id="nombre" name="nombre" value="<?php echo escaparValores($producto->nombre); ?>">

  <label for="descripcion">Descripción</label>
  <textarea id="descripcion" name="descripcion"><?php echo escaparValores($producto->descripcion); ?></textarea>
</fieldset>