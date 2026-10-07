<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/conexion.php';

if ($_POST) {
    $id     = $_POST['id_producto'];
    $nombre = $_POST['nombre_producto'];
    $cat    = $_POST['id_categoria'];
    $pres   = $_POST['id_presentacion'];
    $cant   = $_POST['cantidad'];
    $estado = $_POST['estado'];

    $sql = "UPDATE inventario_general SET nombre_producto=?, id_categoria=?, id_presentacion=?, cantidad=?, estado=? WHERE id_producto=?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre, $cat, $pres, $cant, $estado, $id]);
}

header("Location: listar.php");
exit();
?>