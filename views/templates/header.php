<?php
$enInicio = ($pagina ?? '') === 'inicio';
$enCuenta = in_array($pagina ?? '', ['cuenta', 'login'], true);
$sesion = cliente();
$enlaces = [
    ['url' => '/#productos', 'texto' => 'Productos', 'activo' => $enInicio],
    ['url' => '/#como-comprar', 'texto' => 'Cómo comprar', 'activo' => false],
    ['url' => '/#cobertura', 'texto' => 'Cobertura', 'activo' => false],
    ['url' => '/#entregas', 'texto' => 'Entregas', 'activo' => false],
];
?>
<header class="header <?= $enInicio ? 'header--transparente' : '' ?>" data-header>
  <div class="contenedor header__barra">
    <a class="marca" href="/" aria-label="<?= s(sitio('nombre')) ?>, ir al inicio">
      <img class="marca__logo" src="<?= img('logo-canastillas.png') ?>" alt="" width="220" height="40">
      <span class="sr-only"><?= s(sitio('nombre')) ?></span>
    </a>

    <button class="header__menu-btn" type="button" aria-expanded="false" aria-controls="navegacion" aria-label="Abrir menú" data-menu-btn>
      <?= icono('menu', 'icono icono--abrir') ?>
      <?= icono('cerrar', 'icono icono--cerrar') ?>
    </button>

    <nav class="navegacion" id="navegacion" aria-label="Principal" data-navegacion>
      <?php foreach ($enlaces as $enlace): ?>
        <a class="navegacion__enlace <?= $enlace['activo'] ? 'is-activo' : '' ?>" href="<?= s($enlace['url']) ?>" <?= $enlace['activo'] ? 'aria-current="page"' : '' ?>><?= s($enlace['texto']) ?></a>
      <?php endforeach; ?>

      <?php if ($sesion): ?>
        <details class="cuenta-menu" data-cuenta-menu>
          <summary class="navegacion__enlace <?= $enCuenta ? 'is-activo' : '' ?>">
            <?= icono('usuario') ?> Mis compras <?= icono('chevron', 'icono cuenta-menu__flecha') ?>
          </summary>
          <div class="cuenta-menu__panel">
            <p class="cuenta-menu__nombre"><?= s($sesion['nombre']) ?></p>
            <a href="/mi-cuenta/compras"><?= icono('paquete') ?> Mis compras</a>
            <form method="post" action="/logout">
              <?= csrf_campo() ?>
              <button type="submit"><?= icono('salir') ?> Salir</button>
            </form>
          </div>
        </details>
      <?php else: ?>
        <a class="navegacion__enlace navegacion__login <?= $enCuenta ? 'is-activo' : '' ?>" href="/login" <?= ($pagina ?? '') === 'login' ? 'aria-current="page"' : '' ?>><?= icono('usuario') ?> Iniciar sesión</a>
      <?php endif; ?>

      <a class="boton boton--primario boton--chico navegacion__cta" href="<?= s(whatsapp('Hola, quiero cotizar canastillas.')) ?>" target="_blank" rel="noopener">
        <?= icono('whatsapp') ?> Cotizar
      </a>
    </nav>
  </div>
</header>
