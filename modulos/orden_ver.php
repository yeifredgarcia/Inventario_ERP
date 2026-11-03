<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
requerir_login($conexion, '../');
requerir_permiso('ordenes', '../');

$id = $_GET['id'] ?? '';
if (!ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: ordenes.php'); exit(); }

$s = $conexion->prepare(
    "SELECT o.*, b.id_beneficiario, b.nombres, b.apellidos, b.tipo_documento, b.cedula, b.telefono, b.tipo_beneficiario, u.nombre_completo AS registrado_por
     FROM ordenes_entrega o
     INNER JOIN beneficiarios b ON b.id_beneficiario = o.id_beneficiario
     LEFT JOIN usuarios u ON u.id_usuario = o.usuario_registro
     WHERE o.id_orden = ?"
);
$s->execute([(int) $id]);
$o = $s->fetch();
if (!$o) { flash('danger', 'La orden no existe.'); header('Location: ordenes.php'); exit(); }

$s = $conexion->prepare(
    "SELECT m.cantidad, m.existencia_resultante, i.nombre_producto, p.nombre_presentacion
     FROM movimientos_inventario m
     INNER JOIN inventario_general i ON i.id_producto = m.id_producto
     INNER JOIN presentaciones p ON p.id_presentacion = i.id_presentacion
     WHERE m.id_orden = ? ORDER BY m.id_movimiento"
);
$s->execute([(int) $id]);
$lineas = $s->fetchAll();

$base = '../';
$titulo_pagina = 'Orden ' . numero_orden((int) $id);
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0">Orden de entrega <?= e(numero_orden((int) $id)) ?></h1>
  <div class="d-flex gap-2">
    <a href="orden_pdf.php?id=<?= (int) $id ?>" class="btn btn-danger">Descargar orden de entrega (PDF)</a>
    <a href="orden_form.php" class="btn btn-institucional">Nueva orden</a>
    <a href="ordenes.php" class="btn btn-outline-secondary">Volver</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm h-100"><div class="card-header bg-white fw-semibold">Datos de la entrega</div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Fecha de salida</span><strong><?= e(date('d/m/Y', strtotime($o['fecha_salida']))) ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Nombre</span><strong><?= e($o['nombres']) ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Apellido</span><strong><?= e($o['apellidos']) ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted"><?= e(etiqueta_documento($o['tipo_documento'])) ?></span><strong><?= e(documento_formateado($o['tipo_documento'], $o['cedula'])) ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Teléfono</span><strong><?= e($o['telefono'] ?? 'No indicado') ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Condición</span><strong><?= e($o['tipo_beneficiario']) ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Beneficiario N°</span><strong><?= e(numero_beneficiario((int) $o['id_beneficiario'])) ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Registrada por</span><strong><?= e($o['registrado_por'] ?? '—') ?></strong></li>
        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">N° de récipe</span><strong><?= e($o['recipe_numero'] ?? 'No indicado') ?></strong></li>
        <li class="list-group-item d-flex justify-content-between align-items-center gap-2"><span class="text-muted">Récipe médico (archivo)</span>
          <?php if ($o['recipe_archivo']): ?>
            <span class="text-end"><?= e($o['recipe_original']) ?>
              <a href="orden_recipe.php?id=<?= (int) $id ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm ms-1">Ver</a>
              <a href="orden_recipe.php?id=<?= (int) $id ?>&descargar=1" class="btn btn-outline-secondary btn-sm">Descargar</a></span>
          <?php else: ?><strong>Sin récipe adjunto</strong><?php endif; ?>
        </li>
        <li class="list-group-item"><span class="text-muted">Observación:</span> <?= e($o['observacion'] ?? 'Ninguna') ?></li>
      </ul>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card shadow-sm h-100"><div class="card-header bg-white fw-semibold">Medicamentos entregados</div>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light"><tr><th>N°</th><th>Medicamento</th><th>Presentación</th><th class="text-end">Cantidad</th><th class="text-end">Existencia restante</th></tr></thead>
          <tbody>
          <?php foreach ($lineas as $i => $l): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($l['nombre_producto']) ?></td>
              <td><?= e($l['nombre_presentacion']) ?></td>
              <td class="text-end"><?= (int) $l['cantidad'] ?></td>
              <td class="text-end"><?= (int) $l['existencia_resultante'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<p class="text-muted small mt-3">Imprima la orden de entrega en PDF y haga que ambas partes la firmen: el responsable que entrega y el beneficiario que recibe.</p>
<?php require __DIR__ . '/../includes/pie.php'; ?>
