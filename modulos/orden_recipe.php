<?php
/** Muestra o descarga el récipe de una orden. El archivo no es accesible por dirección directa: solo a través de esta página, con sesión y permiso. */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('ordenes', '../');

$id = $_GET['id'] ?? '';
if (!ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: ordenes.php'); exit(); }

$s = $conexion->prepare("SELECT recipe_archivo FROM ordenes_entrega WHERE id_orden = ?");
$s->execute([(int) $id]);
$archivo = $s->fetchColumn();

$ruta = $archivo ? RECIPE_DIR . '/' . basename($archivo) : '';
if (!$archivo || !is_file($ruta)) {
    flash('warning', 'Esta orden no tiene un récipe adjunto o el archivo no se encuentra.');
    header('Location: orden_ver.php?id=' . (int) $id);
    exit();
}

$ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mime = RECIPE_EXTENSIONES[$ext] ?? 'application/octet-stream';
$modo = isset($_GET['descargar']) ? 'attachment' : 'inline';

registrar_evento($conexion, 'VER_RECIPE', 'ordenes_entrega', (int) $id, 'Consulta del récipe de la orden ' . numero_orden((int) $id));

header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $modo . '; filename="recipe_' . numero_orden((int) $id) . '.' . $ext . '"');
header('Content-Length: ' . filesize($ruta));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($ruta);
exit();
