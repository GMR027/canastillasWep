<?php

use Model\Cliente;

/*
 * Sesion del portal de clientes.
 *
 * - Cookie httponly, SameSite=Lax y secure cuando la conexion es HTTPS.
 * - "Huella": sha256 del hash de la contrasena. Si el cliente (o el
 *   administrador desde cotizaciones) cambia la contrasena, las demas
 *   sesiones abiertas dejan de ser validas.
 * - CSRF: un token por sesion en todos los formularios POST.
 */

function conexionSegura(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function iniciarSesion(): void
{
    if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('cdb_sesion');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => conexionSegura(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_csrf" value="' . s(csrf_token()) . '">';
}

/** Corta la peticion con 400 si el token no coincide. */
function verificarCsrf(): void
{
    $enviado = (string) ($_POST['_csrf'] ?? '');
    if ($enviado === '' || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(400);
        exit('Solicitud inválida. Recarga la página e inténtalo de nuevo.');
    }
}

/* ---------- Cliente con sesion ---------- */

function entrarComoCliente(Cliente $cliente): void
{
    session_regenerate_id(true);
    $_SESSION['cliente'] = [
        'id' => $cliente->id,
        'nombre' => $cliente->nombreVisible(),
        'huella' => $cliente->huella(),
    ];
    unset($_SESSION['csrf']);
}

function salir(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Datos del cliente en sesion (sin consultar la BD) o null. */
function cliente(): ?array
{
    return $_SESSION['cliente'] ?? null;
}

/**
 * Exige un cliente con sesion vigente. Comprueba la huella contra la BD
 * (una consulta por peticion) y redirige a /login si no es valida.
 */
function soloCliente(): Cliente
{
    $sesion = cliente();
    $actual = $sesion ? Cliente::buscar((int) $sesion['id']) : null;

    if (!$actual || !hash_equals((string) $sesion['huella'], $actual->huella())) {
        if ($sesion) {
            salir();
            iniciarSesion();
            flash('aviso', 'Tu sesión terminó. Vuelve a iniciar sesión.');
        }
        $destino = parse_url($_SERVER['REQUEST_URI'] ?? '/mi-cuenta', PHP_URL_PATH) ?: '/mi-cuenta';
        redirigir('/login?r=' . rawurlencode($destino));
    }

    header('Cache-Control: private, no-store');
    return $actual;
}

/* ---------- Mensajes de una sola vez ---------- */

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** @return array<int, array{tipo: string, mensaje: string}> */
function flashes(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}
