<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
requerir_login($conexion, '../');
requerir_permiso('beneficiarios', '../');

$id     = $_GET['id'] ?? '';
$volver = ($_GET['volver'] ?? '') === 'orden' ? 'orden' : '';
$b = ['nombres' => '', 'apellidos' => '', 'tipo_documento' => 'V', 'cedula' => '', 'telefono' => '', 'tipo_beneficiario' => 'Trabajador', 'status' => 'Activo'];
$editando = false;

if ($id !== '') {
    if (!ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: beneficiarios.php'); exit(); }
    $s = $conexion->prepare("SELECT id_beneficiario, nombres, apellidos, tipo_documento, cedula, telefono, tipo_beneficiario, status FROM beneficiarios WHERE id_beneficiario = ?");
    $s->execute([(int) $id]);
    $b = $s->fetch();
    if (!$b) { flash('danger', 'El beneficiario no existe.'); header('Location: beneficiarios.php'); exit(); }
    $editando = true;
}
if (isset($_SESSION['datos_form'])) { $b = array_merge($b, $_SESSION['datos_form']); unset($_SESSION['datos_form']); }
$errores_form = $_SESSION['errores_form'] ?? [];
unset($_SESSION['errores_form']);

$base = '../';
$titulo_pagina = $editando ? 'Editar beneficiario' : 'Registrar beneficiario';
$scripts_extra = ['js/beneficiarios.js'];
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="row justify-content-center"><div class="col-lg-6">
  <div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-1"><?= $editando ? 'Editar beneficiario N° ' . e(numero_beneficiario((int) $id)) : 'Registrar beneficiario' ?></h1>
    <?php if (!$editando): ?><p class="text-muted small">El número del registro se asigna automáticamente.</p><?php endif; ?>
    <?php if ($errores_form): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores_form as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form action="beneficiario_guardar.php" method="POST" id="formBeneficiario" novalidate>
      <?= csrf_campo() ?>
      <?php if ($editando): ?><input type="hidden" name="id_beneficiario" value="<?= (int) $id ?>"><?php endif; ?>
      <input type="hidden" name="volver" value="<?= e($volver) ?>">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="nombres">Nombres</label>
          <input type="text" class="form-control" id="nombres" name="nombres" maxlength="60" value="<?= e($b['nombres']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="apellidos">Apellidos</label>
          <input type="text" class="form-control" id="apellidos" name="apellidos" maxlength="60" value="<?= e($b['apellidos']) ?>" required>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5 mb-3">
          <label class="form-label" for="tipo_documento">Tipo de documento</label>
          <select class="form-select" id="tipo_documento" name="tipo_documento" required>
            <?php foreach (TIPOS_DOCUMENTO as $clave => $texto): ?>
              <option value="<?= e($clave) ?>" <?= ($b['tipo_documento'] ?? 'V') === $clave ? 'selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-7 mb-3">
          <label class="form-label" for="cedula">Número de documento</label>
          <input type="text" class="form-control" id="cedula" name="cedula" maxlength="15" value="<?= e($b['cedula']) ?>" autocomplete="off" required>
          <div class="form-text" id="ayudaDocumento">Solo números, de 6 a 10 dígitos.</div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="telefono">Teléfono (opcional)</label>
        <input type="text" inputmode="tel" class="form-control" id="telefono" name="telefono" maxlength="20" placeholder="Ej.: 0412-1234567" value="<?= e($b['telefono'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label d-block">Condición</label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="tipo_beneficiario" id="tipoTrabajador" value="Trabajador" <?= $b['tipo_beneficiario'] === 'Trabajador' ? 'checked' : '' ?>>
          <label class="form-check-label" for="tipoTrabajador">Trabajador</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="tipo_beneficiario" id="tipoAfiliado" value="Afiliado" <?= $b['tipo_beneficiario'] === 'Afiliado' ? 'checked' : '' ?>>
          <label class="form-check-label" for="tipoAfiliado">Afiliado</label>
        </div>
      </div>
      <?php if ($editando && es_admin()): ?>
      <div class="mb-3">
        <label class="form-label" for="status">Estatus</label>
        <select class="form-select" id="status" name="status">
          <option value="Activo" <?= $b['status'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
          <option value="Inactivo" <?= $b['status'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-institucional"><?= $editando ? 'Actualizar' : 'Guardar' ?></button>
        <a href="<?= $volver === 'orden' ? 'orden_form.php' : 'beneficiarios.php' ?>" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div></div>
</div></div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
