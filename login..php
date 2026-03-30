<?php
include './includes/app.php';
$db = database();
$errores = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = $db->escape_string($_POST['correo']);
  $contrasena = $_POST['contrasena'];

  if(!$correo) {
    $errores[] = 'El correo es obligatorio';
  }
  if(!$contrasena) {
    $errores[] = 'La contraseña es obligatoria';
  }

  if(empty($errores)) {
    $query = "SELECT * FROM usuarios WHERE correo = '$correo'";
    $resultado = $db->query($query);
    if($resultado->num_rows) {
      $correoDB = $resultado->fetch_assoc();
      $autorizado = password_verify($contrasena, $correoDB['contrasena']);

      if($autorizado) {
        session_start();
        $_SESSION['id'] = $correoDB['id'];
        $_SESSION['nombre'] = $correoDB['nombre'];
        $_SESSION['rol'] = $correoDB['rol'];
        $_SESSION['empresa'] = $correoDB['empresa'];
        $_SESSION['login'] = true;
        
        if((int) $correoDB['rol'] === 1) {
          header('Location: /admin/index.php');
        } elseif((int) $correoDB['rol'] === 2) {
          header('Location: /public/cliente/index.php');
        } else {
          header('Location: /public/proveedor/index.php');
        }
        exit;
      } else {
        $errores[] = 'Contraseña incorrecta';
      }
    } else {
      $errores[] = 'El correo no existe'; 
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
    <label for="correo">Correo:</label>
    <input type="text" id="correo" name="correo">

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