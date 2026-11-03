<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('editar', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit();
}
verificar_csrf();

$id          = $_POST['id_producto'] ?? '';
$cat_retorno = ctype_digit($_POST['cat_retorno'] ?? '') ? (int) $_POST['cat_retorno'] : 0;
$volver      = 'listar.php' . ($cat_retorno ? "?cat=$cat_retorno" : '');

if (!ctype_digit((string) $id)) {
    flash('danger', 'El identificador del insumo no es válido.');
    header("Location: $volver");
    exit();
}
$id = (int) $id;

$stmt = $conexion->prepare("SELECT * FROM inventario_general WHERE id_producto = ? AND status = 'Activo'");
$stmt->execute([$id]);
$anterior = $stmt->fetch();
if (!$anterior) {
    flash('danger', 'El insumo no existe o está inactivo.');
    header("Location: $volver");
    exit();
}

[$errores, $d] = validar_producto($conexion, $_POST, $id);
if ($errores) {
    $_SESSION['errores_form'] = $errores;
    $_SESSION['datos_form']   = $_POST;
    header("Location: editar.php?id=$id" . ($cat_retorno ? "&cat=$cat_retorno" : ''));
    exit();
}

try {
    $conexion->prepare(
        "UPDATE inventario_general
         SET nombre_producto = ?, id_categoria = ?, id_presentacion = ?, cantidad = ?, fecha_vencimiento = ?,
             fecha_modificacion = NOW(), usuario_modificacion = ?
         WHERE id_producto = ?"
    )->execute([$d['nombre_producto'], $d['id_categoria'], $d['id_presentacion'], $d['cantidad'], $d['fecha_vencimiento'], id_usuario_actual(), $id]);

    // Detalle de los campos que cambiaron
    $cambios = [];
    foreach (['nombre_producto' => 'Nombre', 'id_categoria' => 'Categoría (id)', 'id_presentacion' => 'Presentación (id)',
              'cantidad' => 'Cantidad', 'fecha_vencimiento' => 'Vencimiento'] as $campo => $etiqueta) {
        if ((string) $anterior[$campo] !== (string) $d[$campo]) {
            $cambios[] = "$etiqueta: " . ($anterior[$campo] ?? 'vacío') . ' → ' . ($d[$campo] ?? 'vacío');
        }
    }
    registrar_evento($conexion, 'EDITAR_PRODUCTO', 'inventario_general', $id, $cambios ? implode('; ', $cambios) : 'Sin cambios en los datos');
    flash('success', 'Insumo actualizado correctamente.');
} catch (PDOException $e) {
    error_log('actualizar.php: ' . $e->getMessage());
    flash('danger', 'No fue posible actualizar el insumo. Verifique que no esté duplicado.');
}

header("Location: $volver");
exit();
