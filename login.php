<?php
require_once __DIR__ . '/includes/seguridad.php';
iniciar_sesion_segura();

if (!empty($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

$mensajes = [
    '1' => 'Usuario o contraseña incorrectos.',
    '2' => 'Demasiados intentos fallidos. Espere 15 minutos e intente de nuevo.',
    '3' => 'Su usuario está inactivo. Comuníquese con el administrador.',
];
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión - Inventario Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="fondo-login d-flex align-items-center justify-content-center min-vh-100">
  <div class="card shadow-sm tarjeta-login">
    <div class="card-body p-4">
      <h1 class="h4 text-center titulo-institucional">Inventario Médico</h1>
      <p class="text-center text-muted small mb-4">Ingrese sus credenciales de acceso</p>

      <?php if (isset($mensajes[$error])): ?>
        <div class="alert alert-danger py-2 small"><?= e($mensajes[$error]) ?></div>
      <?php elseif (isset($_GET['expirada'])): ?>
        <div class="alert alert-warning py-2 small">Su sesión expiró por inactividad. Ingrese nuevamente.</div>
      <?php elseif (isset($_GET['salio'])): ?>
        <div class="alert alert-success py-2 small">Sesión cerrada correctamente.</div>
      <?php endif; ?>

      <form action="validar_login.php" method="POST" id="loginForm" novalidate>
        <?= csrf_campo() ?>
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" maxlength="30" autocomplete="username" required>
        </div>
        <div class="mb-3">
          <label for="clave" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="clave" name="clave" maxlength="72" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-institucional w-100">Ingresar</button>
      </form>
    </div>
  </div>
  <script src="js/main.js"></script>
  <script src="js/login.js"></script>
</body>
</html>
