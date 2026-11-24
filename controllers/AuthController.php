<?php

namespace Controllers;

use Limitador;
use Model\Cliente;
use MVC\Router;

class AuthController
{
    public static function login(Router $router): void
    {
        if (cliente()) {
            redirigir('/mi-cuenta/compras');
        }

        $email = '';
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $email = trim((string) ($_POST['email'] ?? ''));
            $contrasena = (string) ($_POST['contrasena'] ?? '');
            $claves = Limitador::claves($email);
            $espera = Limitador::espera($claves);

            if ($espera > 0) {
                http_response_code(429);
                $error = 'Demasiados intentos. Espera ' . (int) ceil($espera / 60) . ' min e inténtalo de nuevo.';
            } else {
                try {
                    $cliente = Cliente::autenticar($email, $contrasena);
                } catch (\mysqli_sql_exception $e) {
                    error_log('[login] ' . $e->getMessage());
                    $cliente = null;
                    $error = 'No pudimos verificar tu cuenta en este momento. Inténtalo más tarde.';
                }

                if ($cliente) {
                    Limitador::limpiar($claves);
                    entrarComoCliente($cliente);
                    flash('exito', 'Bienvenido, ' . $cliente->nombreVisible() . '.');
                    redirigir('/mi-cuenta/compras');
                }

                if ($error === null) {
                    Limitador::fallo($claves);
                    $error = 'Correo o contraseña incorrectos.';
                }
            }
        }

        $router->render('auth/login', [
            'titulo' => 'Iniciar sesión',
            'descripcion' => 'Consulta tus compras, entregas y actas de Canastillas de la Baja.',
            'pagina' => 'login',
            'robots' => 'noindex,nofollow',
            'email' => $email,
            'error' => $error,
        ]);
    }

    public static function logout(Router $router): void
    {
        verificarCsrf();
        salir();
        iniciarSesion();
        flash('exito', 'Cerraste sesión.');
        redirigir('/login');
    }
}
