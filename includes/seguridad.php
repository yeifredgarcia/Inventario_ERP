<?php
/**
 * Funciones de seguridad: sesiones, roles, CSRF y mensajes.
 */
date_default_timezone_set('America/Caracas');
require_once __DIR__ . '/auditoria.php';

const TIEMPO_INACTIVIDAD = 1800; // 30 minutos
// Categorías que muestra el sistema (ids de la tabla categorias). Hoy solo 1 = Medicamentos.
// Para mostrar otra (por ejemplo 3 = Blíster) agregue su id: const CATEGORIAS_VISIBLES = [1, 3];
const CATEGORIAS_VISIBLES = [1];
// Récipe médico que se adjunta a cada orden de entrega
const RECIPE_OBLIGATORIO = true;                 // false = el récipe es opcional
const RECIPE_MAX_BYTES   = 5 * 1024 * 1024;      // 5 MB
const RECIPE_DIR         = __DIR__ . '/../uploads/recipes';
const RECIPE_EXTENSIONES = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
const MAX_USUARIOS = 2; // administrador principal y encargado del inventario
const ROL_ADMIN   = 'Administrador';
const ROL_ENCARGADO = 'Encargado';

// Qué puede hacer cada rol
const PERMISOS = [
    ROL_ADMIN   => ['consultar', 'crear', 'editar', 'estatus', 'movimientos', 'beneficiarios', 'ordenes', 'reportes', 'auditoria', 'usuarios'],
    ROL_ENCARGADO => ['consultar', 'crear', 'editar', 'movimientos', 'beneficiarios', 'ordenes', 'reportes'],
];

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

/** Exige sesión activa y vigente. $base es la ruta relativa a la raíz del sistema ('' o '../'). */
function requerir_login(PDO $conexion, string $base = ''): void
{
    iniciar_sesion_segura();

    if (empty($_SESSION['id_usuario'])) {
        header("Location: {$base}login.php");
        exit();
    }

    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > TIEMPO_INACTIVIDAD) {
        registrar_evento($conexion, 'SESION_EXPIRADA', 'usuarios', (int) $_SESSION['id_usuario'], 'Sesión cerrada por inactividad');
        session_unset();
        session_destroy();
        header("Location: {$base}login.php?expirada=1");
        exit();
    }
    $_SESSION['ultimo_acceso'] = time();

    // Si el administrador inactivó al usuario mientras tenía sesión abierta, se cierra.
    $stmt = $conexion->prepare("SELECT rol, status FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $u = $stmt->fetch();
    if (!$u || $u['status'] !== 'Activo') {
        session_unset();
        session_destroy();
        header("Location: {$base}login.php?error=3");
        exit();
    }
    $_SESSION['rol'] = $u['rol'];
}

function puede(string $accion): bool
{
    $rol = $_SESSION['rol'] ?? '';
    return in_array($accion, PERMISOS[$rol] ?? [], true);
}

/** Mensaje que se muestra cuando un rol intenta algo que no le corresponde. */
function mensaje_permiso(string $accion): string
{
    $mensajes = [
        'estatus'   => 'Acción no permitida: solo el Administrador puede dar de baja o reactivar insumos.',
        'auditoria' => 'Acción no permitida: solo el Administrador puede ver los accesos al sistema y la auditoría.',
        'usuarios'  => 'Acción no permitida: solo el Administrador puede registrar o editar usuarios.',
    ];
    return $mensajes[$accion] ?? 'Acción no permitida para su rol (' . ($_SESSION['rol'] ?? 'sin rol') . ').';
}

function requerir_permiso(string $accion, string $base = ''): void
{
    if (!puede($accion)) {
        flash('warning', mensaje_permiso($accion));
        header("Location: {$base}index.php");
        exit();
    }
}

function es_admin(): bool
{
    return ($_SESSION['rol'] ?? '') === ROL_ADMIN;
}

function id_usuario_actual(): ?int
{
    return isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
}

/** Escapa texto para mostrarlo en HTML (previene XSS). */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verificar_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        die('La solicitud no es válida o la sesión expiró. Regrese a la página anterior e intente de nuevo.');
    }
}

// ---------- Mensajes ----------
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtener_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Lista de ids visibles para usar dentro de un IN (...) de SQL. Solo contiene enteros fijos del código. */
function categorias_sql(): string
{
    return implode(',', array_map('intval', CATEGORIAS_VISIBLES));
}

function categoria_visible(int $id): bool
{
    return in_array($id, CATEGORIAS_VISIBLES, true);
}
