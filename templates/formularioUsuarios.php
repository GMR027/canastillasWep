 <fieldset>
      <legend>Ingrese la informacion solicitada</legend>
      <label for="nombre">Nombre</label>
      <input type="text" id="nombre" name="nombre" value="<?php echo escaparValores($usuario->nombre); ?>">

      <label for="telefono">Teléfono</label>
      <input type="text" id="telefono" name="telefono" value="<?php echo escaparValores($usuario->telefono); ?>">
      
      <label for="correo">Correo</label>
      <input type="email" id="correo" name="correo" value="<?php echo escaparValores($usuario->correo); ?>">

      <label for="empresa">Empresa</label>
      <input type="text" id="empresa" name="empresa" value="<?php echo escaparValores($usuario->empresa); ?>">

      <label for="rol">Rol</label>
      <select id="rol" name="rol">
        <option value="" disabled selected>Seleccione</option>
        <option value="1" <?php echo $usuario->rol == 1 ? 'selected' : ''; ?>>Admin</option>
        <option value="2" <?php echo $usuario->rol == 2 ? 'selected' : ''; ?>>Cliente</option>
        <option value="3" <?php echo $usuario->rol == 3 ? 'selected' : ''; ?>>Proveedor</option>
      </select>

      <label for="contrasena">Contraseña</label>
      <input type="password" id="contrasena" name="contrasena" value="<?php echo escaparValores($usuario->contrasena); ?>">

    </fieldset>