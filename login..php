<?php
include './includes/app.php';
$db = database();
$errores = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $telefono = $db->escape_string($_POST['telefono']);
  $contrasena = $_POST['contrasena'];

  if(!$telefono) {
    $errores[] = 'El teléfono es obligatorio';
  }
  if(!$contrasena) {
    $errores[] = 'La contraseña es obligatoria';
  }

  if(empty($errores)) {
    $query = "SELECT * FROM usuarios WHERE telefono = '$telefono'";
    $resultado = $db->query($query);
    if($resultado->num_rows) {
      $telefonoDB = $resultado->fetch_assoc();
      $autorizado = password_verify($contrasena, $telefonoDB['contrasena']);

      if($autorizado) {
        session_start();
        $_SESSION['id'] = $telefonoDB['id'];
        $_SESSION['nombre'] = $telefonoDB['nombre'];
        $_SESSION['rol'] = $telefonoDB['rol'];
        $_SESSION['empresa'] = $telefonoDB['empresa'];
        $_SESSION['login'] = true;
        
        if((int) $telefonoDB['rol'] === 1) {
          header('Location: /admin/index.php');
        } elseif((int) $telefonoDB['rol'] === 2) {
          header('Location: /public/cliente/index.php');
        } else {
          header('Location: /public/proveedor/index.php');
        }
        exit;
      } else {
        $errores[] = 'Contraseña incorrecta';
      }
    } else {
      $errores[] = 'El teléfono no existe'; 
  }
}
}

template('headerHTML');
?>

<body>
<div class="navbar-admin">
  <div class="titulo">
    <img src="src/img/LogoWeb.png" alt="">
  </div>
  <div class="links">
    <a href="/">Regresar</a>
  </div>
</div>

<section class="mensajesError contenedor">
  <?php foreach($errores as $error) :?>
    <div class="error">
      <?php echo $error;?>
    </div>
  <?php endforeach; ?>
</section>

<section class="contenedor">
  <h1>Login</h1>
  <div class="login">
  <form class="formulario" action="" method="post">
    <label for="telefono">Teléfono:</label>
    <input type="text" id="telefono" name="telefono">

    <label for="contrasena">Contraseña:</label>
    <input type="password" id="contrasena" name="contrasena">

    <div class="flex-center">
      <button type="submit">Iniciar Sesión</button>
    </div>
  </form>
  </div>
<?php
 template('footer');
?>