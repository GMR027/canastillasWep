<?php
use App\Usuarios;

$paginacion = paginacion(10, Usuarios::contarTodos());
$usuarios = Usuarios::mostrarTodos($paginacion['limite'], $paginacion['offset']);

$mensaje = (int) ($_GET['st'] ?? 0);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $id = filter_var($id, FILTER_VALIDATE_INT);
  $usuario = Usuarios::buscarID($id);
  if($usuario) {
    $usuario->eliminar();
    header('Location: /admin/usuarios?st=3');
  } else {
    header('Location: /admin/usuarios');
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
    <a class="button hMovil" href="/admin/usuarios/crear">Crear Usuario</a>
    <a class="button" href="/logout">Cerrar Sesion</a>
  </div>
</div>

<section class="mensajes contenedor">
  <?php
    if($mensaje === 1) {
      echo '<p class="alerta exito">Usuario Creado Correctamente</p>';
    } else if($mensaje === 2) {
      echo '<p class="alerta actualizacion">Usuario Actualizado Correctamente</p>';
    } else if($mensaje === 3) {
      echo '<p class="alerta eliminacion">Usuario Eliminado Correctamente</p>';
    }
   ?>
</section>

<section class="contenedor">
  <h1>Usuarios Registrados</h1>
  <table class="tablas">
    <thead>
      <tr>
        <th class="hMovilTablas">ID</th>
        <th>Nombre</th>
        <th class="info">Correo</th>
        <th class="hMovilTablas">Teléfono</th>
        <th class="hMovilTablas">Empresa</th>
        <th class="hMovilTablas">Rol</th>
        <th class="hMovilTablas">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($usuarios as $usuario): ?>
        <tr>
          <td class="hMovilTablas"><?php echo $usuario->id; ?></td>
          <td class="info"><?php echo $usuario->nombre; ?></td>
          <td class="info"><?php echo $usuario->correo; ?></td>
          <td class="hMovilTablas"><?php echo $usuario->telefono; ?></td>
          <td class="hMovilTablas"><?php echo $usuario->empresa; ?></td>
          <td class="hMovilTablas">
            <?php
            $rolInfo = [
              1 => 'Admin',
              2 => 'Cliente',
              3 => 'Proveedor'
            ];
            ?>
            <span><?php echo $rolInfo[$usuario->rol] ?? 'Desconocido';  ?></span>
          </td>
          <td class="hMovilTablas">
            <div class="acciones">
              <a class="button editar" href="/admin/usuarios/editar?id=<?php echo $usuario->id; ?>">Editar</a>
              
               <button type="button" class="button eliminar hMovil" onclick="abrirModal(<?php echo $usuario->id?>)">Eliminar</button> 
              <div id="modal-overlay" style="display: none;">
                <div id="modal-box"> 
                  <p>¿Estás seguro de que deseas eliminar este usuario?</p> 
                  <form action="" id="form-eliminar" method="POST">
                    <input type="hidden" name="id" id="modal-id" value=""> 
                    <button type="button" onclick="cerrarModal()">Cancelar</button>  
                    <button type="submit">Aceptar</button>
                  </form>
                </div>
              </div>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php require TEMPLATES_URL . '/paginacion.php'; ?>
</section>


<?php
 template('footer');
?>
