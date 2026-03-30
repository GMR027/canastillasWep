<?php

namespace App;

class Productos {
  protected static $db;
  public static function setDB($database) {
    self::$db = $database;
  }
  protected static $columnasDB = ['id', 'nombre', 'descripcion'];
  protected static $errores = [];


  public $id;
  public $nombre;
  public $descripcion;


  public function __construct($args = []) {
    $this->id = $args['id'] ?? null;
    $this->nombre = $args['nombre'] ?? '';
    $this->descripcion = $args['descripcion'] ?? '';
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
    $atributos = $this->sanitizarAtributos();
    $columnas = join(', ', array_keys($atributos));
    $valores = join("', '", array_values($atributos));

    $query = "INSERT INTO productos" . " ($columnas) VALUES ('$valores')";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/productos/index.php?st=1');
    }
  }

  //Apartado de actualizar
  public static function buscarID($id) {
    // Convertimos $id a entero para evitar SQL injection,
    // ya que este valor viene directamente de $_GET['id'].
    $id = intval($id);
    $query = "SELECT * FROM productos WHERE productos.id = $id";

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
    $query = "UPDATE productos SET $valores WHERE id = '" . self::$db->escape_string($this->id) . "' ";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/productos/index.php?st=2');
    }
  }
  //Fin de actualizar

  public function eliminar() {
    $query = "DELETE FROM productos WHERE id = '" . self::$db->escape_string($this->id) . "'";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/productos/index.php?st=3');
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
    if(!$this->descripcion) {
      self::$errores[] = 'La descripción es obligatoria';
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

  public static function mostrarTodos() {
    // Selecciona todos los campos del producto.
    $query = "SELECT * FROM productos";
    $resultado = self::consultaSQL($query);
    return $resultado;
  }

}
