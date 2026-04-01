<?php

namespace App;

class Usuarios {
  protected static $db;
  public static function setDB($database) {
    self::$db = $database;
  }
  protected static $columnasDB = ['id', 'nombre', 'telefono', 'correo', 'rol', 'contrasena', 'empresa'];
  protected static $errores = [];


  public $id;
  public $nombre;
  public $telefono;
  public $correo;
  public $rol;
  public $contrasena;
  public $empresa;


  public function __construct($args = []) {
    $this->id = $args['id'] ?? null;
    $this->nombre = $args['nombre'] ?? '';
    $this->telefono = $args['telefono'] ?? '';
    $this->correo = $args['correo'] ?? '';
    $this->rol = $args['rol'] ?? '';
    $this->contrasena = $args['contrasena'] ?? '';
    $this->empresa = $args['empresa'] ?? '';

  }

  //CRUD

  public function atributos() {
    $atributos = [];
    foreach(self::$columnasDB as $columna) {
      if($columna === 'id') continue;
      $atributos[$columna] = $this->$columna;
    }
    return $atributos;
  }

  public function sanitizarAtributos() {
    $atributos = $this->atributos();
    $sanitizado = [];
    foreach($atributos as $key => $value) {
      $sanitizado[$key] = self::$db->escape_string($value);
    }
    return $sanitizado;
  }

  public function guardar() {
    if(!is_null($this->id)) {
      $this->actualizar();
    } else {
      $this->crear();
    }
  }

  public function crear() {
    $this->contrasena = password_hash($this->contrasena, PASSWORD_BCRYPT); // Encripta la contraseña antes de guardarla.
    $atributos = $this->sanitizarAtributos();
    $columnas = join(', ', array_keys($atributos));
    $valores = join("', '", array_values($atributos));

    $query = "INSERT INTO usuarios" . " ($columnas) VALUES ('$valores')";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/usuarios/index.php?st=1');
    }
  }

  //Apartado de actualizar
  public static function buscarID($id) {
    // Convertimos $id a entero para evitar SQL injection,
    // ya que este valor viene directamente de $_GET['id'].
    $id = intval($id);
    $query = "SELECT * FROM usuarios WHERE usuarios.id = $id";

    $resultado = self::consultaSQL($query);
    // array_shift devuelve el primer (y único) elemento del arreglo
    return array_shift($resultado);
  }

  public function sincronizarCambios($args = []) {
    foreach($args as $key => $value) {
      if(property_exists($this, $key) && !is_null($value)) {
        $this->$key = $value;
      }
    }
  }

  public function actualizar() {
    if(!empty($this->contrasena)) { //esto permite actualizar la contraseña solo si se envió una nueva, de lo contrario se mantiene la existente.
      $this->contrasena = password_hash($this->contrasena, PASSWORD_BCRYPT);
    }
    $atributos = $this->sanitizarAtributos();
    // Si no se envió nueva contraseña, excluirla del UPDATE
    if(empty($this->contrasena)) {
      unset($atributos['contrasena']);
    }
    $valores = [];
    foreach($atributos as $key => $value) {
      $valores[] = "$key = '$value'";
    }
    $valores = join(', ', $valores);
    $query = "UPDATE usuarios SET $valores WHERE id = '" . self::$db->escape_string($this->id) . "' ";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/usuarios/index.php?st=2');
    }
  }
  //Fin de actualizar

  public function eliminar() {
    $query = "DELETE FROM usuarios WHERE id = '" . self::$db->escape_string($this->id) . "'";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/usuarios/index.php?st=3');
    }
  }


  //VALIDACIONES

  public static function getErrores() {
    return self::$errores;
  }

  public function validar() {
    if(!$this->nombre) {
      self::$errores[] = 'El nombre es obligatorio';
    }
    if(!$this->telefono) {
      self::$errores[] = 'El teléfono es obligatorio';
    }
    if(!$this->correo) {
      self::$errores[] = 'El correo es obligatorio';
    }
    if(!$this->rol) {
      self::$errores[] = 'El rol es obligatorio';
    }
    if(!$this->contrasena) {
      self::$errores[] = 'La contraseña es obligatoria';
    }
    return self::$errores;
  }

  //MOSTRAR
  public static function consultaSQL($query) {
    $resultado = self::$db->query($query);
    $array = [];
    while($registro = $resultado->fetch_assoc()) {
      $array[] = self::crearObjeto($registro);
    }
    $resultado->free();
    return $array;
  }

  protected static function crearObjeto($registro) {
    $objeto = new self;
    foreach($registro as $key => $value) {
      if(property_exists($objeto, $key)) {
        $objeto->$key = $value;
      }
    }
    return $objeto;
  }

  public static function mostrarTodos($limite = 10, $offset = 0) {
    // Selecciona todos los campos del usuario.
    $limite = intval($limite);
    $offset = intval($offset);
    $query = "SELECT * FROM usuarios ORDER BY FIELD(rol, 1, 3, 2) ASC LIMIT $limite OFFSET $offset";
    $resultado = self::consultaSQL($query);
    return $resultado;
  }
  public static function contarTodos() {
    $query = "SELECT COUNT(*) AS total FROM usuarios";
    $resultado = self::$db->query($query);
    $fila = $resultado->fetch_assoc();
    return (int) $fila['total'];
  }
}
