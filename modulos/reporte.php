<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('reportes', '../');

$cat_id  = isset($_GET['cat']) && ctype_digit($_GET['cat']) ? (int) $_GET['cat'] : 0;
if (!categoria_visible($cat_id)) {
    $cat_id = count(CATEGORIAS_VISIBLES) === 1 ? CATEGORIAS_VISIBLES[0] : 0;
}
$estado  = $_GET['estado'] ?? 'todos';
$estatus = $_GET['estatus'] ?? 'Activo';
$buscar  = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
$desde   = trim($_GET['desde'] ?? '');
$hasta   = trim($_GET['hasta'] ?? '');
$generar = isset($_GET['generar']);
$errores = [];

if (!in_array($estado, ['todos', 'Disponible', 'Por Vencer', 'Vencido'], true)) $estado = 'todos';
if (!es_admin() || !in_array($estatus, ['Activo', 'Inactivo', 'todos'], true)) $estatus = 'Activo';

// Validación de fechas del filtro (formato y rango lógico)
foreach (['desde' => $desde, 'hasta' => $hasta] as $campo => $valor) {
    if ($valor !== '' && !fecha_valida($valor)) $errores[] = "La fecha \"$campo\" no es válida.";
}
if (!$errores && $desde !== '' && $hasta !== '' && $desde > $hasta) {
    $errores[] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
}

$categorias = $conexion->query("SELECT id_categoria, nombre_categoria FROM categorias WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ") ORDER BY nombre_categoria")->fetchAll();
$filas = [];

if ($generar && !$errores) {
    $where = ['id_categoria IN (' . categorias_sql() . ')']; $params = [];
    if ($cat_id)          { $where[] = 'id_categoria = ?'; $params[] = $cat_id; }
    if ($estado !== 'todos') { $where[] = 'estado_vencimiento = ?'; $params[] = $estado; }
    if ($estatus !== 'todos') { $where[] = 'status = ?'; $params[] = $estatus; }
    if ($buscar !== '')   { $where[] = 'nombre_producto LIKE ?'; $params[] = "%$buscar%"; }
    if ($desde !== '')    { $where[] = 'fecha_vencimiento >= ?'; $params[] = $desde; }
    if ($hasta !== '')    { $where[] = 'fecha_vencimiento <= ?'; $params[] = $hasta; }
    $cond = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $s = $conexion->prepare("SELECT * FROM v_inventario$cond ORDER BY nombre_categoria, nombre_producto");
    $s->execute($params);
    $filas = $s->fetchAll();
    if (isset($_GET['pdf'])) {
        $nombre_cat = $cat_id ? (array_column($categorias, 'nombre_categoria', 'id_categoria')[$cat_id] ?? '') : 'todas';
        registrar_evento($conexion, 'EXPORTAR_PDF', 'inventario_general', null, 'PDF de reporte de inventario (' . count($filas) . ' registros)');
        $pdf = new PdfTabla('Reporte de inventario médico', [
            'Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de registros: ' . count($filas),
            'Filtros: categoría ' . $nombre_cat . ', estado ' . $estado . ($buscar !== '' ? ", nombre «$buscar»" : '')
                . ($desde !== '' ? ', vence desde ' . fecha_pdf($desde) : '') . ($hasta !== '' ? ', hasta ' . fecha_pdf($hasta) : ''),
        ]);
        $datos = [];
        foreach ($filas as $f) {
            $datos[] = [$f['id_producto'], $f['nombre_producto'], $f['nombre_categoria'], $f['nombre_presentacion'], $f['cantidad'],
                        $f['fecha_vencimiento'] ? fecha_pdf($f['fecha_vencimiento']) : 'No aplica', $f['estado_vencimiento'], $f['status']];
        }
        $pdf->tabla(['ID', 'Producto', 'Categoría', 'Presentación', 'Cant.', 'Vence', 'Estado', 'Estatus'], [5, 48, 18, 16, 7, 10, 10, 8], $datos);
        $pdf->descargar('reporte_inventario_' . date('Ymd_His') . '.pdf');
    }
    registrar_evento($conexion, 'REPORTE', 'inventario_general', null, 'Reporte de inventario generado (' . count($filas) . ' registros)');
}

$total_unidades = array_sum(array_column($filas, 'cantidad'));
$clase_estado = ['Vencido' => 'danger', 'Por Vencer' => 'warning text-dark', 'Disponible' => 'success'];

$base = '../';
$titulo_pagina = 'Reporte de inventario';
require __DIR__ . '/../includes/encabezado.php';
?>
<h1 class="h3 mb-3 no-imprimir">Reporte de inventario</h1>

<div class="card shadow-sm mb-4 no-imprimir">
  <div class="card-body">
    <?php if ($errores): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="GET" id="formReporte" class="row g-2 align-items-end" novalidate>
      <input type="hidden" name="generar" value="1">
      <?php if (count($categorias) > 1): ?>
      <div class="col-md-3">
        <label class="form-label small mb-1" for="cat">Categoría</label>
        <select class="form-select" id="cat" name="cat">
          <option value="">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id_categoria'] ?>" <?= $cat_id === (int) $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-3">
        <label class="form-label small mb-1" for="estado">Estado de vencimiento</label>
        <select class="form-select" id="estado" name="estado">
          <?php foreach (['todos' => 'Todos', 'Disponible' => 'Disponible', 'Por Vencer' => 'Por vencer', 'Vencido' => 'Vencido'] as $v => $t): ?>
            <option value="<?= e($v) ?>" <?= $estado === $v ? 'selected' : '' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (es_admin()): ?>
      <div class="col-md-2">
        <label class="form-label small mb-1" for="estatus">Estatus</label>
        <select class="form-select" id="estatus" name="estatus">
          <option value="Activo" <?= $estatus === 'Activo' ? 'selected' : '' ?>>Activos</option>
          <option value="Inactivo" <?= $estatus === 'Inactivo' ? 'selected' : '' ?>>Inactivos</option>
          <option value="todos" <?= $estatus === 'todos' ? 'selected' : '' ?>>Todos</option>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-4">
        <label class="form-label small mb-1" for="q">Nombre del insumo</label>
        <input type="text" class="form-control" id="q" name="q" maxlength="100" value="<?= e($buscar) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1" for="desde">Vence desde</label>
        <input type="date" class="form-control" id="desde" name="desde" value="<?= e($desde) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1" for="hasta">Vence hasta</label>
        <input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($hasta) ?>">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-institucional" type="submit">Generar reporte</button>
        <a class="btn btn-outline-secondary" href="reporte.php">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<?php if ($generar && !$errores): ?>
<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h2 class="h5 mb-1">Reporte de inventario médico</h2>
        <div class="small text-muted">
          Generado el <?= date('d/m/Y H:i') ?> por <?= e($_SESSION['nombre_completo']) ?>
          &middot; Filtros: categoría <?= $cat_id ? e(array_column($categorias, 'nombre_categoria', 'id_categoria')[$cat_id] ?? '—') : 'todas' ?>,
          estado <?= e($estado) ?><?= $buscar !== '' ? ', nombre «' . e($buscar) . '»' : '' ?>
          <?= $desde !== '' ? ', vence desde ' . e(date('d/m/Y', strtotime($desde))) : '' ?>
          <?= $hasta !== '' ? ', hasta ' . e(date('d/m/Y', strtotime($hasta))) : '' ?>
        </div>
      </div>
      <div class="d-flex gap-2 no-imprimir">
        <a class="btn btn-outline-danger btn-sm" href="?<?= e(http_build_query($_GET + ['pdf' => 1])) ?>">Descargar PDF</a>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()">Imprimir</button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
          <tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Presentación</th><th class="text-end">Cantidad</th><th>Vencimiento</th><th>Estado</th><th>Estatus</th></tr>
        </thead>
        <tbody>
        <?php if ($filas): foreach ($filas as $f): ?>
          <tr>
            <td><?= (int) $f['id_producto'] ?></td>
            <td><?= e($f['nombre_producto']) ?></td>
            <td><?= e($f['nombre_categoria']) ?></td>
            <td><?= e($f['nombre_presentacion']) ?></td>
            <td class="text-end"><?= (int) $f['cantidad'] ?></td>
            <td><?= $f['fecha_vencimiento'] ? e(date('d/m/Y', strtotime($f['fecha_vencimiento']))) : 'No aplica' ?></td>
            <td><span class="badge bg-<?= $clase_estado[$f['estado_vencimiento']] ?>"><?= e($f['estado_vencimiento']) ?></span></td>
            <td><?= e($f['status']) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted py-3">No hay registros con los filtros indicados.</td></tr>
        <?php endif; ?>
        </tbody>
        <?php if ($filas): ?>
        <tfoot class="table-light"><tr>
          <th colspan="4" class="text-end">Totales: <?= count($filas) ?> registros</th>
          <th class="text-end"><?= number_format($total_unidades, 0, ',', '.') ?></th><th colspan="3"></th>
        </tr></tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
<?php $scripts_extra = ['js/reporte.js']; require __DIR__ . '/../includes/pie.php'; ?>
