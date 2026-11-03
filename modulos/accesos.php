<?php
/** Registro de quiénes entran y quiénes salen del sistema (solo administrador). */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
require_once __DIR__ . '/../includes/pdf.php';
requerir_login($conexion, '../');
requerir_permiso('auditoria', '../');

const GRUPOS_ACCESO = [
    'entradas' => ['Entradas al sistema', ['LOGIN']],
    'salidas'  => ['Salidas del sistema', ['LOGOUT', 'SESION_EXPIRADA']],
    'fallidos' => ['Intentos fallidos', ['LOGIN_FALLIDO', 'LOGIN_INACTIVO']],
];
const TODAS_ACCESO = ['LOGIN', 'LOGOUT', 'SESION_EXPIRADA', 'LOGIN_FALLIDO', 'LOGIN_INACTIVO'];

$usuario = mb_substr(trim($_GET['usuario'] ?? ''), 0, 50);
$grupo   = $_GET['grupo'] ?? '';
$desde   = trim($_GET['desde'] ?? '');
$hasta   = trim($_GET['hasta'] ?? '');
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pag = 50;
$errores = [];

if (!isset(GRUPOS_ACCESO[$grupo])) $grupo = '';
foreach (['desde' => $desde, 'hasta' => $hasta] as $campo => $valor) {
    if ($valor !== '' && !fecha_valida($valor)) $errores[] = "La fecha \"$campo\" no es válida.";
}
if (!$errores && $desde !== '' && $hasta !== '' && $desde > $hasta) {
    $errores[] = 'La fecha "desde" no puede ser posterior a la fecha "hasta".';
}

$acciones = $grupo !== '' ? GRUPOS_ACCESO[$grupo][1] : TODAS_ACCESO;
$marcas   = implode(',', array_fill(0, count($acciones), '?'));
$where    = ["accion IN ($marcas)"]; $params = $acciones;
if (!$errores) {
    if ($usuario !== '') { $where[] = 'nombre_usuario LIKE ?'; $params[] = "%$usuario%"; }
    if ($desde !== '')   { $where[] = 'fecha_registro >= ?'; $params[] = "$desde 00:00:00"; }
    if ($hasta !== '')   { $where[] = 'fecha_registro <= ?'; $params[] = "$hasta 23:59:59"; }
}
$cond = ' WHERE ' . implode(' AND ', $where);

if (isset($_GET['pdf']) && !$errores) {
    $s = $conexion->prepare("SELECT * FROM auditoria$cond ORDER BY id_evento DESC LIMIT 5000");
    $s->execute($params);
    $filas = [];
    foreach ($s->fetchAll() as $ev) {
        $filas[] = [fecha_pdf($ev['fecha_registro'], true), $ev['nombre_usuario'], etiqueta_accion($ev['accion']), $ev['ip_origen'] ?? '', $ev['detalle'] ?? ''];
    }
    registrar_evento($conexion, 'EXPORTAR_PDF', 'auditoria', null, 'PDF de accesos al sistema (' . count($filas) . ' registros)');
    $pdf = new PdfTabla('Registro de accesos al sistema', [
        'Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['nombre_completo'] . ' - Total de eventos: ' . count($filas),
        'Filtros: ' . ($grupo !== '' ? GRUPOS_ACCESO[$grupo][0] : 'todos los accesos') . ($usuario !== '' ? ", usuario «$usuario»" : '')
            . ($desde !== '' ? ', desde ' . fecha_pdf($desde) : '') . ($hasta !== '' ? ', hasta ' . fecha_pdf($hasta) : ''),
    ]);
    $pdf->tabla(['Fecha y hora', 'Usuario', 'Evento', 'IP', 'Detalle'], [16, 20, 34, 16, 55], $filas);
    $pdf->descargar('accesos_sistema_' . date('Ymd_His') . '.pdf');
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

$consulta = array_filter(['usuario' => $usuario, 'grupo' => $grupo, 'desde' => $desde, 'hasta' => $hasta], fn($v) => $v !== '');
$clase = ['LOGIN' => 'success', 'LOGOUT' => 'secondary', 'SESION_EXPIRADA' => 'secondary', 'LOGIN_FALLIDO' => 'danger', 'LOGIN_INACTIVO' => 'warning text-dark'];

$base = '../';
$titulo_pagina = 'Accesos al sistema';
require __DIR__ . '/../includes/encabezado.php';
?>
<h1 class="h3 mb-3">Accesos al sistema: quién entra y quién sale</h1>
<div class="card shadow-sm"><div class="card-body">
  <?php if ($errores): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="GET" class="row g-2 align-items-end mb-3" novalidate>
    <div class="col-md-3">
      <label class="form-label small mb-1" for="usuario">Usuario</label>
      <input type="text" class="form-control" id="usuario" name="usuario" maxlength="50" value="<?= e($usuario) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1" for="grupo">Evento</label>
      <select class="form-select" id="grupo" name="grupo">
        <option value="">Todos</option>
        <?php foreach (GRUPOS_ACCESO as $clave => [$texto]): ?>
          <option value="<?= e($clave) ?>" <?= $grupo === $clave ? 'selected' : '' ?>><?= e($texto) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label small mb-1" for="desde">Desde</label><input type="date" class="form-control" id="desde" name="desde" value="<?= e($desde) ?>"></div>
    <div class="col-md-2"><label class="form-label small mb-1" for="hasta">Hasta</label><input type="date" class="form-control" id="hasta" name="hasta" value="<?= e($hasta) ?>"></div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-institucional" type="submit">Filtrar</button>
      <a class="btn btn-outline-secondary" href="accesos.php">Limpiar</a>
    </div>
    <div class="col-12 text-end"><a class="btn btn-outline-danger btn-sm" href="?<?= e(http_build_query($consulta + ['pdf' => 1])) ?>">Exportar PDF</a></div>
  </form>

  <div class="text-muted small mb-2"><?= $total ?> evento(s)</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light"><tr><th>Fecha y hora</th><th>Usuario</th><th>Evento</th><th>IP</th><th>Detalle</th></tr></thead>
      <tbody>
      <?php if ($eventos): foreach ($eventos as $ev): ?>
        <tr>
          <td class="text-nowrap"><?= e(fecha_pdf($ev['fecha_registro'], true)) ?></td>
          <td><?= e($ev['nombre_usuario']) ?></td>
          <td><span class="badge bg-<?= $clase[$ev['accion']] ?? 'secondary' ?>"><?= e(etiqueta_accion($ev['accion'])) ?></span></td>
          <td class="text-nowrap"><?= e($ev['ip_origen']) ?></td>
          <td><?= e($ev['detalle']) ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay accesos con los filtros indicados.</td></tr>
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
