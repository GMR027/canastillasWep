<?php
use App\Productos;
$productos = Productos::mostrarTodos();

$mensaje = (int) ($_GET['st'] ?? 0);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $id = filter_var($id, FILTER_VALIDATE_INT);
  $producto = Productos::buscarID($id);
  if($producto) {
    $producto->eliminar();
    header('Location: /admin/productos?st=3');
  } else {
    header('Location: /admin/productos');
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
    <a class="button" href="/admin">Regresar</a>
    <a class="button" href="/admin/productos/crear">Crear Producto</a>
    <a class="button" href="/logout">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajes contenedor">
  <?php
    if($mensaje === 1) {
      echo '<p class="alerta exito">Producto Creado Correctamente</p>';
    } else if($mensaje === 2) {
      echo '<p class="alerta actualizacion">Producto Actualizado Correctamente</p>';
    } else if($mensaje === 3) {
      echo '<p class="alerta eliminacion">Producto Eliminado Correctamente</p>';
    }
   ?>
</section>

<section class="contenedor">
  <h1>Productos Registrados</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th class="hMovilTablas">Nombre</th>
        <th>Descripcion</th>
        <th class="hMovilTablas">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($productos as $producto): ?>
        <tr>
          <td class="hMovilTablas"><?php echo $producto->id; ?></td>
          <td class="hMovilTablas"><?php echo $producto->nombre; ?></td>
          <td class="info"><?php echo $producto->descripcion; ?></td>
          <td class="hMovilTablas">
            <div class="acciones">
              <a class="button editar" href="/admin/productos/editar?id=<?php echo $producto->id; ?>">Editar</a>
              <form action="" method="POST">
                <input type="hidden" name="id" value="<?php echo $producto->id; ?>">
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
