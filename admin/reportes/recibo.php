<?php
require '../../includes/app.php';
 soloAdmin();
use App\Reportes;
validarID($_GET['id']);
$reporte = Reportes::buscarID($_GET['id']);
 template('headerHTML');
?>

<body>
<div class="contenedor-Impresion">
<div class="recibo">
  <div class="recibo-contenido">
      <div class="print-header">
        <img src="/src/img/LogoWeb.png" alt="">
        <h3>Recibo de Entrega</h3>
        <p>Soluciones en infraestructura de obra vial</p>
      </div>

      <section>
        <P>Entregado a: <?php echo $reporte->nombre_empresa; ?></P>

        <!-- Inicio de iteracion de entregas -->
        <p class="hTabletTablas">Fecha: <?php echo $reporte->fecha; ?></p>
        <p class="hMovilTablas">Lugar de entrega: <?php echo $reporte->ubicacion_nombre; ?></p>
        <table class="tablas">
          <thead>
            <tr>
              <th class="hMovilTablas">ID</th>
              <th class="hMovilTablas">Fecha</th>
              <th class="info">Producto</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
              <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
              <td class="info"><?php echo $reporte->nombre_producto; ?></td>
              <td><?php echo $reporte->cantidad; ?></td> 
          </tbody>
        </table>
      </section>

      <section class="descripcion">
        <div>Descripción: <?php echo $reporte->descripcion_producto; ?></div>
      </section>
  </div>

  <div class="recibo-contenido footer-recibo">
    <div class="recibo-notas">
      <span>Notas:</span>
      <div class="linea-escritura"></div>
      <div class="linea-escritura"></div>
      <div class="linea-escritura"></div>
    </div>
    <div class="recibo-firmas">
      <div class="firma">
        <div class="linea-firma"></div>
        <span>Firma del cliente</span>
      </div>
    </div>
  </div>
</div>
<div class="recibo">
  <div class="recibo-contenido">
      <div class="print-header">
        <img src="/src/img/LogoWeb.png" alt="">
        <h3>Recibo de Entrega</h3>
        <p>Soluciones en infraestructura de obra vial</p>
      </div>

      <section>
        <P>Entregado a: <?php echo $reporte->nombre_empresa; ?></P>

        <!-- Inicio de iteracion de entregas -->
        <p class="hTabletTablas">Fecha: <?php echo $reporte->fecha; ?></p>
        <p class="hMovilTablas">Lugar de entrega: <?php echo $reporte->ubicacion_nombre; ?></p>
        <table class="tablas">
          <thead>
            <tr>
              <th class="hMovilTablas">ID</th>
              <th class="hMovilTablas">Fecha</th>
              <th class="info">Producto</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
              <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
              <td class="info"><?php echo $reporte->nombre_producto; ?></td>
              <td><?php echo $reporte->cantidad; ?></td> 
          </tbody>
        </table>
      </section>

      <section class="descripcion">
        <div>Descripción: <?php echo $reporte->descripcion_producto; ?></div>
      </section>
  </div>

  <div class="recibo-contenido footer-recibo">
    <div class="recibo-notas">
      <span>Notas:</span>
      <div class="linea-escritura"></div>
      <div class="linea-escritura"></div>
      <div class="linea-escritura"></div>
    </div>
    <div class="recibo-firmas">
      <div class="firma">
        <div class="linea-firma"></div>
        <span>Firma del cliente</span>
      </div>
    </div>
  </div>
</div>
</div>
