<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('auditoria', '../');

$usuario = mb_substr(trim($_GET['usuario'] ?? ''), 0, 50);
$accion  = $_GET['accion'] ?? '';
$desde   = trim($_GET['desde'] ?? '');
$hasta   = trim($_GET['hasta'] ?? '');
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pag = 50;
$errores = [];

foreach (['desde' => $desde, 'hasta' => $hasta] as $campo => $valor) {
    if ($valor !== '' && !fecha_valida($valor)) $errores[] = "La fecha \"$campo\" no es válida.";
}
if (!$errores && $desde !== '' && $hasta !== '' && $desde > $hasta) {
    $errores[] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
}

$acciones = $conexion->query("SELECT DISTINCT accion FROM auditoria ORDER BY accion")->fetchAll(PDO::FETCH_COLUMN);
if ($accion !== '' && !in_array($accion, $acciones, true)) $accion = '';

$where = []; $params = [];
if (!$errores) {
    if ($usuario !== '') { $where[] = 'nombre_usuario LIKE ?'; $params[] = "%$usuario%"; }
    if ($accion !== '')  { $where[] = 'accion = ?'; $params[] = $accion; }
    if ($desde !== '')   { $where[] = 'fecha_registro >= ?'; $params[] = "$desde 00:00:00"; }
    if ($hasta !== '')   { $where[] = 'fecha_registro <= ?'; $params[] = "$hasta 23:59:59"; }
}
$cond = $where ? ' WHERE ' . implode(' AND ', $where) : '';

if (isset($_GET['pdf']) && !$errores) {
    $s = $conexion->prepare("SELECT * FROM auditoria$cond ORDER BY id_evento DESC LIMIT 5000");
    $s->execute($params);
    $filas = [];
    foreach ($s->fetchAll() as $ev) {
        $filas[] = [fecha_pdf($ev['fecha_registro'], true), $ev['nombre_usuario'], etiqueta_accion($ev['accion']), $ev['tabla_afectada'] ?? '', $ev['detalle'] ?? '', $ev['ip_origen'] ?? ''];
    }
    registrar_evento($conexion, 'EXPORTAR_PDF', 'auditoria', null, 'PDF de auditoría (' . count($filas) . ' registros)');
    $pdf = new PdfTabla('Auditoría del sistema', [
        'Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de eventos: ' . count($filas),
        'Filtros: ' . ($accion !== '' ? etiqueta_accion($accion) : 'todas las acciones') . ($usuario !== '' ? ", usuario «$usuario»" : '')
            . ($desde !== '' ? ', desde ' . fecha_pdf($desde) : '') . ($hasta !== '' ? ', hasta ' . fecha_pdf($hasta) : ''),
    ]);
    $pdf->tabla(['Fecha y hora', 'Usuario', 'Acción', 'Tabla', 'Detalle', 'IP'], [16, 14, 28, 18, 56, 14], $filas);
    $pdf->descargar('auditoria_' . date('Ymd_His') . '.pdf');
}

$s = $conexion->prepare("SELECT COUNT(*) FROM auditoria$cond");
$s->execute($params);
$total      = (int) $s->fetchColumn();
$total_pags = max(1, (int) ceil($total / $por_pag));
$pagina     = min($pagina, $total_pags);
$offset     = ($pagina - 1) * $por_pag;

$s = $conexion->prepare("SELECT * FROM auditoria$cond ORDER BY id_evento DESC LIMIT $por_pag OFFSET $offset");
$s->execute($params);
$eventos = $s->fetchAll();

$consulta = array_filter(['usuario' => $usuario, 'accion' => $accion, 'desde' => $desde, 'hasta' => $hasta], fn($v) => $v !== '');

$base = '../';
$titulo_pagina = 'Auditoría';
require __DIR__ . '/../includes/encabezado.php';
?>
<h1 class="h3 mb-3">Auditoría del sistema</h1>

<div class="card shadow-sm">
  <div class="card-body">
    <?php if ($errores): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="GET" class="row g-2 align-items-end mb-3" novalidate>
      <div class="col-md-3">
        <label class="form-label small mb-1" for="usuario">Usuario</label>
        <input type="text" class="form-control" id="usuario" name="usuario" maxlength="50" value="<?= e($usuario) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1" for="accion">Acción</label>
        <select class="form-select" id="accion" name="accion">
          <option value="">Todas</option>
          <?php foreach ($acciones as $a): ?><option value="<?= e($a) ?>" <?= $accion === $a ? 'selected' : '' ?>><?= e(etiqueta_accion($a)) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1" for="desde">Desde</label>
        <input type="date" class="form-control" id="desde" name="desde" value="<?= e($desde) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1" for="hasta">Hasta</label>
        <input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($hasta) ?>">
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-institucional" type="submit">Filtrar</button>
        <a class="btn btn-outline-secondary" href="auditoria.php">Limpiar</a>
      </div>
      <div class="col-12 text-end"><a class="btn btn-outline-danger btn-sm" href="?<?= e(http_build_query($consulta + ['pdf' => 1])) ?>">Exportar PDF</a></div>
    </form>

    <div class="text-muted small mb-2"><?= $total ?> evento(s)</div>
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle">
        <thead class="table-light"><tr><th>Fecha y hora</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Detalle</th><th>IP</th></tr></thead>
        <tbody>
        <?php if ($eventos): foreach ($eventos as $ev): ?>
          <tr>
            <td class="text-nowrap"><?= e(date('d/m/Y H:i:s', strtotime($ev['fecha_registro']))) ?></td>
            <td><?= e($ev['nombre_usuario']) ?></td>
            <td><span class="badge text-bg-secondary"><?= e(etiqueta_accion($ev['accion'])) ?></span></td>
            <td><?= e($ev['tabla_afectada']) ?></td>
            <td><?= e($ev['detalle']) ?></td>
            <td class="text-nowrap"><?= e($ev['ip_origen']) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center text-muted py-3">No hay eventos con los filtros indicados.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pags > 1): ?>
    <nav aria-label="Paginación"><ul class="pagination pagination-sm justify-content-center mb-0">
      <?php for ($i = 1; $i <= $total_pags; $i++):
        if ($i !== 1 && $i !== $total_pags && abs($i - $pagina) > 2) { if (abs($i - $pagina) === 3) echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; continue; } ?>
        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($consulta + ['pagina' => $i])) ?>"><?= $i ?></a></li>
      <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
