<?php
include __DIR__ . '/../includes/app.php';
soloAdmin();
use App\Reportes;

// Leer los filtros de la URL
$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null; // Si no se especifica un filtro, se asigna null para mostrar todos los reportes. isset verifica si el parametro existe en la URL, y la segunda parte verifica que no esté vacío. Si ambos son verdaderos, se asigna el valor del filtro, de lo contrario se asigna null. Esto permite mostrar todos los reportes cuando no se aplican filtros.
$clienteFiltro   = (isset($_GET['cliente'])   && $_GET['cliente']   !== '') ? $_GET['cliente']   : null; //Lee el parametro cliente de la URL y lo asigna a $clienteFiltro, o null si no se especifica.
$ubicacionFiltro = (isset($_GET['ubicacion']) && $_GET['ubicacion'] !== '') ? $_GET['ubicacion'] : null; //Lee el parametro ubicacion de la URL y lo asigna a $ubicacionFiltro, o null si no se especifica.

// Datos para los selects
$clientes    = $db->query("SELECT id, nombre FROM usuarios WHERE rol = 2"); // rol 2 = Cliente
$ubicaciones = $db->query("SELECT id, nombre FROM ubicacion"); //Obtiene la lista de ubicaciones para el filtro, desde la tabla ubicacion.
$anioActual  = (int) date('Y'); //Obtiene el año actual para generar el rango de años en el filtro.
$anios       = range($anioActual, 2023); //Genera un array de años desde el año actual hasta 2023 para el filtro de años.

//Seccion para la paginacion de usuarios
$paginacion = paginacion(10, Reportes::contarNumReportes($anioFiltro, $clienteFiltro, $ubicacionFiltro));
$reportes = Reportes::mostrarTodos($anioFiltro, $clienteFiltro, $ubicacionFiltro, $paginacion['limite'], $paginacion['offset']);

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

<form action="" method="get" class="filtros contenedor"> <!-- Formulario para los filtros, con método GET para que los filtros se reflejen en la URL. -->
  <div>
    <select class="filtro-grupo" name="anio" id=""> <!-- Select para filtrar por año, con opción por defecto para mostrar todos los años. -->
    <option value="">--Filtro por año--</option>
    <?php foreach($anios as $year): ?> <!-- Itera sobre el array de años para generar las opciones del select. -->
      <option value="<?php echo $year; ?>" <?php echo ($anioFiltro == $year) ? 'selected' : ''; ?>> <!-- Marca como seleccionado el año que coincide con el filtro actual. -->
        <?php echo $year; ?> <!-- Muestra el año como texto de la opción. -->
      </option>
      <?php endforeach;
      ?>
    </select>

  <select class="filtro-grupo" name="cliente">
    <option value="">--Filtro por cliente--</option>
    <?php while($cliente = $clientes->fetch_assoc()): ?>
      <option value="<?php echo $cliente['id']; ?>" <?php echo ($clienteFiltro == $cliente['id']) ? 'selected' : ''; ?>>
        <?php echo $cliente['nombre']; ?>
      </option>
    <?php endwhile; ?>
  </select>

    <select class="filtro-grupo" name="ubicacion">
      <option value="">--Filtro por ubicacion--</option>
      <?php while($ubicacion = $ubicaciones->fetch_assoc()): ?>
        <option value="<?php echo $ubicacion['id']; ?>" <?php echo ($ubicacionFiltro == $ubicacion['id']) ? 'selected' : ''; ?>>
          <?php echo $ubicacion['nombre']; ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div>
    <button type="submit" class="button">Filtrar</button>
    <a href="/admin/index.php" class="button">Limpiar Filtro</a>
  </div>
</form>

<!-- Seccion de paginacion de reportes -->
<section>
  <h1>Ultimas entregas</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Cliente</th>
        <th class="info hMovilTablas">Producto</th>
        <th class="hMovilTablas">Cantidad</th>
        <th class="hMovilTablas">Imagen</th>
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
          <td class="foto-entrega-container hMovilTablas">
            <img src="/public/image/<?php echo $reporte->imagen; ?>" alt="Foto de entrega" class="foto-entrega">
          </td>
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
<?php require __DIR__ . '/../templates/paginacion.php'; ?>
</section>


<?php
 template('footer');
?>