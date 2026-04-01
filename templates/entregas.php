<?php
use App\Reportes;
$entregas = Reportes::mostrarEntregasLimitadoPaginaPrincipal($limite);


?>
<div class="contenedor-entregas">
  <?php foreach($entregas as $entrega): ?>
    <div class="entrega">
      <img src="/public/image/<?php echo $entrega->imagen; ?>" alt="">
      <div class="informacion-entrega">
        <p>Ubicacion: <?php echo $entrega->ubicacion_nombre; ?></p>
        <p>Cantidad: <?php echo $entrega->cantidad; ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>
