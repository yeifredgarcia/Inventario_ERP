<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
requerir_login($conexion, '../');
requerir_permiso('crear', '../');

$categorias     = $conexion->query("SELECT id_categoria, nombre_categoria FROM categorias WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ") ORDER BY nombre_categoria")->fetchAll();
$presentaciones = $conexion->query("SELECT id_presentacion, nombre_presentacion FROM presentaciones WHERE status = 'Activo' ORDER BY nombre_presentacion")->fetchAll();

$cat_retorno = isset($_GET['cat']) && ctype_digit($_GET['cat']) ? (int) $_GET['cat'] : 0;
$producto    = $_SESSION['datos_form'] ?? ['id_categoria' => $cat_retorno];
unset($_SESSION['datos_form']);

$base = '../';
$titulo_pagina    = 'Registrar insumo';
$titulo_form      = 'Registrar nuevo insumo';
$accion_destino   = 'guardar.php';
$texto_boton      = 'Guardar';
$scripts_extra    = ['js/inventario.js'];
require __DIR__ . '/../includes/encabezado.php';
require __DIR__ . '/../includes/form_producto.php';
require __DIR__ . '/../includes/pie.php';
