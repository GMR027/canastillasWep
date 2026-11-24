<?php
/** @var \Model\Cliente $cliente */
/** @var \Model\Compra $compra */
/** @var \Model\Entrega $entrega */
/** @var array $detalles */
/** @var array $renglones */
/** @var array $fotos */
$totalFotos = count($fotos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acta de entrega <?= s($compra->folio) ?> | <?= s(sitio('nombre')) ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="<?= asset('css/sitio.css') ?>">
</head>
<body class="pagina-acta">
  <main class="acta">
    <div class="acta__acciones">
      <a class="boton boton--contorno boton--chico" href="/mi-cuenta/compras/<?= $compra->id ?>"><?= icono('flecha-izq') ?> Regresar</a>
      <button class="boton boton--primario boton--chico" type="button" onclick="window.print()"><?= icono('descargar') ?> Imprimir acta</button>
    </div>

    <header class="acta__cabecera">
      <div class="acta__marca">
        <img src="<?= img('logo-canastillas.png') ?>" alt="<?= s(sitio('nombre')) ?>" width="260" height="47">
        <h1>Acta de entrega</h1>
        <p class="acta__negocio">
          <?= s(sitio('nombre')) ?><br>
          <?= s(sitio('ciudad')) ?><br>
          <?= s(sitio('telefono')) ?> · <?= s(sitio('correo')) ?>
        </p>
      </div>
      <div class="acta__folio">
        <strong><?= s($compra->folio) ?></strong>
        <p>Compra: <?= s(fecha($compra->fecha)) ?></p>
        <p>Entrega: <?= s(fecha($entrega->fecha)) ?></p>
        <p>Acta generada: <?= s(date('d/m/Y H:i')) ?></p>
        <p><?= $totalFotos ?> foto<?= $totalFotos === 1 ? '' : 's' ?></p>
      </div>
    </header>

    <section class="acta__bloque">
      <h2>Cliente</h2>
      <p><strong><?= s($cliente->nombreVisible()) ?></strong>
        <?php if ($cliente->nombre !== '' && $cliente->nombre !== $cliente->nombreVisible()): ?> · contacto: <?= s($cliente->nombre) ?><?php endif; ?>
      </p>
      <p class="acta__chico">
        <?php if ($cliente->telefono): ?>Tel. <?= s($cliente->telefono) ?> · <?php endif; ?>
        <?php if ($cliente->email): ?><?= s($cliente->email) ?><?php endif; ?>
      </p>
    </section>

    <section class="acta__bloque">
      <h2>Estado de la compra</h2>
      <p>
        <span class="acta__estado"><?= s($compra->etiquetaEstado()) ?></span>
        · Entrega del <?= s(fecha($entrega->fecha)) ?>
        <?php if ($entrega->recibio_nombre): ?> · Recibió: <?= s($entrega->recibio()) ?><?php endif; ?>
      </p>
      <p class="acta__chico">
        <?php if ($compra->total_entregas > 0): ?><?= (int) $compra->total_entregas ?> entrega<?= $compra->total_entregas === 1 ? '' : 's' ?> registrada<?= $compra->total_entregas === 1 ? '' : 's' ?> · <?php endif; ?>
        <?= $totalFotos ?> foto<?= $totalFotos === 1 ? '' : 's' ?> del acta
      </p>
    </section>

    <section class="acta__bloque">
      <h2>Datos de la entrega</h2>
      <div class="acta__datos">
        <p><span>Fecha de entrega</span><strong><?= s(fecha($entrega->fecha)) ?></strong></p>
        <p><span>Recibió</span><strong><?= s($entrega->recibio()) ?></strong></p>
        <?php if ($entrega->mapa()): ?><p><span>Ubicación</span><strong><?= s($entrega->mapa()) ?></strong></p><?php endif; ?>
      </div>
      <?php if ($entrega->notas): ?>
        <p class="acta__notas"><strong>Comentarios:</strong><br><?= nl2br(s($entrega->notas)) ?></p>
      <?php endif; ?>
    </section>

    <section class="acta__bloque">
      <h2>Productos entregados</h2>
      <?php if ($renglones): ?>
        <table class="acta__tabla">
          <thead>
            <tr><th>Producto</th><th>Código</th><th class="num">Cantidad entregada</th></tr>
          </thead>
          <tbody>
            <?php foreach ($renglones as $renglon): ?>
              <tr>
                <td><?= s($renglon['nombre']) ?></td>
                <td><?= s($renglon['sku'] ?: '—') ?></td>
                <td class="num"><?= (int) $renglon['cantidad'] ?> <?= s($renglon['unidad']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="acta__chico">Sin renglones de productos en esta entrega.</p>
      <?php endif; ?>
    </section>

    <section class="acta__bloque">
      <h2>Fotos del acta</h2>
      <?php if ($fotos): ?>
        <div class="acta__fotos">
          <?php foreach ($fotos as $i => $foto): ?>
            <figure>
              <img src="/mi-cuenta/fotos/<?= (int) $foto['id'] ?>?w=400" alt="Foto <?= $i + 1 ?> de la entrega">
              <figcaption><?= s(fecha($foto['tomada_en'] ?: $foto['fecha'])) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="acta__chico">Esta entrega no tiene fotos registradas.</p>
      <?php endif; ?>
    </section>

    <footer class="acta__pie">
      <p><?= s(sitio('nombre')) ?> · <?= s(sitio('ciudad')) ?> · <?= s(sitio('telefono')) ?></p>
      <p>Acta generada desde el portal de clientes de <?= s(sitio('nombre')) ?>. Las fechas de las fotos sin EXIF corresponden al registro de la entrega.</p>
    </footer>
  </main>
</body>
</html>
