<?php
session_start();
require_once 'config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['usuario']);
    $clave          = trim($_POST['clave']);

    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";
    $stmt = $conexion_usuarios->prepare($sql);
    $stmt->execute([$nombre_usuario]);
    $usuario = $stmt->fetch();

    if ($usuario && (password_verify($clave, $usuario['clave']) || $clave === $usuario['clave'])) {
        $_SESSION['id_usuario']      = $usuario['id_usuario'];
        $_SESSION['usuario']         = $usuario['nombre_usuario'];
        $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
        $_SESSION['rol']             = $usuario['rol'];

        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>