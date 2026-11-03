<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('usuarios', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: usuarios.php'); exit(); }
verificar_csrf();

$id       = $_POST['id_usuario'] ?? '';
$editando = $id !== '';
if ($editando && !ctype_digit((string) $id)) { flash('danger', 'Identificador no válido.'); header('Location: usuarios.php'); exit(); }
$id = $editando ? (int) $id : 0;

$volver_form = 'usuario_form.php' . ($editando ? "?id=$id" : '');
[$errores, $d] = validar_usuario($conexion, $_POST, $id);

$status = $_POST['status'] ?? 'Activo';
if (!in_array($status, ['Activo', 'Inactivo'], true)) $status = 'Activo';

if ($editando) {
    $s = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    $s->execute([$id]);
    if (!$s->fetch()) { flash('danger', 'El usuario no existe.'); header('Location: usuarios.php'); exit(); }
    if ($id === id_usuario_actual()) { // evita que el administrador se bloquee a sí mismo
        $d['rol'] = ROL_ADMIN; $status = 'Activo';
    }
}

if (!$editando) {
    $total = (int) $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($total >= MAX_USUARIOS) {
        $errores[] = 'Solo se permiten ' . MAX_USUARIOS . ' usuarios: el administrador y el encargado. Edite uno de los existentes.';
    }
}

if ($errores) {
    $_SESSION['errores_form'] = $errores;
    $_SESSION['datos_form']   = array_diff_key($_POST, ['clave' => 1]);
    header("Location: $volver_form");
    exit();
}

try {
    if ($editando) {
        $sql = "UPDATE usuarios SET cedula=?, nombre_usuario=?, nombre_completo=?, correo=?, rol=?, status=?,
                    fecha_modificacion=NOW(), usuario_modificacion=?" . ($d['clave'] !== '' ? ", clave=?" : "") . " WHERE id_usuario=?";
        $p = [$d['cedula'], $d['nombre_usuario'], $d['nombre_completo'], $d['correo'], $d['rol'], $status, id_usuario_actual()];
        if ($d['clave'] !== '') $p[] = password_hash($d['clave'], PASSWORD_DEFAULT);
        $p[] = $id;
        $conexion->prepare($sql)->execute($p);
        registrar_evento($conexion, 'EDITAR_USUARIO', 'usuarios', $id,
            "Usuario {$d['nombre_usuario']} (rol: {$d['rol']}, estatus: $status)" . ($d['clave'] !== '' ? ' — contraseña cambiada' : ''));
        flash('success', 'Usuario actualizado correctamente.');
    } else {
        $conexion->prepare(
            "INSERT INTO usuarios (cedula, nombre_usuario, nombre_completo, correo, clave, rol, usuario_registro) VALUES (?,?,?,?,?,?,?)"
        )->execute([$d['cedula'], $d['nombre_usuario'], $d['nombre_completo'], $d['correo'],
                    password_hash($d['clave'], PASSWORD_DEFAULT), $d['rol'], id_usuario_actual()]);
        registrar_evento($conexion, 'CREAR_USUARIO', 'usuarios', (int) $conexion->lastInsertId(), "Usuario {$d['nombre_usuario']} (rol: {$d['rol']})");
        flash('success', 'Usuario registrado correctamente.');
    }
} catch (PDOException $e) {
    error_log('usuario_guardar.php: ' . $e->getMessage());
    flash('danger', 'No fue posible guardar el usuario. Verifique que la cédula, el usuario y el correo no estén repetidos.');
}

header('Location: usuarios.php');
exit();
