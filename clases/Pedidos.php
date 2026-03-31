<?php

namespace App;

class Pedidos {
  protected static $db;
  public static function setDB($database) {
    self::$db = $database;
  }
  protected static $columnasDB = ['id', 'fecha', 'numeroPedido', 'producto', 'cantidad', 'estatus', 'imagen', 'comentarios', 'embarque', 'fechaEmbarque', 'fechaRecibo', 'costoSinIva', 'costoConIva'];
  protected static $errores = [];


  public $id;
  public $fecha;
  public $numeroPedido;
  public $producto;
  public $cantidad;
  public $estatus;
  public $imagen;
  public $comentarios;
  public $embarque;
  public $fechaEmbarque;
  public $fechaRecibo;
  public $costoSinIva;
  public $costoConIva;

  public function __construct($args = []) {
    $this->id = $args['id'] ?? null;
    $this->fecha = $args['fecha'] ?? '';
    $this->numeroPedido = $args['numeroPedido'] ?? '';
    $this->producto = $args['producto'] ?? '';
    $this->cantidad = $args['cantidad'] ?? '';
    $this->estatus = $args['estatus'] ?? '';
    $this->imagen = $args['imagen'] ?? '';
    $this->comentarios = $args['comentarios'] ?? '';
    $this->embarque = $args['embarque'] ?? '';
    $this->fechaEmbarque = $args['fechaEmbarque'] ?? '';
    $this->fechaRecibo = $args['fechaRecibo'] ?? '';
    $this->costoSinIva = $args['costoSinIva'] ?? '';
    $this->costoConIva = $args['costoConIva'] ?? '';
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

    $query = "INSERT INTO pedidos" . " ($columnas) VALUES ('$valores')";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/pedidos/index.php?st=1');
    }
  }

  //Apartado de actualizar
  public static function buscarID($id) {
    // Convertimos $id a entero para evitar SQL injection,
    // ya que este valor viene directamente de $_GET['id'].
    $id = intval($id);
    $query = "SELECT * FROM pedidos WHERE pedidos.id = $id";

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
    $query = "UPDATE pedidos SET $valores WHERE id = '" . self::$db->escape_string($this->id) . "' ";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/pedidos/index.php?st=2');
    }
  }
  //Fin de actualizar

  public function eliminar() {
    $this->eliminarImagen();
    $query = "DELETE FROM pedidos WHERE id = '" . self::$db->escape_string($this->id) . "'";
    $resultado = self::$db->query($query);
    if($resultado) {
      header('Location: /admin/pedidos/index.php?st=3');
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
    if(!$this->numeroPedido) {
      self::$errores[] = 'El número de pedido es obligatorio';
    }
    if(!$this->producto) {
      self::$errores[] = 'El producto es obligatorio';
    }
    if(!$this->cantidad) {
      self::$errores[] = 'La cantidad es obligatoria';
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

  public static function mostrarTodos($anio = null, $estatus = null, $diasTransito = null) {
    // Selecciona todos los campos del pedido.
    $query = "SELECT * FROM pedidos WHERE 1=1"; // Ordenamos por fecha descendente para mostrar los más recientes primero. es un truco para facilitar la concatenación de condiciones con AND sin preocuparnos por si es la primera condición o no.

    if($anio) {
      $anio = intval($anio);
      $query .= " AND YEAR(pedidos.fecha) = $anio"; //Filtra por año si se especifica.
    }

    if($estatus) {
      $estatus = intval($estatus);
      $query .= " AND pedidos.estatus = $estatus"; //Filtra por estatus si se especifica.
    }


    if($diasTransito) {
      $diasTransito = intval($diasTransito);
      // El valor 1, 2 o 3 representa un rango de días, no un número exacto.
      // DATEDIFF(fechaRecibo, fechaEmbarque) calcula los días entre las dos fechas.
      if($diasTransito === 1) {
        $query .= " AND DATEDIFF(fechaRecibo, fechaEmbarque) BETWEEN 0 AND 7"; //Datediff devuelve la cantidad de días entre fechaRecibo y fechaEmbarque. Si el resultado está entre 0 y 7, se considera tránsito rápido.
      } elseif($diasTransito === 2) {
        $query .= " AND DATEDIFF(fechaRecibo, fechaEmbarque) BETWEEN 8 AND 15";
      } elseif($diasTransito === 3) {
        $query .= " AND DATEDIFF(fechaRecibo, fechaEmbarque) > 15";
      }
    }

    $query .= " ORDER BY pedidos.fecha DESC"; //Ordenamos por fecha descendente para mostrar los más recientes primero.

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

  public function diasEnTransito() {
    if(!$this->fechaEmbarque || !$this->fechaRecibo) return ''; // Si no hay fechas para calcular, regresa vacio para evitar errores visuales.
    $embarque = new \DateTime($this->fechaEmbarque); // Convierte la fecha de embarque a objeto DateTime para poder calcular la diferencia.
    $recibo = new \DateTime($this->fechaRecibo); // Convierte la fecha de recibo a objeto DateTime para poder calcular la diferencia.
    $diferencia = $embarque->diff($recibo); // Calcula la diferencia entre las dos fechas, obteniendo un objeto DateInterval con la diferencia en años, meses y días.
    return $diferencia->days; // Regresa solo la cantidad total de días en tránsito, sin importar meses o años.
  }

  public function claseTransito() {
    $dias = $this->diasEnTransito();
    if($dias === '') return '';
    if($dias <= 7) return 'transito-rapido';
    if($dias <= 12) return 'transito-normal';
    return 'transito-lento';
  }
}
