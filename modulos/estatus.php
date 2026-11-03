<?php
/** Baja lógica: cambia el estatus Activo/Inactivo (no se borran registros). */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('estatus', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit();
}
verificar_csrf();

$id      = $_POST['id_producto'] ?? '';
$accion  = $_POST['accion'] ?? '';
$retorno = preg_replace('/[^A-Za-z0-9=&_%.\-+]/', '', $_POST['retorno'] ?? '');
$volver  = 'listar.php' . ($retorno !== '' ? "?$retorno" : '');

if (!ctype_digit((string) $id) || !in_array($accion, ['inactivar', 'activar'], true)) {
    flash('danger', 'Solicitud no válida.');
    header("Location: $volver");
    exit();
}

$nuevo = $accion === 'inactivar' ? 'Inactivo' : 'Activo';
$stmt  = $conexion->prepare("SELECT nombre_producto, status, id_categoria FROM inventario_general WHERE id_producto = ?");
$stmt->execute([(int) $id]);
$p = $stmt->fetch();

if (!$p || !categoria_visible((int) $p['id_categoria'])) {
    flash('danger', 'El insumo no existe.');
} elseif ($p['status'] === $nuevo) {
    flash('warning', "El insumo ya estaba $nuevo.");
} else {
    $conexion->prepare("UPDATE inventario_general SET status = ?, fecha_modificacion = NOW(), usuario_modificacion = ? WHERE id_producto = ?")
             ->execute([$nuevo, id_usuario_actual(), (int) $id]);
    registrar_evento($conexion, $accion === 'inactivar' ? 'BAJA_PRODUCTO' : 'REACTIVAR_PRODUCTO', 'inventario_general', (int) $id,
        "«{$p['nombre_producto']}»: {$p['status']} → $nuevo");
    flash('success', $accion === 'inactivar' ? 'Insumo dado de baja correctamente.' : 'Insumo reactivado correctamente.');
}

header("Location: $volver");
exit();
