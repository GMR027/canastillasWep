<?php

namespace Model;

/**
 * Cliente con acceso al portal (tabla `clientes` de cotizaciones).
 *
 * Contrato (cotizaciones/GUIA.md): inicio de sesion con `email` +
 * `portal_contrasena` (bcrypt). NULL = sin acceso. `email` NO es unico.
 */
class Cliente extends ModeloDB
{
    public int $id = 0;
    public string $nombre = '';
    public ?string $nombre_empresa = null;
    public ?string $email = null;
    public ?string $telefono = null;
    private ?string $hash = null;

    private const COLUMNAS = 'id, nombre, nombre_empresa, email, telefono, portal_contrasena';


    public function __construct(array $fila = [])
    {
        $this->id = (int) ($fila['id'] ?? 0);
        $this->nombre = (string) ($fila['nombre'] ?? '');
        $this->nombre_empresa = $fila['nombre_empresa'] ?? null;
        $this->email = $fila['email'] ?? null;
        $this->telefono = $fila['telefono'] ?? null;
        $this->hash = $fila['portal_contrasena'] ?? null;
    }

    public static function buscar(int $id): ?self
    {
        $fila = static::fila(
            'SELECT ' . self::COLUMNAS . ' FROM clientes WHERE id = ? AND portal_contrasena IS NOT NULL',
            'i',
            [$id]
        );
        return $fila ? new self($fila) : null;
    }

    /**
     * Devuelve el cliente si exactamente uno de los registros con ese correo
     * tiene esa contrasena.
     */
    public static function autenticar(string $email, string $contrasena): ?self
    {
        $email = trim($email);
        if ($email === '' || $contrasena === '') {
            return null;
        }

        $filas = static::filas(
            'SELECT ' . self::COLUMNAS . ' FROM clientes WHERE email = ? AND portal_contrasena IS NOT NULL',
            's',
            [$email]
        );

        if (!$filas) {
            // Mismo costo que un intento real: no revela si el correo existe
            password_verify($contrasena, password_hash('sin-cliente', PASSWORD_BCRYPT));
            return null;
        }

        $coincidencias = array_filter($filas, fn(array $f) => password_verify($contrasena, (string) $f['portal_contrasena']));

        return count($coincidencias) === 1 ? new self(array_values($coincidencias)[0]) : null;
    }

    public function verificar(string $contrasena): bool
    {
        return $this->hash !== null && password_verify($contrasena, $this->hash);
    }

    public function cambiarContrasena(string $nueva): void
    {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        static::ejecutar(
            'UPDATE clientes SET portal_contrasena = ?, portal_actualizada_en = NOW() WHERE id = ?',
            'si',
            [$hash, $this->id]
        );
        $this->hash = $hash;
    }

    /** Cambia si cambia la contrasena: invalida las demas sesiones. */
    public function huella(): string
    {
        return hash('sha256', (string) $this->hash);
    }

    public function nombreVisible(): string
    {
        return $this->nombre !== '' ? $this->nombre : (string) $this->nombre_empresa;
    }

    /** @return string[] errores de la nueva contrasena */
    public static function validarNueva(string $nueva, string $confirmacion): array
    {
        $errores = [];
        if (mb_strlen($nueva) < 8) {
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }
        if (mb_strlen($nueva) > 72) {
            $errores[] = 'La nueva contraseña no puede tener más de 72 caracteres.';
        }
        if ($nueva !== $confirmacion) {
            $errores[] = 'La confirmación no coincide.';
        }
        return $errores;
    }
}
