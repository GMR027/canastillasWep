<?php
/** @var \Model\Producto[] $productos */
/** @var int[] $galeria ids de foto de entrega */
$anios = (int) date('Y') - (int) sitio('desde');
$pasos = [
    ['icono' => 'seleccionar', 'titulo' => 'Elige', 'texto' => 'Revisa las medidas y la ficha técnica de la canastilla o el marco que necesita tu obra.'],
    ['icono' => 'pagar', 'titulo' => 'Paga y envía', 'texto' => 'Manda tu orden de compra y tu comprobante de pago por WhatsApp o correo electrónico.'],
    ['icono' => 'recibir', 'titulo' => 'Recibe', 'texto' => 'Coordinamos la entrega en La Paz, Los Cabos, Todos Santos o Ciudad Constitución.'],
];
?>
<section class="hero" aria-labelledby="hero-titulo">
  <div class="hero__media" style="--portada: url('<?= img('portada.jpg') ?>')">
    <video class="hero__video" autoplay muted loop playsinline preload="metadata" poster="<?= img('portada.jpg') ?>" aria-hidden="true">
      <source src="/src/video/Canastillas-V2-low.webm" type="video/webm">
      <source src="/src/video/Canastillas-V2-low.mp4" type="video/mp4">
    </video>
  </div>
  <div class="hero__overlay"></div>

  <div class="contenedor hero__contenido">
    <p class="etiqueta etiqueta--hero" data-animar>Desde <?= (int) sitio('desde') ?> · <?= s(sitio('ciudad')) ?></p>
    <h1 class="hero__titulo" id="hero-titulo" data-animar>
      Canastillas que <span class="texto-acento">soportan</span> tu obra
    </h1>
    <p class="hero__texto" data-animar>
      Canastillas C-1 y C-3 y marcos de concreto para vialidades.
      Fabricación con medidas listas para instalar y entrega en Baja California Sur.
    </p>
    <div class="hero__acciones" data-animar>
      <a class="boton boton--primario" href="#productos">Ver productos <?= icono('flecha') ?></a>
      <a class="boton boton--contorno" href="<?= s(whatsapp('Hola, quiero cotizar canastillas.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Cotizar por WhatsApp</a>
    </div>
  </div>

  <a class="hero__scroll" href="#nosotros" aria-label="Bajar a la siguiente sección"><span></span></a>
</section>

<section class="cifras" aria-label="Canastillas de la Baja en números">
  <div class="contenedor cifras__grid">
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= $anios ?>">+<?= $anios ?></span>
      <span class="cifra__texto">años en el mercado</span>
    </div>
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= (int) $totalProductos ?>"><?= (int) $totalProductos ?></span>
      <span class="cifra__texto">productos en catálogo</span>
    </div>
    <div class="cifra" data-animar>
      <span class="cifra__numero" data-contador="<?= count(sitio('cobertura')) ?>"><?= count(sitio('cobertura')) ?></span>
      <span class="cifra__texto">ciudades con entrega</span>
    </div>
  </div>
</section>

<section class="seccion nosotros" id="nosotros" aria-labelledby="nosotros-titulo">
  <div class="contenedor nosotros__grid">
    <div class="nosotros__imagen" data-animar>
      <img src="<?= img('nosotros.png') ?>" alt="Equipo de Canastillas de la Baja" width="671" height="574" loading="lazy">
      <div class="nosotros__sello">
        <span class="nosotros__sello-numero"><?= (int) sitio('desde') ?></span>
        <span class="nosotros__sello-texto">en Baja California Sur</span>
      </div>
    </div>

    <div class="nosotros__texto" data-animar>
      <p class="etiqueta">Sobre nosotros</p>
      <h2 class="titulo-seccion" id="nosotros-titulo">Tu aliado en concreto y acero</h2>
      <p class="texto-grande">
        Somos un equipo comprometido con la mejora continua, enfocado en ofrecer soluciones integrales para nuestro mercado.
        Desde <?= (int) sitio('desde') ?> nos hemos posicionado gracias a la calidad, la puntualidad y el trabajo en equipo, construyendo relaciones sólidas con nuestros clientes.
      </p>
      <ul class="valores">
        <li class="valor"><?= icono('escudo') ?><div><strong>Calidad</strong><span>Perfil redondo y ángulo con armado reforzado para cada tipo de vialidad.</span></div></li>
        <li class="valor"><?= icono('camion') ?><div><strong>Puntualidad</strong><span>Entregas coordinadas en los municipios de Baja California Sur.</span></div></li>
        <li class="valor"><?= icono('herramienta') ?><div><strong>Asesoría</strong><span>Te ayudamos a elegir entre C-1, C-3 o marco y contramarco según tu pavimento.</span></div></li>
      </ul>
    </div>
  </div>
</section>

<section class="seccion seccion--placa productos" id="productos" aria-labelledby="productos-titulo">
  <div class="contenedor">
    <header class="encabezado-seccion" data-animar>
      <p class="etiqueta">Catálogo</p>
      <h2 class="titulo-seccion" id="productos-titulo">Líneas de producto</h2>
      <p class="encabezado-seccion__texto">Cada producto incluye ficha técnica con medidas, perfil, armado y tipo de uso recomendado.</p>
    </header>

    <?php if (!$catalogoDisponible): ?>
      <p class="vacio">El catálogo no está disponible en este momento. <a class="texto-acento" href="<?= s(whatsapp('Hola, quiero información de sus productos.')) ?>" target="_blank" rel="noopener">Escríbenos por WhatsApp</a> y te ayudamos.</p>
    <?php endif; ?>

    <div class="productos-grid">
      <?php foreach ($productos as $i => $producto): ?>
        <?php $retraso = $i * 70; include __DIR__ . '/../templates/producto-card.php'; ?>
      <?php endforeach; ?>
    </div>

    <?php if (!$productos && $catalogoDisponible): ?>
      <p class="vacio">Pronto publicaremos el catálogo de Canastillas de la Baja. Mientras tanto, escríbenos por WhatsApp.</p>
    <?php endif; ?>
  </div>
</section>

<section class="seccion instalacion" id="instalacion" aria-labelledby="instalacion-titulo">
  <div class="contenedor instalacion__grid">
    <div class="instalacion__texto" data-animar>
      <p class="etiqueta">Guía de instalación</p>
      <h2 class="titulo-seccion" id="instalacion-titulo">Cómo colocar tus canastillas</h2>
      <p class="texto-grande">Revisa la separación, el sentido de las pasajuntas y el acomodo antes de colar. Una instalación correcta prolonga la vida de la vialidad.</p>
      <button class="boton boton--primario" type="button" data-abrir-modal="ficha-instalacion">Ver guía de instalación <?= icono('flecha') ?></button>
    </div>
    <div class="instalacion__imagen" data-animar>
      <img src="<?= img('instalacionCanastillas.png') ?>" alt="Guía de instalación de canastillas" loading="lazy">
    </div>
  </div>
</section>

<section class="seccion como-comprar" id="como-comprar" aria-labelledby="comprar-titulo">
  <div class="contenedor">
    <header class="encabezado-seccion" data-animar>
      <p class="etiqueta">Cómo comprar</p>
      <h2 class="titulo-seccion" id="comprar-titulo">Tu pedido en tres pasos</h2>
    </header>

    <ol class="pasos">
      <?php foreach ($pasos as $i => $paso): ?>
        <li class="paso" data-animar style="--retraso: <?= $i * 120 ?>ms">
          <span class="paso__numero"><?= $i + 1 ?></span>
          <span class="paso__icono"><?= icono($paso['icono']) ?></span>
          <h3 class="paso__titulo"><?= s($paso['titulo']) ?></h3>
          <p class="paso__texto"><?= s($paso['texto']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
    <p class="nota">*Consulta las restricciones de entrega y la disponibilidad según tu solicitud.</p>
  </div>
</section>

<section class="seccion cobertura" id="cobertura" aria-labelledby="cobertura-titulo">
  <div class="contenedor cobertura__grid">
    <div class="cobertura__texto" data-animar>
      <p class="etiqueta">Cobertura y entrega</p>
      <h2 class="titulo-seccion" id="cobertura-titulo">Del centro al sur de la península</h2>
      <p class="texto-grande">Entregamos nuestros productos en los municipios donde tenemos presencia y aliados de distribución.</p>
      <ul class="chips">
        <?php foreach (sitio('cobertura') as $ciudad): ?>
          <li class="chip"><?= icono('ubicacion') ?> <?= s($ciudad) ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="boton boton--primario" href="<?= s(whatsapp('Hola, quiero saber si entregan en mi ciudad.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Pregunta por tu ciudad</a>
    </div>
    <div class="cobertura__imagen" data-animar>
      <img src="<?= img('logistica.jpg') ?>" alt="Entrega de canastillas para obra" loading="lazy">
    </div>
  </div>
</section>

<?php if ($galeria): ?>
<section class="seccion entregas-galeria" id="entregas" aria-labelledby="galeria-titulo">
  <div class="contenedor">
    <header class="encabezado-seccion" data-animar>
      <p class="etiqueta">Galería</p>
      <h2 class="titulo-seccion" id="galeria-titulo">Entregas recientes</h2>
      <p class="encabezado-seccion__texto">Así llegan nuestras canastillas y marcos a las obras de Baja California Sur.</p>
    </header>

    <ul class="entregas-galeria__lista" data-galeria aria-label="Fotos de entregas recientes">
      <?php foreach ($galeria as $fotoId): ?>
        <li class="entregas-galeria__item">
          <img src="/img/entregas/<?= (int) $fotoId ?>" alt="Entrega de Canastillas de la Baja" loading="lazy" decoding="async">
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="seccion seccion--compacta cta-final">
  <div class="contenedor cta-final__contenido" data-animar>
    <div>
      <p class="etiqueta">Cotiza tu obra</p>
      <h2 class="titulo-seccion">¿Necesitas canastillas o marcos?</h2>
      <p>Envíanos las medidas y la cantidad. Te ayudamos a elegir el producto correcto.</p>
    </div>
    <a class="boton boton--primario" href="<?= s(whatsapp('Hola, quiero cotizar canastillas para mi obra.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Escríbenos</a>
  </div>
</section>

<?php foreach ($productos as $producto): ?>
  <?php include __DIR__ . '/../templates/modal-producto.php'; ?>
<?php endforeach; ?>

<dialog class="modal" id="ficha-instalacion" aria-labelledby="ficha-instalacion-titulo">
  <div class="modal__contenido">
    <button class="modal__cerrar" type="button" data-cerrar-modal aria-label="Cerrar guía de instalación"><?= icono('cerrar') ?></button>
    <div class="modal__media">
      <img src="<?= img('instalacionCanastillas.png') ?>" alt="Guía de instalación de canastillas" loading="lazy">
    </div>
    <div class="modal__info">
      <p class="etiqueta">Guía</p>
      <h2 class="modal__titulo" id="ficha-instalacion-titulo">Instalación de canastillas</h2>
      <p class="modal__subtitulo">Coloca las canastillas sobre la base compactada, respeta la separación y verifica el sentido de las pasajuntas antes de colar.</p>
      <div class="uso">
        <span class="uso__titulo">Recomendación</span>
        <p>Consulta con nosotros las restricciones de entrega y las medidas de tu pavimento.</p>
      </div>
      <div class="modal__botones">
        <a class="boton boton--primario boton--bloque" href="<?= s(whatsapp('Hola, tengo dudas sobre la instalación de las canastillas.')) ?>" target="_blank" rel="noopener"><?= icono('whatsapp') ?> Preguntar por WhatsApp</a>
      </div>
    </div>
  </div>
</dialog>
