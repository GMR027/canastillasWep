<?php
/** Variables: $tituloCuenta, $subtituloCuenta (opcional), $cliente */
?>
<section class="pagina-header pagina-header--cuenta">
  <div class="contenedor">
    <p class="etiqueta">Hola, <?= s($cliente->nombreVisible()) ?></p>
    <h1 class="pagina-header__titulo"><?= s($tituloCuenta) ?></h1>
    <?php if (!empty($subtituloCuenta)): ?><p class="pagina-header__texto"><?= $subtituloCuenta ?></p><?php endif; ?>
  </div>
</section>
