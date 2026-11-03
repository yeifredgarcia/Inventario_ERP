<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/auditoria.php';

iniciar_sesion_segura();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}
verificar_csrf();

$nombre_usuario = trim($_POST['usuario'] ?? '');
$clave          = $_POST['clave'] ?? '';

if ($nombre_usuario === '' || $clave === '' || strlen($nombre_usuario) > 30 || strlen($clave) > 72) {
    header('Location: login.php?error=1');
    exit();
}

// Bloqueo temporal: 5 intentos fallidos en 15 minutos para el mismo usuario
$stmt = $conexion->prepare(
    "SELECT COUNT(*) FROM auditoria
     WHERE accion = 'LOGIN_FALLIDO' AND nombre_usuario = ? AND fecha_registro > (NOW() - INTERVAL 15 MINUTE)"
);
$stmt->execute([$nombre_usuario]);
if ((int) $stmt->fetchColumn() >= 5) {
    header('Location: login.php?error=2');
    exit();
}

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
$stmt->execute([$nombre_usuario]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($clave, $usuario['clave'])) {
    if ($usuario['status'] !== 'Activo') {
        registrar_evento($conexion, 'LOGIN_INACTIVO', 'usuarios', (int) $usuario['id_usuario'], 'Intento de ingreso con usuario inactivo', $nombre_usuario);
        header('Location: login.php?error=3');
        exit();
    }

    // Actualiza el hash si el algoritmo por defecto de PHP cambió
    if (password_needs_rehash($usuario['clave'], PASSWORD_DEFAULT)) {
        $conexion->prepare("UPDATE usuarios SET clave = ? WHERE id_usuario = ?")
                 ->execute([password_hash($clave, PASSWORD_DEFAULT), $usuario['id_usuario']]);
    }

    session_regenerate_id(true); // evita fijación de sesión
    $_SESSION['id_usuario']      = (int) $usuario['id_usuario'];
    $_SESSION['usuario']         = $usuario['nombre_usuario'];
    $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
    $_SESSION['rol']             = $usuario['rol'];
    $_SESSION['ultimo_acceso']   = time();

    registrar_evento($conexion, 'LOGIN', 'usuarios', (int) $usuario['id_usuario'], 'Inicio de sesión');
    header('Location: index.php');
    exit();
}

registrar_evento($conexion, 'LOGIN_FALLIDO', 'usuarios', $usuario ? (int) $usuario['id_usuario'] : null, 'Credenciales incorrectas', $nombre_usuario);
header('Location: login.php?error=1');
exit();
