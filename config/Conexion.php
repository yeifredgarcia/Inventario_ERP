<?php
$host = "localhost";
$user = "root";
$pass = "";

try {
    // Conexión 1: Base de datos del Inventario
    $conexion = new PDO("mysql:host=$host;dbname=inventario_medico;charset=utf8mb4", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Conexión 2: Base de datos de Usuarios/Login
    $conexion_usuarios = new PDO("mysql:host=$host;dbname=usuarios_db;charset=utf8mb4", $user, $pass);
    $conexion_usuarios->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>