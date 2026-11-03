<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('consultar', '../');

// ---- Parámetros de filtro ----
$cat_id  = isset($_GET['cat']) && ctype_digit($_GET['cat']) ? (int) $_GET['cat'] : 0;
if (!categoria_visible($cat_id)) {
    // Si solo se muestra una categoría (Medicamentos), queda fija
    $cat_id = count(CATEGORIAS_VISIBLES) === 1 ? CATEGORIAS_VISIBLES[0] : 0;
}
$filtro  = $_GET['filtro'] ?? 'todos';
$buscar  = trim($_GET['q'] ?? '');
$estatus = $_GET['estatus'] ?? 'Activo';
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pag = 25;

if (!in_array($filtro, ['todos', 'por_vencer', 'vencidos'], true)) $filtro = 'todos';
if (!es_admin() || !in_array($estatus, ['Activo', 'Inactivo', 'todos'], true)) $estatus = 'Activo';
if (mb_strlen($buscar) > 100) $buscar = mb_substr($buscar, 0, 100);

$categorias = $conexion->query("SELECT id_categoria, nombre_categoria FROM categorias WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ") ORDER BY nombre_categoria")->fetchAll();

// ---- Título del módulo ----
$titulo_modulo = 'Inventario general';
if ($cat_id) {
    $s = $conexion->prepare("SELECT nombre_categoria FROM categorias WHERE id_categoria = ?");
    $s->execute([$cat_id]);
    $nombre_cat = $s->fetchColumn();
    if ($nombre_cat) $titulo_modulo = 'Inventario de ' . $nombre_cat;
    else $cat_id = 0;
}

// ---- Tarjetas de totales (solo activos de la categoría actual) ----
$sql_tot = "SELECT COUNT(*) AS total,
                   COALESCE(SUM(estado_vencimiento = 'Por Vencer'), 0) AS por_vencer,
                   COALESCE(SUM(estado_vencimiento = 'Vencido'), 0) AS vencidos
            FROM v_inventario WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ")" . ($cat_id ? " AND id_categoria = ?" : "");
$s = $conexion->prepare($sql_tot);
$s->execute($cat_id ? [$cat_id] : []);
$tot = $s->fetch();

// ---- Consulta filtrada ----
$where = ['id_categoria IN (' . categorias_sql() . ')']; $params = [];
if ($cat_id)  { $where[] = 'id_categoria = ?'; $params[] = $cat_id; }
if ($filtro === 'por_vencer') $where[] = "estado_vencimiento = 'Por Vencer'";
if ($filtro === 'vencidos')   $where[] = "estado_vencimiento = 'Vencido'";
if ($estatus !== 'todos')     { $where[] = 'status = ?'; $params[] = $estatus; }
if ($buscar !== '') {
    $where[] = '(nombre_producto LIKE ? OR nombre_presentacion LIKE ?)';
    $params[] = "%$buscar%"; $params[] = "%$buscar%";
}
$cond = $where ? ' WHERE ' . implode(' AND ', $where) : '';

if (isset($_GET['pdf'])) {
    $s = $conexion->prepare("SELECT * FROM v_inventario$cond ORDER BY nombre_categoria, nombre_producto LIMIT 5000");
    $s->execute($params);
    $datos = [];
    foreach ($s->fetchAll() as $f) {
        $datos[] = [$f['id_producto'], $f['nombre_producto'], $f['nombre_categoria'], $f['nombre_presentacion'], $f['cantidad'],
                    $f['fecha_vencimiento'] ? fecha_pdf($f['fecha_vencimiento']) : 'No aplica', $f['estado_vencimiento'], $f['status']];
    }
    registrar_evento($conexion, 'EXPORTAR_PDF', 'inventario_general', null, 'PDF del inventario (' . count($datos) . ' registros)');
    $pdf = new PdfTabla($titulo_modulo, ['Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de registros: ' . count($datos)]);
    $pdf->tabla(['ID', 'Producto', 'Categoría', 'Presentación', 'Cant.', 'Vence', 'Estado', 'Estatus'], [5, 48, 18, 16, 7, 10, 10, 8], $datos);
    $pdf->descargar('inventario_' . date('Ymd_His') . '.pdf');
}

$s = $conexion->prepare("SELECT COUNT(*) FROM v_inventario$cond");
$s->execute($params);
$total_filas  = (int) $s->fetchColumn();
$total_pags   = max(1, (int) ceil($total_filas / $por_pag));
$pagina       = min($pagina, $total_pags);
$offset       = ($pagina - 1) * $por_pag;

$s = $conexion->prepare("SELECT * FROM v_inventario$cond ORDER BY id_producto DESC LIMIT $por_pag OFFSET $offset");
$s->execute($params);
$productos = $s->fetchAll();

$consulta = array_filter(['cat' => $cat_id ?: null, 'filtro' => $filtro !== 'todos' ? $filtro : null,
                          'q' => $buscar !== '' ? $buscar : null, 'estatus' => $estatus !== 'Activo' ? $estatus : null]);
$retorno  = http_build_query($consulta + ($pagina > 1 ? ['pagina' => $pagina] : []));

$clase_estado = ['Vencido' => 'danger', 'Por Vencer' => 'warning text-dark', 'Disponible' => 'success'];

$base = '../';
$titulo_pagina = $titulo_modulo;
require __DIR__ . '/../includes/encabezado.php';
?>
<h1 class="h3 mb-3"><?= e($titulo_modulo) ?></h1>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card text-center shadow-sm"><div class="card-body">
    <div class="display-6 texto-institucional"><?= (int) $tot['total'] ?></div><div class="text-muted">Total activos en esta sección</div></div></div></div>
  <div class="col-md-4"><div class="card text-center shadow-sm"><div class="card-body">
    <div class="display-6 text-warning"><?= (int) $tot['por_vencer'] ?></div><div class="text-muted">Por vencer</div></div></div></div>
  <div class="col-md-4"><div class="card text-center shadow-sm"><div class="card-body">
    <div class="display-6 text-danger"><?= (int) $tot['vencidos'] ?></div><div class="text-muted">Vencidos</div></div></div></div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-end mb-3">
      <?php if (count($categorias) > 1): ?>
      <div class="col-md-3">
        <label for="cat" class="form-label small mb-1">Categoría</label>
        <select class="form-select" id="cat" name="cat">
          <option value="">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id_categoria'] ?>" <?= $cat_id === (int) $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-3">
        <label for="q" class="form-label small mb-1">Buscar</label>
        <input type="text" class="form-control" id="q" name="q" maxlength="100" value="<?= e($buscar) ?>" placeholder="Nombre o presentación">
      </div>
      <div class="col-md-2">
        <label for="filtro" class="form-label small mb-1">Estado de vencimiento</label>
        <select class="form-select" id="filtro" name="filtro">
          <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
          <option value="por_vencer" <?= $filtro === 'por_vencer' ? 'selected' : '' ?>>Por vencer</option>
          <option value="vencidos" <?= $filtro === 'vencidos' ? 'selected' : '' ?>>Vencidos</option>
        </select>
      </div>
      <?php if (es_admin()): ?>
      <div class="col-md-2">
        <label for="estatus" class="form-label small mb-1">Estatus</label>
        <select class="form-select" id="estatus" name="estatus">
          <option value="Activo" <?= $estatus === 'Activo' ? 'selected' : '' ?>>Activos</option>
          <option value="Inactivo" <?= $estatus === 'Inactivo' ? 'selected' : '' ?>>Inactivos</option>
          <option value="todos" <?= $estatus === 'todos' ? 'selected' : '' ?>>Todos</option>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-institucional" type="submit">Filtrar</button>
        <a class="btn btn-outline-secondary" href="listar.php<?= $cat_id ? '?cat=' . $cat_id : '' ?>">Limpiar</a>
      </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="text-muted small"><?= $total_filas ?> registro(s) encontrado(s)</span>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-danger btn-sm" href="?<?= e(http_build_query($consulta + ['pdf' => 1])) ?>">Exportar PDF</a>
        <?php if (puede('crear')): ?>
          <a href="agregar.php<?= $cat_id ? '?cat=' . $cat_id : '' ?>" class="btn btn-success btn-sm">+ Registrar nuevo insumo</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr><th>N°</th><th>Producto</th><th>Categoría</th><th>Presentación</th><th class="text-end">Cantidad</th><th>Vencimiento</th><th>Estado</th><th>Estatus</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php if ($productos): foreach ($productos as $p): ?>
          <tr class="<?= $p['status'] === 'Inactivo' ? 'table-secondary' : '' ?>">
            <td><?= (int) $p['id_producto'] ?></td>
            <td><strong><?= e($p['nombre_producto']) ?></strong></td>
            <td><?= e($p['nombre_categoria']) ?></td>
            <td><?= e($p['nombre_presentacion']) ?></td>
            <td class="text-end"><?= (int) $p['cantidad'] ?></td>
            <td><?= $p['fecha_vencimiento'] ? e(date('d/m/Y', strtotime($p['fecha_vencimiento']))) : 'No aplica' ?></td>
            <td><span class="badge bg-<?= $clase_estado[$p['estado_vencimiento']] ?>"><?= e($p['estado_vencimiento']) ?></span></td>
            <td><?= e($p['status']) ?></td>
            <td class="text-nowrap">
              <?php if ($p['status'] === 'Activo' && puede('editar')): ?>
                <a href="editar.php?id=<?= (int) $p['id_producto'] ?><?= $cat_id ? '&cat=' . $cat_id : '' ?>" class="btn btn-warning btn-sm">Editar</a>
              <?php endif; ?>
              <?php if (puede('estatus')): ?>
                <form action="estatus.php" method="POST" class="d-inline" data-confirmar="<?= $p['status'] === 'Activo' ? '¿Desea dar de baja este insumo?' : '¿Desea reactivar este insumo?' ?>">
                  <?= csrf_campo() ?>
                  <input type="hidden" name="id_producto" value="<?= (int) $p['id_producto'] ?>">
                  <input type="hidden" name="accion" value="<?= $p['status'] === 'Activo' ? 'inactivar' : 'activar' ?>">
                  <input type="hidden" name="retorno" value="<?= e($retorno) ?>">
                  <button class="btn btn-sm <?= $p['status'] === 'Activo' ? 'btn-danger' : 'btn-success' ?>" type="submit">
                    <?= $p['status'] === 'Activo' ? 'Dar de baja' : 'Reactivar' ?>
                  </button>
                </form>
              <?php elseif ($p['status'] === 'Activo'): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bloqueado="<?= e(mensaje_permiso('estatus')) ?>">Dar de baja &#128274;</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="9" class="text-center text-muted py-4">No se encontraron insumos con los filtros indicados.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pags > 1): ?>
    <nav aria-label="Paginación"><ul class="pagination pagination-sm justify-content-center mb-0">
      <?php for ($i = 1; $i <= $total_pags; $i++):
        if ($i !== 1 && $i !== $total_pags && abs($i - $pagina) > 2) { if (abs($i - $pagina) === 3) echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; continue; } ?>
        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
          <a class="page-link" href="?<?= e(http_build_query($consulta + ['pagina' => $i])) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
