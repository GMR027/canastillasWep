<?php

namespace App;

class Reportes {
  protected static $db;
  public static function setDB($database) {
    self::$db = $database;
  }
  protected static $columnasDB = ['id', 'fecha', 'cliente', 'producto', 'cantidad', 'ubicacion', 'maps', 'lugar', 'imagen'];
  protected static $errores = [];


  public $id;
  public $fecha;
  public $cliente;
  public $producto;
  public $cantidad;
  public $ubicacion;
  public $maps;
  public $lugar;
  public $imagen;
  // Nombre del cliente traido con JOIN desde la tabla usuarios.
  public $nombre_cliente;
  // Nombre del producto traido con JOIN desde la tabla productos.
  public $nombre_producto;
  public $telefono;
  public $ubicacion_nombre;

  public function __construct($args = []) {
    $this->id = $args['id'] ?? null;
    $this->fecha = $args['fecha'] ?? '';
    $this->cliente = $args['cliente'] ?? '';
    $this->producto = $args['producto'] ?? '';
    $this->cantidad = $args['cantidad'] ?? '';
    $this->ubicacion = $args['ubicacion'] ?? '';
    $this->maps = $args['maps'] ?? '';
    $this->lugar = $args['lugar'] ?? '';
    $this->imagen = $args['imagen'] ?? '';
    // Guarda el alias usuarios.nombre AS nombre_cliente.
    $this->nombre_cliente = $args['nombre_cliente'] ?? '';
    // Guarda el alias productos.nombre AS nombre_producto.
    $this->nombre_producto = $args['nombre_producto'] ?? '';
    $this->telefono = $args['telefono'] ?? '';
    $this->ubicacion_nombre = $args['ubicacion_nombre'] ?? '';
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

    $query = "INSERT INTO reportes" . " ($columnas) VALUES ('$valores')";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/index.php?st=1');
    }
  }

  //Apartado de actualizar
  public static function buscarID($id) {
    // Convertimos $id a entero para evitar SQL injection,
    // ya que este valor viene directamente de $_GET['id'].
    $id = intval($id);

    // Antes solo habia "SELECT * FROM reportes" lo cual SOLO traía
    // los campos propios de la tabla reportes.
    // El problema: nombre_cliente y telefono viven en la tabla "usuarios",
    // no en "reportes", por eso siempre salían vacíos.
    //
    // Solución: hacemos JOIN con "usuarios" y "productos" igual que en
    // mostrarTodos(), para traer esos campos junto con el reporte.
    //
    // LEFT JOIN significa: aunque no exista un usuario o producto relacionado,
    // igual nos devuelve el reporte (en lugar de no devolver nada).
    //
    // "usuarios.nombre AS nombre_cliente" le da un alias al campo para que
    // coincida con la propiedad $nombre_cliente de esta clase.
    // "usuarios.telefono AS telefono" hace lo mismo con $telefono.
    // 'ubicacion' en reportes guarda el ID de la tabla 'ubicacion'.
    // Hacemos JOIN para traer el nombre legible como 'ubicacion_nombre'.
    $query = "SELECT reportes.*, 
              usuarios.nombre AS nombre_cliente,
              usuarios.telefono AS telefono,
              productos.nombre AS nombre_producto,
              ubicacion.nombre AS ubicacion_nombre
              FROM reportes 
              LEFT JOIN usuarios ON reportes.cliente = usuarios.id 
              LEFT JOIN productos ON reportes.producto = productos.id
              LEFT JOIN ubicacion ON reportes.ubicacion = ubicacion.id
              WHERE reportes.id = $id";

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
    $valores = [];
    foreach($atributos as $key => $value) {
      $valores[] = "$key = '$value'";
    }
    $valores = join(', ', $valores);
    $query = "UPDATE reportes SET $valores WHERE id = '" . self::$db->escape_string($this->id) . "' ";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/index.php?st=2');
    }
  }
  //Fin de actualizar

  public function eliminar() {
    $this->eliminarImagen();
    $query = "DELETE FROM reportes WHERE id = '" . self::$db->escape_string($this->id) . "'";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/index.php?st=3');
    }
  }

  public function eliminarImagen() {
    $existeArchivo = file_exists(CARPETA_IMAGEN . $this->imagen);
    if($existeArchivo) {
      unlink(CARPETA_IMAGEN . $this->imagen);
    }
  }

  public function sincImage($archivo) {
    if($this->id) {
      $this->eliminarImagen();
    }
    if($archivo) {
      $this->imagen = $archivo;
    }
  }

  //VALIDACIONES

  public static function getErrores() {
    return self::$errores;
  }

  public function validar() {
    if(!$this->fecha) {
      self::$errores[] = 'La fecha es obligatoria';
    }
    if(!$this->cliente) {
      self::$errores[] = 'El cliente es obligatorio';
    }
    if(!$this->producto) {
      self::$errores[] = 'El producto es obligatorio';
    }
    if(!$this->cantidad) {
      self::$errores[] = 'La cantidad es obligatoria';
    }
    if(!$this->ubicacion) {
      self::$errores[] = 'La ubicacion es obligatoria';
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
    // Selecciona todos los campos del reporte y ademas nombres legibles de cliente y producto.
    // usuarios.nombre se expone como nombre_cliente.
    // productos.nombre se expone como nombre_producto.
    // Se usan LEFT JOIN para no perder reportes si falta relacion en usuarios o productos.
    // 'ubicacion' en reportes es FK a la tabla 'ubicacion'.
    // Se incluye JOIN para traer el nombre de la ubicacion.
    $query = "SELECT reportes.*, 
              usuarios.nombre AS nombre_cliente,
              usuarios.telefono AS telefono,
              productos.nombre AS nombre_producto,
              ubicacion.nombre AS ubicacion_nombre
              FROM reportes 
              LEFT JOIN usuarios ON reportes.cliente = usuarios.id 
              LEFT JOIN productos ON reportes.producto = productos.id
              LEFT JOIN ubicacion ON reportes.ubicacion = ubicacion.id";
    $resultado = self::consultaSQL($query);
    return $resultado;
  }

  public function fechaFormateada() {
    // Si no hay fecha, regresa vacio para evitar errores visuales.
    if(!$this->fecha) return '';
    // Diccionario de meses en espanol para mostrar texto en lugar de numero.
    $meses = [
      1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
      5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
      9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    // Convierte la fecha (YYYY-MM-DD) a timestamp para poder separarla.
    $ts = strtotime($this->fecha);
    // Si strtotime no puede leer el formato, regresa la fecha tal cual
    // sin intentar parsearla (evita TypeError en PHP 8+ con date(false)).
    if($ts === false) return $this->fecha;
    // Extrae el dia con dos digitos (ejemplo: 08).
    $dia = date('d', $ts);
    // Obtiene el numero de mes y busca su nombre en el arreglo $meses.
    $mes = $meses[(int) date('n', $ts)];
    // Extrae el anio con cuatro digitos.
    $anio = date('Y', $ts);
    // Arma el formato final solicitado: 00/enero/2026.
    return "$dia/$mes/$anio";
  }
}
