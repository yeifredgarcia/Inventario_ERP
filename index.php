<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/auditoria.php';
requerir_login($conexion, '');

$admin = es_admin();
$uid   = id_usuario_actual();
$cats  = categorias_sql(); // solo enteros fijos del código

// Totales de los insumos activos que muestra el sistema
$t = $conexion->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(estado_vencimiento = 'Por Vencer'), 0) AS por_vencer,
            COALESCE(SUM(estado_vencimiento = 'Vencido'), 0)    AS vencidos
     FROM v_inventario WHERE status = 'Activo' AND id_categoria IN ($cats)"
)->fetch();

// Últimos accesos al sistema (el administrador ve a todos; el encargado solo los suyos)
$s = $conexion->prepare(
    "SELECT nombre_usuario, accion, ip_origen, fecha_registro FROM auditoria
     WHERE accion IN ('LOGIN', 'LOGIN_FALLIDO', 'LOGIN_INACTIVO', 'LOGOUT', 'SESION_EXPIRADA')" . ($admin ? '' : ' AND id_usuario = ?') . "
     ORDER BY id_evento DESC LIMIT 8"
);
$s->execute($admin ? [] : [$uid]);
$accesos = $s->fetchAll();

// Últimas modificaciones de datos
$s = $conexion->prepare(
    "SELECT nombre_usuario, accion, detalle, fecha_registro FROM auditoria
     WHERE accion IN ('CREAR_PRODUCTO', 'EDITAR_PRODUCTO', 'BAJA_PRODUCTO', 'REACTIVAR_PRODUCTO', 'CREAR_USUARIO', 'EDITAR_USUARIO')" . ($admin ? '' : ' AND id_usuario = ?') . "
     ORDER BY id_evento DESC LIMIT 8"
);
$s->execute($admin ? [] : [$uid]);
$cambios = $s->fetchAll();

// Últimas entradas y salidas de insumos (las ven ambos roles)
$movs = $conexion->query(
    "SELECT m.tipo, m.cantidad, m.fecha_movimiento, m.nombre_persona, m.destinatario, i.nombre_producto, u.nombre_usuario AS registrado_por
     FROM movimientos_inventario m
     INNER JOIN inventario_general i ON i.id_producto = m.id_producto
     LEFT JOIN usuarios u ON u.id_usuario = m.usuario_registro
     WHERE i.id_categoria IN ($cats)
     ORDER BY m.id_movimiento DESC LIMIT 8"
)->fetchAll();

// Resumen de las personas que ingresan (solo administrador)
$personas = [];
if ($admin) {
    $personas = $conexion->query(
        "SELECT u.nombre_completo, u.nombre_usuario, u.rol, u.status,
                (SELECT MAX(a.fecha_registro) FROM auditoria a WHERE a.id_usuario = u.id_usuario AND a.accion = 'LOGIN') AS ultimo_ingreso,
                (SELECT COUNT(*) FROM auditoria a WHERE a.id_usuario = u.id_usuario AND a.accion = 'LOGIN') AS ingresos,
                (SELECT COUNT(*) FROM auditoria a WHERE a.id_usuario = u.id_usuario
                    AND a.accion IN ('CREAR_PRODUCTO', 'EDITAR_PRODUCTO', 'BAJA_PRODUCTO', 'REACTIVAR_PRODUCTO')) AS modificaciones,
                (SELECT COUNT(*) FROM auditoria a WHERE a.id_usuario = u.id_usuario
                    AND a.accion IN ('ENTRADA_INSUMO', 'SALIDA_INSUMO')) AS movimientos
         FROM usuarios u ORDER BY u.rol, u.nombre_completo"
    )->fetchAll();
}

$base = '';
$titulo_pagina = 'Inicio';
require __DIR__ . '/includes/encabezado.php';
?>
<h1 class="h3 mb-1">Bienvenido, <?= e($_SESSION['nombre_completo']) ?></h1>
<p class="text-muted mb-4">Vista general del sistema de inventario de medicamentos.</p>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card text-center shadow-sm"><div class="card-body">
      <div class="display-6 texto-institucional"><?= (int) $t['total'] ?></div>
      <div class="text-muted">Total de insumos activos</div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card text-center shadow-sm"><div class="card-body">
      <div class="display-6 text-warning"><?= (int) $t['por_vencer'] ?></div>
      <div class="text-muted">Por vencer (90 días o menos)</div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card text-center shadow-sm"><div class="card-body">
      <div class="display-6 text-danger"><?= (int) $t['vencidos'] ?></div>
      <div class="text-muted">Vencidos</div>
    </div></div>
  </div>
</div>

<div class="card shadow-sm borde-institucional mb-4">
  <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h2 class="h5 mb-1">Inventario general</h2>
      <p class="small text-muted mb-0">Consulte, registre y corrija los medicamentos. También puede dar de baja (administrador) y exportar a PDF.</p>
    </div>
    <a href="modulos/listar.php" class="btn btn-institucional">Abrir inventario general</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card shadow-sm h-100"><div class="card-body d-flex justify-content-between align-items-center gap-2">
      <div><h2 class="h6 mb-1">Orden de entrega de medicamentos</h2><p class="small text-muted mb-0">Registre la salida a un beneficiario y genere el PDF con firmas.</p></div>
      <a href="modulos/orden_form.php" class="btn btn-danger btn-sm text-nowrap">Nueva orden</a>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm h-100"><div class="card-body d-flex justify-content-between align-items-center gap-2">
      <div><h2 class="h6 mb-1">Registro de beneficiarios</h2><p class="small text-muted mb-0">Trabajadores y afiliados, numerados, con su cédula.</p></div>
      <a href="modulos/beneficiarios.php" class="btn btn-institucional btn-sm text-nowrap">Ver registro</a>
    </div></div>
  </div>
</div>

<?php if ($admin): ?>
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white fw-semibold">Personas con acceso al sistema</div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light"><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estatus</th><th>Último ingreso</th><th class="text-end">Ingresos</th><th class="text-end">Cambios en inventario</th><th class="text-end">Entradas y salidas</th></tr></thead>
      <tbody>
      <?php foreach ($personas as $p): ?>
        <tr>
          <td><?= e($p['nombre_completo']) ?></td>
          <td><?= e($p['nombre_usuario']) ?></td>
          <td><?= e($p['rol']) ?></td>
          <td><span class="badge bg-<?= $p['status'] === 'Activo' ? 'success' : 'secondary' ?>"><?= e($p['status']) ?></span></td>
          <td><?= $p['ultimo_ingreso'] ? e(date('d/m/Y H:i', strtotime($p['ultimo_ingreso']))) : 'Nunca ha ingresado' ?></td>
          <td class="text-end"><?= (int) $p['ingresos'] ?></td>
          <td class="text-end"><?= (int) $p['modificaciones'] ?></td>
          <td class="text-end"><?= (int) $p['movimientos'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-semibold"><?= $admin ? 'Quiénes han entrado y salido' : 'Mis últimos ingresos' ?></div>
      <ul class="list-group list-group-flush">
      <?php if ($accesos): foreach ($accesos as $a): ?>
        <li class="list-group-item small">
          <strong><?= e($a['nombre_usuario']) ?></strong> &middot; <?= e(etiqueta_accion($a['accion'])) ?>
          <div class="text-muted"><?= e(date('d/m/Y H:i', strtotime($a['fecha_registro']))) ?> &middot; IP <?= e($a['ip_origen']) ?></div>
        </li>
      <?php endforeach; else: ?>
        <li class="list-group-item text-muted small">Aún no hay accesos registrados.</li>
      <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-semibold"><?= $admin ? 'Últimas modificaciones' : 'Mis últimas modificaciones' ?></div>
      <ul class="list-group list-group-flush">
      <?php if ($cambios): foreach ($cambios as $c): ?>
        <li class="list-group-item small">
          <strong><?= e($c['nombre_usuario']) ?></strong> &middot; <?= e(etiqueta_accion($c['accion'])) ?>
          <div><?= e($c['detalle']) ?></div>
          <div class="text-muted"><?= e(date('d/m/Y H:i', strtotime($c['fecha_registro']))) ?></div>
        </li>
      <?php endforeach; else: ?>
        <li class="list-group-item text-muted small">Aún no hay modificaciones registradas.</li>
      <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
    <span>Últimas entradas y salidas de medicamentos</span>
    <a href="modulos/movimientos.php" class="btn btn-outline-secondary btn-sm">Ver todas</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light"><tr><th>Fecha</th><th>Tipo</th><th>Medicamento</th><th class="text-end">Cant.</th><th>Persona</th><th>Para quién</th><th>Registró</th></tr></thead>
      <tbody>
      <?php if ($movs): foreach ($movs as $m): ?>
        <tr>
          <td class="text-nowrap"><?= e(date('d/m/Y', strtotime($m['fecha_movimiento']))) ?></td>
          <td><span class="badge bg-<?= $m['tipo'] === 'Entrada' ? 'success' : 'danger' ?>"><?= e($m['tipo']) ?></span></td>
          <td><?= e($m['nombre_producto']) ?></td>
          <td class="text-end"><?= (int) $m['cantidad'] ?></td>
          <td><?= e($m['nombre_persona']) ?></td>
          <td><?= e($m['destinatario'] ?? '—') ?></td>
          <td><?= e($m['registrado_por'] ?? '—') ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="7" class="text-center text-muted py-3">Aún no hay entradas ni salidas registradas.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($admin): ?>
  <div class="text-end mt-3">
    <a href="modulos/accesos.php" class="btn btn-outline-secondary btn-sm">Ver todos los accesos</a>
    <a href="modulos/auditoria.php" class="btn btn-outline-secondary btn-sm">Ver la auditoría completa</a>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/pie.php'; ?>
