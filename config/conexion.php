<?php
/**
 * Único archivo de conexión a la base de datos.
 * Todas las páginas lo incluyen con require_once; no se repite en ningún otro lugar.
 */
$host   = 'localhost';
$bd     = 'inventario_medico';
$usuario_bd = 'root';
$clave_bd   = '';

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$bd;charset=utf8mb4",
        $usuario_bd,
        $clave_bd,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    // Hora de Venezuela para CURDATE() y NOW()
    $conexion->exec("SET time_zone = '-04:00'");
} catch (PDOException $e) {
    error_log('Error de conexión: ' . $e->getMessage());
    http_response_code(500);
    die('No fue posible conectar con la base de datos. Comuníquese con el administrador del sistema.');
}
