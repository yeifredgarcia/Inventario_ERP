<?php
/** Descarga la orden de entrega en PDF (con recuadros de firma de ambas partes). */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf_orden.php';
requerir_login($conexion, '../');
requerir_permiso('ordenes', '../');

$id = $_GET['id'] ?? '';
if (!ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: ordenes.php'); exit(); }

$s = $conexion->prepare(
    "SELECT o.fecha_salida, o.observacion, o.recipe_numero, o.recipe_original, o.recipe_archivo, b.nombres, b.apellidos, b.tipo_documento, b.cedula, b.telefono, b.tipo_beneficiario, u.nombre_completo AS entregado_por
     FROM ordenes_entrega o
     INNER JOIN beneficiarios b ON b.id_beneficiario = o.id_beneficiario
     LEFT JOIN usuarios u ON u.id_usuario = o.usuario_registro
     WHERE o.id_orden = ?"
);
$s->execute([(int) $id]);
$o = $s->fetch();
if (!$o) { flash('danger', 'La orden no existe.'); header('Location: ordenes.php'); exit(); }

$s = $conexion->prepare(
    "SELECT i.nombre_producto, p.nombre_presentacion, m.cantidad
     FROM movimientos_inventario m
     INNER JOIN inventario_general i ON i.id_producto = m.id_producto
     INNER JOIN presentaciones p ON p.id_presentacion = i.id_presentacion
     WHERE m.id_orden = ? ORDER BY m.id_movimiento"
);
$s->execute([(int) $id]);
$lineas = array_map(fn($l) => [$l['nombre_producto'], $l['nombre_presentacion'], $l['cantidad']], $s->fetchAll());

$num = numero_orden((int) $id);
registrar_evento($conexion, 'EXPORTAR_PDF', 'ordenes_entrega', (int) $id, "PDF de la orden de entrega $num");

$pdf = new PdfOrden([
    'numero'        => $num,
    'fecha'         => date('d/m/Y', strtotime($o['fecha_salida'])),
    'nombre'        => $o['nombres'],
    'apellido'      => $o['apellidos'],
    'doc_etiqueta'  => etiqueta_documento($o['tipo_documento']),
    'cedula'        => documento_formateado($o['tipo_documento'], $o['cedula']),
    'telefono'      => (string) ($o['telefono'] ?? ''),
    'tipo'          => $o['tipo_beneficiario'],
    'observacion'   => (string) ($o['observacion'] ?? ''),
    'recipe'        => ($o['recipe_numero'] ? 'N° ' . $o['recipe_numero'] . ' - ' : '') . ($o['recipe_archivo'] ? 'archivo adjunto' : 'sin archivo'),
    'recipe_numero' => (string) ($o['recipe_numero'] ?? ''),
    'recipe_original' => (string) ($o['recipe_original'] ?? ''),
    'recipe_ruta'   => ($o['recipe_archivo'] && is_file(RECIPE_DIR . '/' . basename($o['recipe_archivo']))) ? RECIPE_DIR . '/' . basename($o['recipe_archivo']) : '',
    'entregado_por' => (string) ($o['entregado_por'] ?? ''),
], $lineas, date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo']);
$pdf->descargar("orden_entrega_$num.pdf");
