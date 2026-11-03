<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
requerir_login($conexion, '../');
requerir_permiso('usuarios', '../');

$usuarios = $conexion->query("SELECT id_usuario, cedula, nombre_usuario, nombre_completo, correo, rol, status, fecha_registro FROM usuarios ORDER BY nombre_completo")->fetchAll();

$base = '../';
$titulo_pagina = 'Usuarios';
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Usuarios del sistema</h1>
  <?php if (count($usuarios) < MAX_USUARIOS): ?>
    <a href="usuario_form.php" class="btn btn-success btn-sm">+ Registrar usuario</a>
  <?php else: ?>
    <span class="text-muted small">Límite de <?= MAX_USUARIOS ?> usuarios alcanzado: el administrador y el encargado.</span>
  <?php endif; ?>
</div>
<div class="card shadow-sm"><div class="card-body">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light"><tr><th>Cédula</th><th>Usuario</th><th>Nombre completo</th><th>Correo</th><th>Rol</th><th>Estatus</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($usuarios as $u): ?>
        <tr class="<?= $u['status'] === 'Inactivo' ? 'table-secondary' : '' ?>">
          <td><?= e($u['cedula']) ?></td>
          <td><?= e($u['nombre_usuario']) ?></td>
          <td><?= e($u['nombre_completo']) ?></td>
          <td><?= e($u['correo']) ?></td>
          <td><?= e($u['rol']) ?></td>
          <td><span class="badge bg-<?= $u['status'] === 'Activo' ? 'success' : 'secondary' ?>"><?= e($u['status']) ?></span></td>
          <td><a href="usuario_form.php?id=<?= (int) $u['id_usuario'] ?>" class="btn btn-warning btn-sm">Editar</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
