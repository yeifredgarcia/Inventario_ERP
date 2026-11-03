<?php
/** Listado de órdenes de entrega con filtros y PDF. */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('ordenes', '../');

$buscar  = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
$tipo    = $_GET['tipo'] ?? '';
$desde   = trim($_GET['desde'] ?? '');
$hasta   = trim($_GET['hasta'] ?? '');
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pag = 25;
$errores = [];

if (!in_array($tipo, ['Trabajador', 'Afiliado'], true)) $tipo = '';
foreach (['desde' => $desde, 'hasta' => $hasta] as $campo => $valor) {
    if ($valor !== '' && !fecha_valida($valor)) $errores[] = "La fecha \"$campo\" no es válida.";
}
if (!$errores && $desde !== '' && $hasta !== '' && $desde > $hasta) {
    $errores[] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
}

$where = []; $params = [];
if (!$errores) {
    if ($buscar !== '') {
        $num = (int) preg_replace('/\D/', '', $buscar); // permite buscar por «OE-000012» o por «12»
        $where[] = '(b.nombres LIKE ? OR b.apellidos LIKE ? OR CONCAT(b.tipo_documento, b.cedula) LIKE ? OR b.cedula LIKE ? OR o.recipe_numero LIKE ? OR o.id_orden = ?)';
        $qdoc = normalizar_documento($buscar);
        $qdoc = $qdoc !== '' ? $qdoc : $buscar;
        array_push($params, "%$buscar%", "%$buscar%", "%$qdoc%", "%$qdoc%", "%$buscar%", $num);
    }
    if ($tipo !== '')  { $where[] = 'b.tipo_beneficiario = ?'; $params[] = $tipo; }
    if ($desde !== '') { $where[] = 'o.fecha_salida >= ?'; $params[] = $desde; }
    if ($hasta !== '') { $where[] = 'o.fecha_salida <= ?'; $params[] = $hasta; }
}
$cond = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$from = "FROM ordenes_entrega o
         INNER JOIN beneficiarios b ON b.id_beneficiario = o.id_beneficiario
         LEFT JOIN usuarios u ON u.id_usuario = o.usuario_registro";
$cols = "o.id_orden, o.fecha_salida, o.recipe_archivo, o.recipe_numero, b.nombres, b.apellidos, b.tipo_documento, b.cedula, b.tipo_beneficiario, u.nombre_usuario AS registrado_por,
         (SELECT COUNT(*) FROM movimientos_inventario m WHERE m.id_orden = o.id_orden) AS items,
         (SELECT COALESCE(SUM(m.cantidad), 0) FROM movimientos_inventario m WHERE m.id_orden = o.id_orden) AS unidades";

if (isset($_GET['pdf']) && !$errores) {
    $s = $conexion->prepare("SELECT $cols $from$cond ORDER BY o.id_orden DESC LIMIT 5000");
    $s->execute($params);
    $filas = [];
    foreach ($s->fetchAll() as $o) {
        $filas[] = [numero_orden((int) $o['id_orden']), fecha_pdf($o['fecha_salida']), $o['apellidos'] . ' ' . $o['nombres'], documento_formateado($o['tipo_documento'], $o['cedula']), $o['tipo_beneficiario'], $o['items'], $o['unidades'], $o['recipe_archivo'] ? 'Sí' : 'No', $o['registrado_por'] ?? ''];
    }
    registrar_evento($conexion, 'EXPORTAR_PDF', 'ordenes_entrega', null, 'PDF del listado de órdenes (' . count($filas) . ' registros)');
    $pdf = new PdfTabla('Órdenes de entrega de medicamentos', [
        'Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de órdenes: ' . count($filas),
        'Filtros: ' . ($tipo ?: 'trabajadores y afiliados') . ($buscar !== '' ? ", búsqueda «$buscar»" : '')
            . ($desde !== '' ? ', desde ' . fecha_pdf($desde) : '') . ($hasta !== '' ? ', hasta ' . fecha_pdf($hasta) : ''),
    ]);
    $pdf->tabla(['N° orden', 'Fecha', 'Beneficiario', 'Documento', 'Condición', 'Medic.', 'Unid.', 'Récipe', 'Registró'], [11, 10, 36, 16, 11, 7, 7, 6, 14], $filas);
    $pdf->descargar('ordenes_entrega_' . date('Ymd_His') . '.pdf');
}

$s = $conexion->prepare("SELECT COUNT(*) $from$cond");
$s->execute($params);
$total      = (int) $s->fetchColumn();
$total_pags = max(1, (int) ceil($total / $por_pag));
$pagina     = min($pagina, $total_pags);
$offset     = ($pagina - 1) * $por_pag;

$s = $conexion->prepare("SELECT $cols $from$cond ORDER BY o.id_orden DESC LIMIT $por_pag OFFSET $offset");
$s->execute($params);
$ordenes = $s->fetchAll();

$consulta = array_filter(['q' => $buscar, 'tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta], fn($v) => $v !== '');

$base = '../';
$titulo_pagina = 'Órdenes de entrega';
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Órdenes de entrega de medicamentos</h1>
  <a href="orden_form.php" class="btn btn-danger btn-sm">− Nueva orden de entrega</a>
</div>

<div class="card shadow-sm"><div class="card-body">
  <?php if ($errores): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="GET" class="row g-2 align-items-end mb-3" novalidate>
    <div class="col-md-3">
      <label class="form-label small mb-1" for="q">N° de orden, nombre, documento o N° de récipe</label>
      <input type="text" class="form-control" id="q" name="q" maxlength="100" value="<?= e($buscar) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1" for="tipo">Condición</label>
      <select class="form-select" id="tipo" name="tipo">
        <option value="">Todas</option>
        <option value="Trabajador" <?= $tipo === 'Trabajador' ? 'selected' : '' ?>>Trabajador</option>
        <option value="Afiliado" <?= $tipo === 'Afiliado' ? 'selected' : '' ?>>Afiliado</option>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label small mb-1" for="desde">Desde</label><input type="date" class="form-control" id="desde" name="desde" value="<?= e($desde) ?>"></div>
    <div class="col-md-2"><label class="form-label small mb-1" for="hasta">Hasta</label><input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($hasta) ?>"></div>
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-institucional" type="submit">Filtrar</button>
      <a class="btn btn-outline-secondary" href="ordenes.php">Limpiar</a>
      <a class="btn btn-outline-danger" href="?<?= e(http_build_query($consulta + ['pdf' => 1])) ?>">PDF</a>
    </div>
  </form>

  <div class="text-muted small mb-2"><?= $total ?> orden(es)</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light"><tr><th>N° orden</th><th>Fecha de salida</th><th>Beneficiario</th><th>Documento</th><th>Condición</th><th class="text-end">Medicamentos</th><th class="text-end">Unidades</th><th>Récipe</th><th>Registró</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php if ($ordenes): foreach ($ordenes as $o): ?>
        <tr>
          <td class="fw-semibold"><?= e(numero_orden((int) $o['id_orden'])) ?></td>
          <td class="text-nowrap"><?= e(fecha_pdf($o['fecha_salida'])) ?></td>
          <td><?= e($o['apellidos'] . ' ' . $o['nombres']) ?></td>
          <td class="text-nowrap"><?= e(documento_formateado($o['tipo_documento'], $o['cedula'])) ?></td>
          <td><span class="badge bg-<?= $o['tipo_beneficiario'] === 'Trabajador' ? 'primary' : 'info text-dark' ?>"><?= e($o['tipo_beneficiario']) ?></span></td>
          <td class="text-end"><?= (int) $o['items'] ?></td>
          <td class="text-end"><?= (int) $o['unidades'] ?></td>
          <td><?= $o['recipe_archivo'] ? '<span class="badge bg-success">Adjunto</span>' : '<span class="badge bg-secondary">No</span>' ?><?php if ($o['recipe_numero']): ?><div class="small text-muted">N° <?= e($o['recipe_numero']) ?></div><?php endif; ?></td>
          <td><?= e($o['registrado_por'] ?? '—') ?></td>
          <td class="text-nowrap">
            <a href="orden_ver.php?id=<?= (int) $o['id_orden'] ?>" class="btn btn-outline-secondary btn-sm">Ver</a>
            <a href="orden_pdf.php?id=<?= (int) $o['id_orden'] ?>" class="btn btn-danger btn-sm">PDF</a>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="10" class="text-center text-muted py-4">No hay órdenes con los filtros indicados.</td></tr>
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
