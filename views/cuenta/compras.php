<?php
/** @var array $lista */
/** @var \Model\Cliente $cliente */
$tituloCuenta = 'Mis compras';
$totalTodas = (int) ($lista['conteos']['todas'] ?? 0);
$subtituloCuenta = $totalTodas === 0 ? '' : $totalTodas . ' compra' . ($totalTodas === 1 ? '' : 's') . ' con Canastillas de la Baja';
$pestana = 'compras';
$filtroActivo = (string) ($lista['filtro'] ?? 'todas');
$conteos = $lista['conteos'] ?? [];

$urlFiltro = static function (string $clave) use ($filtroActivo): string {
    return '/mi-cuenta/compras' . ($clave === 'todas' ? '' : '?filtro=' . rawurlencode($clave));
};
$urlPagina = static function (int $pagina) use ($filtroActivo): string {
    return '/mi-cuenta/compras?' . http_build_query(array_filter([
        'filtro' => $filtroActivo !== 'todas' ? $filtroActivo : null,
        'pagina' => $pagina,
    ]));
};
$textosVacio = [
    'en-proceso' => ['No tienes compras en proceso', 'Aquí verás las compras que estamos preparando o que están pendientes de entrega.'],
    'entregadas' => ['Aún no tienes compras entregadas', 'Cuando recibas una compra aparecerá en esta pestaña, con sus fotos y su acta.'],
    'canceladas' => ['No tienes compras canceladas', 'Las compras canceladas aparecerán en esta pestaña.'],
    'todas' => ['Aún no tienes compras registradas', 'Cuando tengas una compra con Canastillas de la Baja aparecerá aquí. Si necesitas ayuda, escríbenos por WhatsApp.'],
];
[$vacioTitulo, $vacioTexto] = $textosVacio[$filtroActivo] ?? $textosVacio['todas'];
include __DIR__ . '/../templates/cuenta-header.php';
?>

<section class="seccion seccion--compacta">
  <div class="contenedor">
    <?php if ($totalTodas === 0): ?>
      <div class="vacio-cuenta tarjeta">
        <span class="vacio-cuenta__icono"><?= icono('paquete') ?></span>
        <h2 class="titulo-seccion">Aún no tienes compras</h2>
        <p>Cuando registremos una compra a tu nombre aparecerá aquí con su estatus, fotos y acta de entrega.</p>
        <a class="boton boton--primario" href="<?= s(whatsapp('Hola, quiero consultar mis compras con Canastillas de la Baja.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Escríbenos</a>
      </div>
    <?php else: ?>
      <nav class="filtros-pedidos" aria-label="Filtrar compras">
        <?php foreach (\Model\Compra::FILTROS as $clave => $texto): $activo = $filtroActivo === $clave; ?>
          <a class="filtro-pedido <?= $activo ? 'is-activo' : '' ?>" href="<?= s($urlFiltro($clave)) ?>" <?= $activo ? 'aria-current="page"' : '' ?>>
            <?= s($texto) ?><span><?= (int) ($conteos[$clave] ?? 0) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <?php if (!$lista['compras']): ?>
        <div class="vacio-cuenta tarjeta">
          <span class="vacio-cuenta__icono"><?= icono('paquete') ?></span>
          <h2 class="titulo-seccion"><?= s($vacioTitulo) ?></h2>
          <p><?= s($vacioTexto) ?></p>
          <?php if ($filtroActivo !== 'todas'): ?>
            <a class="boton boton--contorno" href="/mi-cuenta/compras">Ver todas las compras</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="tabla-pedidos" role="table" aria-label="Mis compras">
          <div class="tabla-pedidos__fila tabla-pedidos__cabecera" role="row">
            <span role="columnheader">Folio</span>
            <span role="columnheader">Fecha</span>
            <span role="columnheader">Productos</span>
            <span role="columnheader">Estado</span>
            <span role="columnheader" class="a-la-derecha">Total</span>
            <span role="columnheader"><span class="sr-only">Detalle</span></span>
          </div>
          <?php foreach ($lista['compras'] as $compra): ?>
            <a class="tabla-pedidos__fila" role="row" href="/mi-cuenta/compras/<?= $compra->id ?>">
              <span role="cell" class="tabla-pedidos__folio"><?= s($compra->folio) ?></span>
              <span role="cell" class="tabla-pedidos__fecha" data-etiqueta="Fecha"><?= s(fecha($compra->fecha)) ?></span>
              <span role="cell" class="tabla-pedidos__productos" data-etiqueta="Productos"><?= (int) $compra->renglones ?></span>
              <span role="cell" class="tabla-pedidos__estado"><?php include __DIR__ . '/../templates/insignia-estado.php'; ?></span>
              <span role="cell" class="a-la-derecha tabla-pedidos__total"><?= dinero($compra->total) ?></span>
              <span role="cell" class="tabla-pedidos__ir"><?= icono('flecha') ?></span>
            </a>
          <?php endforeach; ?>
        </div>

        <?php if ($lista['paginas'] > 1): ?>
          <nav class="paginacion" aria-label="Páginas">
            <?php for ($p = 1; $p <= $lista['paginas']; $p++): ?>
              <a class="paginacion__enlace <?= $p === $lista['pagina'] ? 'is-activa' : '' ?>" href="<?= s($urlPagina($p)) ?>" <?= $p === $lista['pagina'] ? 'aria-current="page"' : '' ?>><?= $p ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
