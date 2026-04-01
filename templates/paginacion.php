  <?php
    $_paginaActual = $paginacion['paginaActual'];
    $_totalPaginas = $paginacion['totalPaginas'];
    $_anio = $anioFiltro ?? '';
    $_cliente = $clienteFiltro ?? '';
    $_ubicacion = $ubicacionFiltro ?? '';
  ?>
  <div class="paginacion contenedor">
    <?php if($_paginaActual > 1): ?>
      <a href="?pagina=<?php echo $_paginaActual - 1; ?>&anio=<?php echo $_anio; ?>&cliente=<?php echo $_cliente; ?>&ubicacion=<?php echo $_ubicacion; ?>" class="button">Anterior</a>
    <?php endif; ?>

    <?php for($i = 1; $i <= $_totalPaginas; $i++): ?>
      <a href="?pagina=<?php echo $i; ?>&anio=<?php echo $_anio; ?>&cliente=<?php echo $_cliente; ?>&ubicacion=<?php echo $_ubicacion; ?>" class="button <?php echo ($i == $_paginaActual) ? 'activo' : ''; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>

    <?php if($_paginaActual < $_totalPaginas): ?>
      <a href="?pagina=<?php echo $_paginaActual + 1; ?>&anio=<?php echo $_anio; ?>&cliente=<?php echo $_cliente; ?>&ubicacion=<?php echo $_ubicacion; ?>" class="button">Siguiente</a>
    <?php endif; ?>
  </div>