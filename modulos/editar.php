<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
requerir_login($conexion, '../');
requerir_permiso('editar', '../');

$id = $_GET['id'] ?? '';
if (!ctype_digit((string) $id)) {
    flash('danger', 'El identificador del insumo no es válido.');
    header('Location: listar.php');
    exit();
}

$stmt = $conexion->prepare("SELECT * FROM inventario_general WHERE id_producto = ?");
$stmt->execute([(int) $id]);
$producto = $stmt->fetch();

if (!$producto || !categoria_visible((int) $producto['id_categoria'])) {
    flash('danger', 'El insumo solicitado no existe.');
    header('Location: listar.php');
    exit();
}
if ($producto['status'] !== 'Activo') {
    flash('warning', 'El insumo está inactivo. Reactívelo antes de modificarlo.');
    header('Location: listar.php');
    exit();
}

if (isset($_SESSION['datos_form'])) {          // datos escritos tras un error de validación
    $producto = array_merge($producto, $_SESSION['datos_form']);
    unset($_SESSION['datos_form']);
}

$categorias     = $conexion->query("SELECT id_categoria, nombre_categoria FROM categorias WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ") ORDER BY nombre_categoria")->fetchAll();
$presentaciones = $conexion->query("SELECT id_presentacion, nombre_presentacion FROM presentaciones WHERE status = 'Activo' ORDER BY nombre_presentacion")->fetchAll();
$cat_retorno    = isset($_GET['cat']) && ctype_digit($_GET['cat']) ? (int) $_GET['cat'] : 0;

$base = '../';
$titulo_pagina    = 'Editar insumo';
$titulo_form      = 'Editar insumo';
$accion_destino   = 'actualizar.php';
$texto_boton      = 'Actualizar';
$scripts_extra    = ['js/inventario.js'];
require __DIR__ . '/../includes/encabezado.php';
require __DIR__ . '/../includes/form_producto.php';
require __DIR__ . '/../includes/pie.php';
