<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
requerir_login($conexion, '../');
requerir_permiso('movimientos', '../');

// Las salidas se registran con una orden de entrega a un beneficiario; aquí solo se registran entradas
if (($_GET['tipo'] ?? '') === 'Salida') {
    header('Location: orden_form.php');
    exit();
}
$tipo = 'Entrada';

$d = ['id_producto' => $_GET['id_producto'] ?? '', 'cantidad' => '', 'fecha_movimiento' => date('Y-m-d'),
      'nombre_persona' => '', 'cedula_persona' => '', 'destinatario' => '', 'observacion' => ''];
if (isset($_SESSION['datos_form'])) { $d = array_merge($d, $_SESSION['datos_form']); unset($_SESSION['datos_form']); }
$errores_form = $_SESSION['errores_form'] ?? [];
unset($_SESSION['errores_form']);

$productos = $conexion->query(
    "SELECT id_producto, nombre_producto, nombre_categoria, nombre_presentacion, cantidad, estado_vencimiento
     FROM v_inventario WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ") ORDER BY nombre_producto, id_producto"
)->fetchAll();

$es_salida = $tipo === 'Salida';
$base = '../';
$titulo_pagina = $es_salida ? 'Registrar salida' : 'Registrar entrada';
$scripts_extra = ['js/movimientos.js'];
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="row justify-content-center"><div class="col-lg-7">
  <div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-3"><?= $es_salida ? 'Registrar salida de insumo' : 'Registrar entrada de insumo' ?></h1>
    <?php if ($errores_form): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores_form as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form action="movimiento_guardar.php" method="POST" id="formMovimiento" novalidate>
      <?= csrf_campo() ?>
      <input type="hidden" name="tipo" id="tipo" value="<?= e($tipo) ?>">

      <div class="mb-3">
        <label class="form-label" for="buscarProducto">Insumo</label>
        <input type="text" class="form-control mb-2" id="buscarProducto" placeholder="Escriba para filtrar la lista" autocomplete="off">
        <select class="form-select" id="id_producto" name="id_producto" size="6" required>
          <?php foreach ($productos as $p):
            $vencido = $es_salida && $p['estado_vencimiento'] === 'Vencido'; ?>
            <option value="<?= (int) $p['id_producto'] ?>" <?= (string) $d['id_producto'] === (string) $p['id_producto'] ? 'selected' : '' ?> <?= $vencido ? 'disabled' : '' ?>>
              <?= e($p['nombre_producto']) ?> — <?= e($p['nombre_categoria']) ?> · <?= e($p['nombre_presentacion']) ?> (existencia: <?= (int) $p['cantidad'] ?>)<?= $vencido ? ' [VENCIDO]' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($es_salida): ?><div class="form-text">Los insumos vencidos no pueden retirarse.</div><?php endif; ?>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="cantidad">Cantidad</label>
          <input type="text" inputmode="numeric" class="form-control" id="cantidad" name="cantidad" maxlength="7" value="<?= e($d['cantidad']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="fecha_movimiento">Fecha del movimiento</label>
          <input type="date" class="form-control" id="fecha_movimiento" name="fecha_movimiento" min="<?= FECHA_MINIMA ?>" max="<?= date('Y-m-d') ?>" value="<?= e($d['fecha_movimiento']) ?>" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-7 mb-3">
          <label class="form-label" for="nombre_persona"><?= $es_salida ? 'Persona que retira' : 'Persona que entrega o proveedor' ?></label>
          <input type="text" class="form-control" id="nombre_persona" name="nombre_persona" maxlength="100" value="<?= e($d['nombre_persona']) ?>" required>
        </div>
        <div class="col-md-5 mb-3">
          <label class="form-label" for="cedula_persona">Documento (cédula, RIF, extranjero o pasaporte)</label>
          <input type="text" class="form-control" id="cedula_persona" name="cedula_persona" maxlength="20" value="<?= e($d['cedula_persona']) ?>" required>
        </div>
      </div>

      <?php if ($es_salida): ?>
      <div class="mb-3">
        <label class="form-label" for="destinatario">¿Para quién es? (paciente o servicio)</label>
        <input type="text" class="form-control" id="destinatario" name="destinatario" maxlength="100" value="<?= e($d['destinatario']) ?>" required>
      </div>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label" for="observacion">Observación (opcional)</label>
        <input type="text" class="form-control" id="observacion" name="observacion" maxlength="255" value="<?= e($d['observacion']) ?>">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-institucional">Registrar <?= $es_salida ? 'salida' : 'entrada' ?></button>
        <a href="movimientos.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div></div>
</div></div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
