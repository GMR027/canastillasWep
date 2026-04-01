
<?php
  require __DIR__ . '/../../includes/app.php';
  soloAdmin();
  use App\Pedidos;

// Leer los filtros de la URL
$anioFiltro      = (isset($_GET['anio'])      && $_GET['anio']      !== '') ? $_GET['anio']      : null; // Si no se especifica un filtro, se asigna null para mostrar todos los reportes. isset verifica si el parametro existe en la URL, y la segunda parte verifica que no esté vacío. Si ambos son verdaderos, se asigna el valor del filtro, de lo contrario se asigna null. Esto permite mostrar todos los reportes cuando no se aplican filtros.
$estatusFiltro = (isset($_GET['estatus']) && $_GET['estatus'] !== '') ? $_GET['estatus'] : null; //Lee el parametro estatus de la URL y lo asigna a $estatusFiltro, o null si no se especifica.
$diasTransitoFiltro = (isset($_GET['dias_transito']) && $_GET['dias_transito'] !== '') ? $_GET['dias_transito'] : null; //Lee el parametro dias_transito de la URL y lo asigna a $diasTransitoFiltro, o null si no se especifica.

// Datos para los selects
$anioActual  = (int) date('Y'); //Obtiene el año actual para generar el rango de años en el filtro.
$anios       = range($anioActual, 2023); //Genera un array de años desde el año actual hasta 2023 para el filtro de años.

// El estatus no es una tabla — se define como array PHP
$estatusOpciones = [ // Define un array para mapear los estatus a texto legible. El valor 0 se usa para "Todos" en el filtro, y los valores 1-5 corresponden a los estatus definidos en la base de datos.
    1 => 'Pendiente',
    2 => 'En tránsito',
    3 => 'Entregado',
    4 => 'Cancelado',
    5 => 'Pagado'
];

$estadoTransitoOpciones = [
    0 => 'Todos',
    1 => '0 a 7 días',
    2 => '9 a 15 días',
    3 => 'Más de 15 días'
];

//Seccion para la paginacion de usuarios
$paginacion = paginacion(10, Pedidos::contarPedidos($anioFiltro, $estatusFiltro, $diasTransitoFiltro));
$pedidos = Pedidos::mostrarTodos($anioFiltro, $estatusFiltro, $diasTransitoFiltro, $paginacion['limite'], $paginacion['offset']);


  $mensaje = (int) ($_GET['st'] ?? 0);

  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $pedido = Pedidos::buscarID($id);
    if($pedido) {
      $pedido->eliminar();
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
    <a class="button" href="crearPedidos.php">Crear</a>
    <a class="button" href="/admin/index.php">Regresar</a>
    <a class="button" href="/cerrar-sesion.php">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajes contenedor">
  <?php
    if($mensaje === 1) {
      echo '<p class="alerta exito">Pedido Creado Correctamente</p>';
    } else if($mensaje === 2) {
      echo '<p class="alerta actualizacion">Pedido Actualizado Correctamente</p>';
    } else if($mensaje === 3) {
      echo '<p class="alerta eliminacion">Pedido Eliminado Correctamente</p>';
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

    <select class="filtro-grupo" name="estatus">
      <option value="">--Filtro por Estatus--</option>
      <?php foreach($estatusOpciones as $id => $nombre): ?>// Itera sobre el array de estatus para generar las opciones del select. El valor del option es el ID del estatus, y el texto mostrado es el nombre legible.
        <option value="<?php echo $id; ?>" <?php echo ($estatusFiltro == $id) ? 'selected' : ''; ?>> <!-- Marca como seleccionado el estatus que coincide con el filtro actual. -->
          <?php echo $nombre; ?> <!-- Muestra el nombre del estatus como texto de la opción. -->
        </option>
      <?php endforeach; ?>
    </select>

    <select class="filtro-grupo" name="dias_transito">
      <option value="">--Filtro por Días en Tránsito--</option>
      <?php foreach($estadoTransitoOpciones as $id => $nombre): ?> // Itera sobre el array de estados de tránsito para generar las opciones del select. El valor del option es el ID del estado, y el texto mostrado es el nombre legible.
        <option value="<?php echo $id; ?>" <?php echo ($diasTransitoFiltro == $id) ? 'selected' : ''; ?>> <!-- Marca como seleccionado el estado de tránsito que coincide con el filtro actual. -->
          <?php echo $nombre; ?> <!-- Muestra el nombre del estado de tránsito como texto de la opción. -->
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <button type="submit" class="button">Filtrar</button>
    <a href="/admin/pedidos/index.php" class="button">Limpiar Filtro</a>
  </div>
</form>


<section>
  <h1>Ultimos pedidos</h1>
  <?php require __DIR__ . '/../../templates/infoPedidos.php'; ?>
  <?php require __DIR__ . '/../../templates/paginacion.php'; ?>
</section>

<?php
 template('footer');
?>