<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('beneficiarios', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: beneficiarios.php'); exit(); }
verificar_csrf();

$id       = $_POST['id_beneficiario'] ?? '';
$editando = $id !== '';
if ($editando && !ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: beneficiarios.php'); exit(); }
$id     = $editando ? (int) $id : 0;
$volver = ($_POST['volver'] ?? '') === 'orden' ? 'orden' : '';
$form   = 'beneficiario_form.php' . ($editando ? "?id=$id" : '') . ($volver ? ($editando ? '&' : '?') . 'volver=orden' : '');

[$errores, $d] = validar_beneficiario($conexion, $_POST, $id);

if ($editando) {
    $s = $conexion->prepare("SELECT status FROM beneficiarios WHERE id_beneficiario = ?");
    $s->execute([$id]);
    $actual = $s->fetchColumn();
    if ($actual === false) { flash('danger', 'El beneficiario no existe.'); header('Location: beneficiarios.php'); exit(); }
    // Solo el administrador puede cambiar el estatus
    $status = es_admin() && in_array($_POST['status'] ?? '', ['Activo', 'Inactivo'], true) ? $_POST['status'] : $actual;
}

if ($errores) {
    $_SESSION['errores_form'] = $errores;
    $_SESSION['datos_form']   = $_POST;
    header("Location: $form");
    exit();
}

try {
    if ($editando) {
        $conexion->prepare(
            "UPDATE beneficiarios SET nombres = ?, apellidos = ?, tipo_documento = ?, cedula = ?, telefono = ?, tipo_beneficiario = ?, status = ?,
                    fecha_modificacion = NOW(), usuario_modificacion = ? WHERE id_beneficiario = ?"
        )->execute([$d['nombres'], $d['apellidos'], $d['tipo_documento'], $d['cedula'], $d['telefono'], $d['tipo_beneficiario'], $status, id_usuario_actual(), $id]);
        registrar_evento($conexion, 'EDITAR_BENEFICIARIO', 'beneficiarios', $id,
            "Beneficiario N° " . numero_beneficiario($id) . ": {$d['nombres']} {$d['apellidos']} (" . documento_formateado($d['tipo_documento'], $d['cedula']) . ", {$d['tipo_beneficiario']}, $status)");
        flash('success', 'Beneficiario actualizado correctamente.');
        header('Location: beneficiarios.php');
    } else {
        $conexion->prepare(
            "INSERT INTO beneficiarios (nombres, apellidos, tipo_documento, cedula, telefono, tipo_beneficiario, usuario_registro) VALUES (?, ?, ?, ?, ?, ?, ?)"
        )->execute([$d['nombres'], $d['apellidos'], $d['tipo_documento'], $d['cedula'], $d['telefono'], $d['tipo_beneficiario'], id_usuario_actual()]);
        $nuevo = (int) $conexion->lastInsertId();
        registrar_evento($conexion, 'CREAR_BENEFICIARIO', 'beneficiarios', $nuevo,
            "Beneficiario N° " . numero_beneficiario($nuevo) . ": {$d['nombres']} {$d['apellidos']} (" . documento_formateado($d['tipo_documento'], $d['cedula']) . ", {$d['tipo_beneficiario']})");
        flash('success', 'Beneficiario registrado con el N° ' . numero_beneficiario($nuevo) . '.');
        header($volver === 'orden' ? "Location: orden_form.php?id_beneficiario=$nuevo" : 'Location: beneficiarios.php');
    }
} catch (PDOException $e) {
    error_log('beneficiario_guardar.php: ' . $e->getMessage());
    flash('danger', 'No fue posible guardar el beneficiario. Verifique que la cédula no esté repetida.');
    header('Location: beneficiarios.php');
}
exit();
