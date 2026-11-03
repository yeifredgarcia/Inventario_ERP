<?php
/** Registro numerado de beneficiarios (trabajadores y afiliados) a quienes se entrega medicamento. */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('beneficiarios', '../');

$buscar  = mb_substr(trim($_GET['q'] ?? ''), 0, 100);
$tipo    = $_GET['tipo'] ?? '';
$estatus = $_GET['estatus'] ?? 'Activo';
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pag = 25;

if (!in_array($tipo, ['Trabajador', 'Afiliado'], true)) $tipo = '';
if (!es_admin() || !in_array($estatus, ['Activo', 'Inactivo', 'todos'], true)) $estatus = 'Activo';

$where = []; $params = [];
if ($buscar !== '') {
    $qdoc = normalizar_documento($buscar); // permite buscar «V-12.345.678», «12345678», «J-12345678-9»...
    $qdoc = $qdoc !== '' ? $qdoc : $buscar;
    $where[] = '(b.nombres LIKE ? OR b.apellidos LIKE ? OR b.cedula LIKE ? OR CONCAT(b.tipo_documento, b.cedula) LIKE ? OR b.telefono LIKE ?)';
    array_push($params, "%$buscar%", "%$buscar%", "%$qdoc%", "%$qdoc%", "%$buscar%");
}
if ($tipo !== '')          { $where[] = 'b.tipo_beneficiario = ?'; $params[] = $tipo; }
if ($estatus !== 'todos')  { $where[] = 'b.status = ?'; $params[] = $estatus; }
$cond = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$cols = "b.*, (SELECT COUNT(*) FROM ordenes_entrega o WHERE o.id_beneficiario = b.id_beneficiario) AS ordenes";

if (isset($_GET['pdf'])) {
    $s = $conexion->prepare("SELECT $cols FROM beneficiarios b$cond ORDER BY b.id_beneficiario ASC LIMIT 5000");
    $s->execute($params);
    $filas = [];
    foreach ($s->fetchAll() as $b) {
        $filas[] = [numero_beneficiario((int) $b['id_beneficiario']), $b['apellidos'], $b['nombres'], documento_formateado($b['tipo_documento'], $b['cedula']), $b['telefono'] ?? '', $b['tipo_beneficiario'], $b['ordenes'], $b['status'], fecha_pdf($b['fecha_registro'])];
    }
    registrar_evento($conexion, 'EXPORTAR_PDF', 'beneficiarios', null, 'PDF del registro de beneficiarios (' . count($filas) . ' registros)');
    $pdf = new PdfTabla('Registro de beneficiarios', [
        'Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de beneficiarios: ' . count($filas),
        'Filtros: ' . ($tipo ?: 'trabajadores y afiliados') . ($buscar !== '' ? ", búsqueda «$buscar»" : ''),
    ]);
    $pdf->tabla(['N°', 'Apellidos', 'Nombres', 'Documento', 'Teléfono', 'Condición', 'Ordenes', 'Estatus', 'Registro'], [6, 24, 24, 17, 14, 11, 7, 8, 11], $filas);
    $pdf->descargar('beneficiarios_' . date('Ymd_His') . '.pdf');
}

$s = $conexion->prepare("SELECT COUNT(*) FROM beneficiarios b$cond");
$s->execute($params);
$total      = (int) $s->fetchColumn();
$total_pags = max(1, (int) ceil($total / $por_pag));
$pagina     = min($pagina, $total_pags);
$offset     = ($pagina - 1) * $por_pag;

$s = $conexion->prepare("SELECT $cols FROM beneficiarios b$cond ORDER BY b.id_beneficiario ASC LIMIT $por_pag OFFSET $offset");
$s->execute($params);
$beneficiarios = $s->fetchAll();

$consulta = array_filter(['q' => $buscar, 'tipo' => $tipo, 'estatus' => $estatus !== 'Activo' ? $estatus : ''], fn($v) => $v !== '');

$base = '../';
$titulo_pagina = 'Beneficiarios';
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Registro de beneficiarios</h1>
  <a href="beneficiario_form.php" class="btn btn-success btn-sm">+ Registrar beneficiario</a>
</div>

<div class="card shadow-sm"><div class="card-body">
  <form method="GET" class="row g-2 align-items-end mb-3" novalidate>
    <div class="col-md-4">
      <label class="form-label small mb-1" for="q">Buscar por nombre, documento (cédula, RIF, pasaporte) o teléfono</label>
      <input type="text" class="form-control" id="q" name="q" maxlength="100" value="<?= e($buscar) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1" for="tipo">Condición</label>
      <select class="form-select" id="tipo" name="tipo">
        <option value="">Todas</option>
        <option value="Trabajador" <?= $tipo === 'Trabajador' ? 'selected' : '' ?>>Trabajador</option>
        <option value="Afiliado" <?= $tipo === 'Afiliado' ? 'selected' : '' ?>>Afiliado</option>
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
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-institucional" type="submit">Filtrar</button>
      <a class="btn btn-outline-secondary" href="beneficiarios.php">Limpiar</a>
      <a class="btn btn-outline-danger" href="?<?= e(http_build_query($consulta + ['pdf' => 1])) ?>">PDF</a>
    </div>
  </form>

  <div class="text-muted small mb-2"><?= $total ?> beneficiario(s)</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light"><tr><th>N°</th><th>Apellidos</th><th>Nombres</th><th>Documento</th><th>Teléfono</th><th>Condición</th><th class="text-end">Órdenes</th><th>Estatus</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php if ($beneficiarios): foreach ($beneficiarios as $b): ?>
        <tr class="<?= $b['status'] === 'Inactivo' ? 'table-secondary' : '' ?>">
          <td class="fw-semibold"><?= e(numero_beneficiario((int) $b['id_beneficiario'])) ?></td>
          <td><?= e($b['apellidos']) ?></td>
          <td><?= e($b['nombres']) ?></td>
          <td class="text-nowrap"><?= e(documento_formateado($b['tipo_documento'], $b['cedula'])) ?></td>
          <td class="text-nowrap"><?= e($b['telefono'] ?? '—') ?></td>
          <td><span class="badge bg-<?= $b['tipo_beneficiario'] === 'Trabajador' ? 'primary' : 'info text-dark' ?>"><?= e($b['tipo_beneficiario']) ?></span></td>
          <td class="text-end"><?= (int) $b['ordenes'] ?></td>
          <td><?= e($b['status']) ?></td>
          <td class="text-nowrap">
            <?php if ($b['status'] === 'Activo'): ?>
              <a href="orden_form.php?id_beneficiario=<?= (int) $b['id_beneficiario'] ?>" class="btn btn-institucional btn-sm">Nueva orden</a>
            <?php endif; ?>
            <a href="beneficiario_form.php?id=<?= (int) $b['id_beneficiario'] ?>" class="btn btn-warning btn-sm">Editar</a>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="9" class="text-center text-muted py-4">No hay beneficiarios con los filtros indicados.</td></tr>
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
