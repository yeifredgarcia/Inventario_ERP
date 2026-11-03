<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/auditoria.php';

iniciar_sesion_segura();
if (!empty($_SESSION['id_usuario'])) {
    registrar_evento($conexion, 'LOGOUT', 'usuarios', (int) $_SESSION['id_usuario'], 'Cierre de sesión');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: login.php?salio=1');
exit();
