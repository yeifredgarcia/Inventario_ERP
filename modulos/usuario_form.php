<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
requerir_login($conexion, '../');
requerir_permiso('usuarios', '../');

$id = $_GET['id'] ?? '';
$u  = ['cedula' => '', 'nombre_usuario' => '', 'nombre_completo' => '', 'correo' => '', 'rol' => ROL_ENCARGADO, 'status' => 'Activo'];
$editando = false;

if ($id !== '') {
    if (!ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: usuarios.php'); exit(); }
    $s = $conexion->prepare("SELECT id_usuario, cedula, nombre_usuario, nombre_completo, correo, rol, status FROM usuarios WHERE id_usuario = ?");
    $s->execute([(int) $id]);
    $u = $s->fetch();
    if (!$u) { flash('danger', 'El usuario no existe.'); header('Location: usuarios.php'); exit(); }
    $editando = true;
}
if (isset($_SESSION['datos_form'])) { $u = array_merge($u, $_SESSION['datos_form']); unset($_SESSION['datos_form']); }
$errores_form = $_SESSION['errores_form'] ?? [];
unset($_SESSION['errores_form']);
if (!$editando && (int) $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn() >= MAX_USUARIOS) {
    flash('warning', 'Ya existen los ' . MAX_USUARIOS . ' usuarios permitidos. Edite uno de ellos.');
    header('Location: usuarios.php');
    exit();
}
$es_propio = $editando && (int) $u['id_usuario'] === id_usuario_actual();

$base = '../';
$titulo_pagina = $editando ? 'Editar usuario' : 'Registrar usuario';
$scripts_extra = ['js/usuarios.js'];
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="row justify-content-center"><div class="col-lg-6">
  <div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-3"><?= e($titulo_pagina) ?></h1>
    <?php if ($errores_form): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores_form as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form action="usuario_guardar.php" method="POST" id="formUsuario" novalidate>
      <?= csrf_campo() ?>
      <?php if ($editando): ?><input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>"><?php endif; ?>

      <div class="mb-3">
        <label class="form-label" for="cedula">Cédula</label>
        <input type="text" inputmode="numeric" class="form-control" id="cedula" name="cedula" maxlength="10" value="<?= e($u['cedula']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="nombre_completo">Nombre completo</label>
        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" maxlength="100" value="<?= e($u['nombre_completo']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="nombre_usuario">Nombre de usuario</label>
        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" maxlength="30" value="<?= e($u['nombre_usuario']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="correo">Correo electrónico</label>
        <input type="email" class="form-control" id="correo" name="correo" maxlength="100" value="<?= e($u['correo']) ?>" required>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="rol">Rol</label>
          <select class="form-select" id="rol" name="rol" <?= $es_propio ? 'disabled' : '' ?> required>
            <?php foreach ([ROL_ADMIN, ROL_ENCARGADO] as $r): ?>
              <option value="<?= e($r) ?>" <?= $u['rol'] === $r ? 'selected' : '' ?>><?= e($r) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($es_propio): ?><input type="hidden" name="rol" value="<?= e($u['rol']) ?>"><?php endif; ?>
        </div>
        <?php if ($editando): ?>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="status">Estatus</label>
          <select class="form-select" id="status" name="status" <?= $es_propio ? 'disabled' : '' ?>>
            <option value="Activo" <?= $u['status'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $u['status'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
          </select>
          <?php if ($es_propio): ?><input type="hidden" name="status" value="Activo"><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="clave"><?= $editando ? 'Nueva contraseña (opcional)' : 'Contraseña' ?></label>
        <input type="password" class="form-control" id="clave" name="clave" maxlength="72" autocomplete="new-password" <?= $editando ? '' : 'required' ?>>
        <div class="form-text">Mínimo 8 caracteres, con letras y números.</div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-institucional"><?= $editando ? 'Actualizar' : 'Guardar' ?></button>
        <a href="usuarios.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div></div>
</div></div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
