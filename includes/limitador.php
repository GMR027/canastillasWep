<?php

/**
 * Limite de intentos de inicio de sesion (por IP y por correo).
 * Guarda marcas de tiempo en archivos del directorio temporal del sistema;
 * suficiente para una sola replica. Si el sitio escala a varias replicas,
 * mover esto a una tabla o a Redis.
 */
final class Limitador
{
    public const MAX_INTENTOS = 5;
    public const VENTANA = 900; // 15 minutos

    private static function archivo(string $clave): string
    {
        $dir = sys_get_temp_dir() . '/cdb-login';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . '/' . hash('sha256', $clave) . '.json';
    }

    /** @return int[] marcas de tiempo vigentes */
    private static function intentos(string $clave): array
    {
        $ruta = self::archivo($clave);
        $datos = is_file($ruta) ? json_decode((string) @file_get_contents($ruta), true) : [];
        $limite = time() - self::VENTANA;
        return array_values(array_filter(is_array($datos) ? $datos : [], fn($t) => (int) $t > $limite));
    }

    /**
     * IP del cliente. Detras del ingress de Kubernetes REMOTE_ADDR es la del
     * ingress (compartida por todos), asi que con CONFIAR_PROXY=1 se usa
     * X-Real-IP / X-Forwarded-For, que pone ingress-nginx. Sin esa variable
     * no se confia en las cabeceras (cualquiera podria falsificarlas).
     */
    public static function ipCliente(): string
    {
        if (env('CONFIAR_PROXY') === '1') {
            $real = trim((string) ($_SERVER['HTTP_X_REAL_IP'] ?? ''));
            $reenviada = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]);
            foreach ([$real, $reenviada] as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'sin-ip';
    }

    /** Claves que se revisan para una solicitud de login. */
    public static function claves(string $email): array
    {
        $ip = self::ipCliente();
        return ['ip:' . $ip, 'correo:' . mb_strtolower(trim($email))];
    }

    /** Segundos de espera si alguna clave esta bloqueada; 0 si puede intentar. */
    public static function espera(array $claves): int
    {
        $espera = 0;
        foreach ($claves as $clave) {
            $intentos = self::intentos($clave);
            if (count($intentos) >= self::MAX_INTENTOS) {
                $espera = max($espera, min($intentos) + self::VENTANA - time());
            }
        }
        return max(0, $espera);
    }

    public static function fallo(array $claves): void
    {
        foreach ($claves as $clave) {
            $intentos = self::intentos($clave);
            $intentos[] = time();
            @file_put_contents(self::archivo($clave), json_encode($intentos), LOCK_EX);
        }
    }

    public static function limpiar(array $claves): void
    {
        foreach ($claves as $clave) {
            @unlink(self::archivo($clave));
        }
    }
}
