<?php
$sesionFooter = cliente();
$productosFooter = [
    ['texto' => 'Canastilla C-1', 'url' => '/#producto-c-1'],
    ['texto' => 'Canastilla C-3', 'url' => '/#producto-c-3'],
    ['texto' => 'Marco y contramarco', 'url' => '/#producto-ra-7'],
    ['texto' => 'Guía de instalación', 'url' => '/#instalacion'],
];
?>
<footer class="footer">
  <div class="contenedor footer__grid">
    <div class="footer__marca">
      <a class="marca" href="/" aria-label="<?= s(sitio('nombre')) ?>, ir al inicio">
        <img class="marca__logo" src="<?= img('logo-canastillas.png') ?>" alt="" width="220" height="40" loading="lazy">
        <span class="sr-only"><?= s(sitio('nombre')) ?></span>
      </a>
      <p><?= s(sitio('lema')) ?>. Desde <?= (int) sitio('desde') ?>.</p>
    </div>

    <div>
      <h2 class="footer__titulo">Productos</h2>
      <ul class="footer__lista">
        <?php foreach ($productosFooter as $producto): ?>
          <li><a href="<?= s($producto['url']) ?>"><?= s($producto['texto']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h2 class="footer__titulo">Sitio</h2>
      <ul class="footer__lista">
        <li><a href="/#como-comprar">Cómo comprar</a></li>
        <li><a href="/#cobertura">Cobertura</a></li>
        <li><a href="/#entregas">Entregas recientes</a></li>
        <li><a href="<?= $sesionFooter ? '/mi-cuenta/compras' : '/login' ?>"><?= $sesionFooter ? 'Mis compras' : 'Iniciar sesión' ?></a></li>
      </ul>
    </div>

    <div>
      <h2 class="footer__titulo">Contacto</h2>
      <ul class="footer__lista footer__contacto">
        <li><a href="tel:+526121541693"><?= icono('telefono') ?> <?= s(sitio('telefono')) ?></a></li>
        <li><a href="<?= s(whatsapp()) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> WhatsApp</a></li>
        <li><a href="mailto:<?= s(sitio('correo')) ?>"><?= icono('correo') ?> <?= s(sitio('correo')) ?></a></li>
        <li><a href="<?= s(sitio('facebook')) ?>" target="_blank" rel="noopener"><?= icono('facebook') ?> Facebook</a></li>
        <li><a href="<?= s(sitio('maps')) ?>" target="_blank" rel="noopener"><?= icono('ubicacion') ?> <?= s(sitio('ciudad')) ?></a></li>
      </ul>
    </div>
  </div>

  <div class="contenedor footer__legal">
    <p>&copy; <?= date('Y') ?> <?= s(sitio('nombre')) ?>. Todos los derechos reservados.</p>
  </div>
</footer>
