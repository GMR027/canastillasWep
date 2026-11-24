<?php
/** @var \Model\Compra $compra */
/** @var \Model\Cliente $cliente */
/** @var array $detalles */
/** @var \Model\Entrega[] $entregas */
$tituloCuenta = 'Compra ' . $compra->folio;
$subtituloCuenta = 'Registrada el ' . s(fecha($compra->fecha)) . ' · ' . s($compra->etiquetaEstado());
$pestana = 'compras';

$nivel = $compra->entregada() ? 3 : 2;
$pasos = [
    1 => ['Compra registrada', 'Tu compra quedó registrada en nuestro sistema.'],
    2 => ['En proceso', 'Estamos preparando tu compra para entrega.'],
    3 => ['Entregada', 'Tu compra fue entregada.'],
];
include __DIR__ . '/../templates/cuenta-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor detalle-pedido">

    <div class="detalle-pedido__barra">
      <a class="enlace-volver" href="/mi-cuenta/compras"><?= icono('flecha-izq') ?> Mis compras</a>
    </div>

    <?php if ($compra->cancelada()): ?>
      <div class="estado-cancelado tarjeta">
        <?php include __DIR__ . '/../templates/insignia-estado.php'; ?>
        <p>Esta compra fue cancelada. Si tienes dudas, escríbenos por WhatsApp.</p>
      </div>
    <?php else: ?>
      <ol class="linea-tiempo" aria-label="Estado de la compra">
        <?php foreach ($pasos as $n => [$nombre, $texto]): ?>
          <li class="linea-tiempo__paso <?= $n <= $nivel ? 'is-hecho' : '' ?> <?= $n === $nivel ? 'is-actual' : '' ?>" <?= $n === $nivel ? 'aria-current="step"' : '' ?>>
            <span class="linea-tiempo__punto"><?= $n < $nivel || ($n === $nivel && $n === 3) ? '✓' : $n ?></span>
            <strong><?= s($nombre) ?></strong>
            <span><?= s($texto) ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>

    <div class="detalle-pedido__grid">
      <div class="tarjeta">
        <h2 class="tarjeta__titulo"><?= icono('paquete') ?> Productos</h2>
        <div class="renglones">
          <?php foreach ($detalles as $d): ?>
            <div class="renglon">
              <div>
                <span class="linea-pedido__sku"><?= s($d['sku']) ?></span>
                <p class="renglon__nombre"><?= s($d['nombre']) ?></p>
                <p class="renglon__precio"><?= (int) $d['cantidad'] ?> × <?= dinero($d['precio_unitario']) ?><?= (float) $d['descuento_pct'] > 0 ? ' · −' . (float) $d['descuento_pct'] . '%' : '' ?></p>
              </div>
              <span class="renglon__importe"><?= dinero($d['subtotal']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <dl class="resumen__importes">
          <div><dt>Subtotal</dt><dd><?= dinero($compra->subtotal) ?></dd></div>
          <?php if ($compra->descuento > 0): ?><div><dt>Descuento</dt><dd>−<?= dinero($compra->descuento) ?></dd></div><?php endif; ?>
          <div><dt>IVA</dt><dd><?= dinero($compra->iva) ?></dd></div>
          <div class="resumen__total"><dt>Total</dt><dd><?= dinero($compra->total) ?></dd></div>
        </dl>
      </div>

      <div class="detalle-pedido__lateral">
        <div class="tarjeta">
          <h2 class="tarjeta__titulo">Resumen</h2>
          <dl class="datos">
            <div><dt>Estado</dt><dd><?php include __DIR__ . '/../templates/insignia-estado.php'; ?></dd></div>
            <div><dt>Folio</dt><dd><?= s($compra->folio) ?></dd></div>
            <div><dt>Fecha</dt><dd><?= s(fecha($compra->fecha)) ?></dd></div>
            <div><dt>Productos</dt><dd><?= (int) $compra->renglones ?></dd></div>
            <div><dt>Entregas</dt><dd><?= (int) $compra->total_entregas ?></dd></div>
            <div><dt>Fotos</dt><dd><?= (int) $compra->total_fotos ?></dd></div>
          </dl>
        </div>

        <div class="tarjeta ayuda-pedido">
          <p>¿Dudas sobre esta compra?</p>
          <a class="boton boton--contorno boton--bloque boton--chico" href="<?= s(whatsapp("Hola, tengo una duda sobre mi compra {$compra->folio}.")) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Escríbenos</a>
        </div>
      </div>
    </div>

    <section class="tarjeta" aria-labelledby="entregas-titulo">
      <h2 class="tarjeta__titulo" id="entregas-titulo"><?= icono('camion') ?> Entregas y actas</h2>

      <?php if (!$entregas): ?>
        <p class="nota">Todavía no hay entregas registradas para esta compra. Aquí aparecerán las fotos y el acta cuando se registre la entrega.</p>
      <?php else: ?>
        <div class="entregas-compra">
          <?php foreach ($entregas as $entrega): $fotosEntrega = $entrega->fotos(); ?>
            <article class="entrega-compra">
              <div class="entrega-compra__cabecera">
                <div>
                  <p class="etiqueta">Entrega</p>
                  <h3 class="entrega-compra__titulo"><?= s(fecha($entrega->fecha)) ?></h3>
                </div>
                <a class="boton boton--contorno boton--chico" href="<?= s($entrega->actaUrl()) ?>"><?= icono('ficha') ?> Ver acta</a>
              </div>

              <dl class="datos">
                <div><dt>Recibió</dt><dd><?= s($entrega->recibio()) ?></dd></div>
                <?php if ($entrega->notas): ?><div><dt>Notas</dt><dd><?= nl2br(s($entrega->notas)) ?></dd></div><?php endif; ?>
                <?php if ($entrega->mapa()): ?>
                  <div><dt>Ubicación</dt><dd><a class="texto-acento" href="<?= s($entrega->mapa()) ?>" target="_blank" rel="noopener"><?= icono('ubicacion') ?> Ver en Maps</a></dd></div>
                <?php endif; ?>
              </dl>

              <?php if ($fotosEntrega): ?>
                <div class="galeria">
                  <?php foreach ($fotosEntrega as $i => $foto): ?>
                    <button class="galeria__item" type="button" data-abrir-modal="foto-<?= (int) $foto['id'] ?>" aria-label="Ampliar foto <?= $i + 1 ?>">
                      <img src="/mi-cuenta/fotos/<?= (int) $foto['id'] ?>?w=400" alt="Foto <?= $i + 1 ?> de la entrega" loading="lazy">
                      <span class="galeria__fecha"><?= s(fecha($foto['tomada_en'] ?: $foto['fecha'])) ?></span>
                    </button>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="nota">Esta entrega no tiene fotos registradas.</p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>

        <?php foreach ($entregas as $entrega): ?>
          <?php foreach ($entrega->fotos() as $i => $foto): ?>
            <dialog class="modal modal--foto" id="foto-<?= (int) $foto['id'] ?>" aria-label="Foto <?= $i + 1 ?> de la entrega">
              <button class="modal__cerrar" type="button" data-cerrar-modal aria-label="Cerrar"><?= icono('cerrar') ?></button>
              <img src="/mi-cuenta/fotos/<?= (int) $foto['id'] ?>?w=1600" alt="Foto <?= $i + 1 ?> de la entrega" loading="lazy">
            </dialog>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</section>
