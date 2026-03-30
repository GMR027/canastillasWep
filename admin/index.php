<?php
include __DIR__ . '/../includes/app.php';
soloAdmin();
use App\Reportes;
$reportes = Reportes::mostrarTodos();

// Lee el parametro st de la URL y lo convierte a entero para comparar con ===.
$mensaje = (int) ($_GET['st'] ?? 0);

//Seccion eliminacion de reporte
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $id = filter_var($id, FILTER_VALIDATE_INT);
  $reporte = Reportes::buscarID($id);
  if($reporte) {
    $reporte->eliminar();
    header('Location: index.php?st=3');
  } else {
    header('Location: index.php');
  }
}
 template('headerHTML');
?>

<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="/src/img/LogoWeb.png" alt="">
  </div>
  <div class="navbar links-admin">
    <a class="button" href="/admin/reportes/crear.php">Reporte</a>
    <a class="button" href="/admin/pedidos/index.php">Pedido</a>
    <a class="button hMovil" href="/admin/usuarios/index.php">Usuario</a>
    <a class="button hMovil" href="/admin/productos/index.php">Productos</a>
    <a class="button" href="/cerrar-sesion.php">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajes contenedor">
  <?php
    if($mensaje === 1) {
      echo '<p class="alerta exito">Reporte Creado Correctamente</p>';
    } else if($mensaje === 2) {
      echo '<p class="alerta actualizacion">Reporte Actualizado Correctamente</p>';
    } else if($mensaje === 3) {
      echo '<p class="alerta eliminacion">Reporte Eliminado Correctamente</p>';
    }
   ?>
</section>

<section class="contenedor">
  <h1>Ultimas entregas</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Cliente</th>
        <th class="info hMovilTablas">Producto</th>
        <th class="hMovilTablas">Cantidad</th>
        <th>Reporte</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($reportes as $reporte): ?>
        <tr>
          <td class="hMovilTablas"><?php echo $reporte->id; ?></td>
            <!-- Muestra la fecha en formato dia/mes_en_texto/anio (ejemplo: 28/marzo/2026). -->
            <td class="hMovilTablas"><?php echo $reporte->fechaFormateada(); ?></td>
            <!-- Muestra el nombre del cliente obtenido con JOIN a la tabla usuarios. -->
            <td class="info"><?php echo $reporte->nombre_cliente; ?></td>
            <!-- Muestra el nombre del producto obtenido con JOIN a la tabla productos. -->
            <td class="info hMovilTablas"><?php echo $reporte->nombre_producto; ?></td>
          <td class="hMovilTablas"><?php echo $reporte->cantidad; ?></td> 
          <td class="hMovilTablas acciones">
            <a class="button" href="/admin/reportes/detalleReportesAdmin.php?id=<?php echo $reporte->id; ?>">Reporte</a>
            <a href="/admin/reportes/recibo.php?id=<?php echo $reporte->id; ?>" class="button">Imprimir Recibo</a>
          </td>
          <td>
            <div class="acciones">
                <a class="button editar" href="/admin/reportes/editarReportes.php?id=<?php echo $reporte->id; ?>">Editar</a>
                <form action="" method="POST">
                  <input type="hidden" name="id" value="<?php echo $reporte->id; ?>">
                <button type="submit" class="button eliminar">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>


<?php
 template('footer');
?>