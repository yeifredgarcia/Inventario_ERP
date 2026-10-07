<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM inventario_general WHERE id_producto = ?");
    $stmt->execute([$id]);
}

header("Location: listar.php");
exit();
?>