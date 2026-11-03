<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('crear', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit();
}
verificar_csrf();

$cat_retorno = ctype_digit($_POST['cat_retorno'] ?? '') ? (int) $_POST['cat_retorno'] : 0;
[$errores, $d] = validar_producto($conexion, $_POST);

if ($errores) {
    $_SESSION['errores_form'] = $errores;
    $_SESSION['datos_form']   = $_POST;
    header('Location: agregar.php' . ($cat_retorno ? "?cat=$cat_retorno" : ''));
    exit();
}

try {
    $uid  = id_usuario_actual();
    $stmt = $conexion->prepare(
        "INSERT INTO inventario_general
            (nombre_producto, id_categoria, id_presentacion, cantidad, fecha_vencimiento, usuario_registro)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$d['nombre_producto'], $d['id_categoria'], $d['id_presentacion'], $d['cantidad'], $d['fecha_vencimiento'], $uid]);
    $nuevo_id = (int) $conexion->lastInsertId();

    registrar_evento($conexion, 'CREAR_PRODUCTO', 'inventario_general', $nuevo_id,
        "Se registró «{$d['nombre_producto']}» (cantidad: {$d['cantidad']})");
    flash('success', 'Insumo registrado correctamente.');
} catch (PDOException $e) {
    error_log('guardar.php: ' . $e->getMessage());
    flash('danger', 'No fue posible guardar el insumo. Verifique que no esté duplicado.');
}

header('Location: listar.php' . ($cat_retorno ? "?cat=$cat_retorno" : ''));
exit();
