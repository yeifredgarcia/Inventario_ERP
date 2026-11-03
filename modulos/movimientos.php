<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('movimientos', '../');

$tipo    = $_GET['tipo'] ?? '';
$buscar  = mb_substr(trim($_GET['q'] ?? ''), 0, 100);      // insumo
$persona = mb_substr(trim($_GET['persona'] ?? ''), 0, 100); // nombre, cédula o destinatario
$desde   = trim($_GET['desde'] ?? '');
$hasta   = trim($_GET['hasta'] ?? '');
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pag = 25;
$errores = [];

if (!in_array($tipo, ['Entrada', 'Salida'], true)) $tipo = '';
foreach (['desde' => $desde, 'hasta' => $hasta] as $campo => $valor) {
    if ($valor !== '' && !fecha_valida($valor)) $errores[] = "La fecha \"$campo\" no es válida.";
}
if (!$errores && $desde !== '' && $hasta !== '' && $desde > $hasta) {
    $errores[] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
}

$where = ['i.id_categoria IN (' . categorias_sql() . ')']; $params = [];
if (!$errores) {
    if ($tipo !== '')    { $where[] = 'm.tipo = ?'; $params[] = $tipo; }
    if ($buscar !== '')  { $where[] = 'i.nombre_producto LIKE ?'; $params[] = "%$buscar%"; }
    if ($persona !== '') { $where[] = '(m.nombre_persona LIKE ? OR m.cedula_persona LIKE ? OR m.destinatario LIKE ?)'; array_push($params, "%$persona%", "%$persona%", "%$persona%"); }
    if ($desde !== '')   { $where[] = 'm.fecha_movimiento >= ?'; $params[] = $desde; }
    if ($hasta !== '')   { $where[] = 'm.fecha_movimiento <= ?'; $params[] = $hasta; }
}
$cond = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$from = "FROM movimientos_inventario m
         INNER JOIN inventario_general i ON i.id_producto = m.id_producto
         LEFT JOIN usuarios u ON u.id_usuario = m.usuario_registro";
$cols = "m.*, i.nombre_producto, u.nombre_usuario AS registrado_por";

// ---- Exportar a PDF ----
if (isset($_GET['pdf']) && !$errores) {
    $s = $conexion->prepare("SELECT $cols $from$cond ORDER BY m.id_movimiento DESC LIMIT 5000");
    $s->execute($params);
    $filas = [];
    foreach ($s->fetchAll() as $m) {
        $filas[] = [$m['id_movimiento'], fecha_pdf($m['fecha_movimiento']), $m['tipo'], $m['nombre_producto'], $m['cantidad'],
                    $m['existencia_resultante'], $m['nombre_persona'], $m['cedula_persona'], $m['destinatario'] ?? '', $m['registrado_por'] ?? '', $m['id_orden'] ? numero_orden((int) $m['id_orden']) : ($m['observacion'] ?? '')];
    }
    registrar_evento($conexion, 'EXPORTAR_PDF', 'movimientos_inventario', null, 'PDF de entradas y salidas (' . count($filas) . ' registros)');
    $pdf = new PdfTabla('Control de entradas y salidas de insumos', [
        'Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de movimientos: ' . count($filas),
        'Filtros: tipo ' . ($tipo ?: 'todos') . ($buscar !== '' ? ", insumo «$buscar»" : '') . ($persona !== '' ? ", persona «$persona»" : '')
            . ($desde !== '' ? ', desde ' . fecha_pdf($desde) : '') . ($hasta !== '' ? ', hasta ' . fecha_pdf($hasta) : ''),
    ]);
    $pdf->tabla(['ID', 'Fecha', 'Tipo', 'Insumo', 'Cant.', 'Exist.', 'Persona', 'Cédula', 'Para quién', 'Registró', 'Orden / Obs.'],
                [5, 10, 7, 30, 6, 6, 22, 10, 22, 12, 16], $filas);
    $pdf->descargar('movimientos_inventario_' . date('Ymd_His') . '.pdf');
}

$s = $conexion->prepare("SELECT COUNT(*) $from$cond");
$s->execute($params);
$total      = (int) $s->fetchColumn();
$total_pags = max(1, (int) ceil($total / $por_pag));
$pagina     = min($pagina, $total_pags);
$offset     = ($pagina - 1) * $por_pag;

$s = $conexion->prepare("SELECT $cols $from$cond ORDER BY m.id_movimiento DESC LIMIT $por_pag OFFSET $offset");
$s->execute($params);
$movs = $s->fetchAll();

$consulta = array_filter(['tipo' => $tipo, 'q' => $buscar, 'persona' => $persona, 'desde' => $desde, 'hasta' => $hasta], fn($v) => $v !== '');

$base = '../';
$titulo_pagina = 'Entradas y salidas';
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Entradas y salidas de insumos</h1>
  <div class="d-flex gap-2">
    <a href="movimiento_form.php?tipo=Entrada" class="btn btn-success btn-sm">+ Registrar entrada</a>
    <a href="orden_form.php" class="btn btn-danger btn-sm">− Nueva orden de entrega (salida)</a>
  </div>
</div>

<div class="card shadow-sm"><div class="card-body">
  <?php if ($errores): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="GET" class="row g-2 align-items-end mb-3" id="formFiltro" novalidate>
    <div class="col-md-2">
      <label class="form-label small mb-1" for="tipo">Tipo</label>
      <select class="form-select" id="tipo" name="tipo">
        <option value="">Todos</option>
        <option value="Entrada" <?= $tipo === 'Entrada' ? 'selected' : '' ?>>Entradas</option>
        <option value="Salida" <?= $tipo === 'Salida' ? 'selected' : '' ?>>Salidas</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1" for="q">Insumo</label>
      <input type="text" class="form-control" id="q" name="q" maxlength="100" value="<?= e($buscar) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1" for="persona">Persona, cédula o destinatario</label>
      <input type="text" class="form-control" id="persona" name="persona" maxlength="100" value="<?= e($persona) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1" for="desde">Desde</label>
      <input type="date" class="form-control" id="desde" name="desde" value="<?= e($desde) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1" for="hasta">Hasta</label>
      <input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($hasta) ?>">
    </div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-institucional" type="submit">Filtrar</button>
      <a class="btn btn-outline-secondary" href="movimientos.php">Limpiar</a>
      <a class="btn btn-outline-danger ms-auto" href="?<?= e(http_build_query($consulta + ['pdf' => 1])) ?>">Exportar PDF</a>
    </div>
  </form>

  <div class="text-muted small mb-2"><?= $total ?> movimiento(s)</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light"><tr><th>Fecha</th><th>Tipo</th><th>Insumo</th><th class="text-end">Cant.</th><th class="text-end">Existencia</th><th>Orden</th><th>Persona</th><th>Para quién</th><th>Registró</th><th>Observación</th></tr></thead>
      <tbody>
      <?php if ($movs): foreach ($movs as $m): ?>
        <tr>
          <td class="text-nowrap"><?= e(fecha_pdf($m['fecha_movimiento'])) ?></td>
          <td><span class="badge bg-<?= $m['tipo'] === 'Entrada' ? 'success' : 'danger' ?>"><?= e($m['tipo']) ?></span></td>
          <td><?= e($m['nombre_producto']) ?></td>
          <td class="text-end"><?= (int) $m['cantidad'] ?></td>
          <td class="text-end"><?= (int) $m['existencia_resultante'] ?></td>
          <td class="text-nowrap"><?php if ($m['id_orden']): ?><a href="orden_ver.php?id=<?= (int) $m['id_orden'] ?>"><?= e(numero_orden((int) $m['id_orden'])) ?></a><?php else: ?>—<?php endif; ?></td>
          <td><?= e($m['nombre_persona']) ?><div class="small text-muted"><?= e($m['cedula_persona']) ?></div></td>
          <td><?= e($m['destinatario'] ?? '—') ?></td>
          <td><?= e($m['registrado_por'] ?? '—') ?></td>
          <td><?= e($m['observacion'] ?? '') ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="10" class="text-center text-muted py-4">No hay movimientos con los filtros indicados.</td></tr>
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
</div></div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
