<section class="auth">
  <div class="auth__fondo" aria-hidden="true"></div>
  <div class="contenedor auth__grid">
    <div class="auth__texto">
      <p class="etiqueta">Portal de clientes</p>
      <h1 class="auth__titulo">Tus compras y entregas, en un solo lugar</h1>
      <ul class="auth__beneficios">
        <li><?= icono('paquete') ?> Consulta el estatus de cada compra: en proceso, entregada o cancelada.</li>
        <li><?= icono('camara') ?> Revisa las fotos de cada entrega.</li>
        <li><?= icono('ficha') ?> Consulta e imprime el acta de entrega.</li>
      </ul>
    </div>

    <div class="tarjeta auth__tarjeta">
      <h2 class="tarjeta__titulo"><?= icono('candado') ?> Iniciar sesión</h2>

      <?php if (!empty($error)): ?>
        <p class="alerta alerta--error" role="alert"><?= s($error) ?></p>
      <?php endif; ?>

      <form class="formulario" method="post" action="/login" novalidate>
        <?= csrf_campo() ?>

        <div class="campo">
          <label class="campo__etiqueta" for="email">Correo electrónico</label>
          <input class="campo__control" type="email" id="email" name="email" value="<?= s($email ?? '') ?>" autocomplete="username" required autofocus>
        </div>

        <div class="campo">
          <label class="campo__etiqueta" for="contrasena">Contraseña</label>
          <input class="campo__control" type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
        </div>

        <button class="boton boton--primario boton--bloque" type="submit">Entrar <?= icono('flecha') ?></button>
      </form>

      <p class="auth__ayuda">
        ¿No tienes acceso o olvidaste tu contraseña?
        <a href="<?= s(whatsapp('Hola, quiero acceso al portal de clientes de Canastillas de la Baja.')) ?>" target="_blank" rel="noopener">Escríbenos por WhatsApp</a>.
      </p>
    </div>
  </div>
</section>
