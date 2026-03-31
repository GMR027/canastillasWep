
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


  $pedidos = Pedidos::mostrarTodos($anioFiltro, $estatusFiltro, $diasTransitoFiltro); //Obtiene los pedidos aplicando los filtros seleccionados por el usuario. Si un filtro es null, se mostrarán todos los pedidos para ese criterio.

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
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Fecha</th>
        <th>Num Pedido</th>
        <th class="info">Producto</th>
        <th>Cantidad</th>
        <th class="hMovilTablas">Costo sin IVA</th>
        <th class="hMovilTablas">Costo con IVA</th>
        <th class="hMovilTablas">Estatus</th>
        <th class="hMovilTablas">Fecha Embarque</th>
        <th class="hMovilTablas">Fecha Recibo</th>
        <th class="hMovilTablas">Días en Tránsito</th>
        <th class="hMovilTablas">Imagen</th>
        <th class="hMovilTablas">Comentarios</th>
        <th class="hMovilTablas">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pedidos as $pedido): ?>
      <tr>
        <td class="hMovilTablas"><?php echo $pedido->id; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->fechaFormateada(); ?></td>
        <td class="info"><?php echo $pedido->numeroPedido; ?></td>
        <td class="info"><?php echo $pedido->producto; ?></td>
        <td><?php echo $pedido->cantidad; ?></td> 
        <td class="hMovilTablas">$<?php echo $pedido->costoSinIva; ?>.00 MXN</td>
        <td class="hMovilTablas">$<?php echo $pedido->costoConIva; ?>.00 MXN</td>
        <td class="hMovilTablas indicadores">
          <?php 
            $estadusInfo = [ // Agrega un array para mapear los estatus a texto y clases CSS.
              1 => ['texto' => 'Pendiente', 'clase' => 'pendiente'],
              2 => ['texto' => 'En tránsito', 'clase' => 'en-transito'],
              3 => ['texto' => 'Entregado', 'clase' => 'entregado'],
              4 => ['texto' => 'Cancelado', 'clase' => 'cancelado'],
              5 => ['texto' => 'Pagado', 'clase' => 'pagado']
            ];
            $info = $estadusInfo[$pedido->estatus] ?? ['texto' => 'Desconocido', 'clase' => 'desconocido']; //Si el estatus no coincide con ninguno definido, muestra "Desconocido" y una clase por defecto.
          ?>
          <span class="indicadores <?php echo $info['clase']; ?>"><?php echo $info['texto']; ?></span>
        </td>
        <td class="hMovilTablas"><?php echo $pedido->fechaEmbarque ? date('d/m/Y', strtotime($pedido->fechaEmbarque)) : ''; ?></td>
        <td class="hMovilTablas"><?php echo $pedido->fechaRecibo ? date('d/m/Y', strtotime($pedido->fechaRecibo)) : ''; ?></td>

        <td class="hMovilTablas">
          <?php if($pedido->diasEnTransito() !== ''): ?>
            <span class="dias-transito <?php echo $pedido->claseTransito(); ?>">
              <?php echo $pedido->diasEnTransito() . ' días'; ?>
            </span>
          <?php endif; ?>
        </td>


        <td class="foto-entrega-container hMovilTablas">
          <img src="/public/image/<?php echo $pedido->imagen; ?>" alt="Foto de pedido" class="foto-entrega">
        </td>
        <td class="hMovilTablas"><?php echo nl2br(escaparValores($pedido->comentarios)); ?></td>
        <td class="hMovilTablas">
          <div class="acciones">
            <a class="button editar" href="editarPedidos.php?id=<?php echo $pedido->id; ?>">Editar</a>
            <form action="" method="POST">
              <input type="hidden" name="id" value="<?php echo $pedido->id; ?>">
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