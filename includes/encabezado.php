<?php
/** Encabezado común: Bootstrap + barra lateral izquierda. Requiere $base y $titulo_pagina. */
$base          = $base ?? '';
$titulo_pagina = $titulo_pagina ?? 'Inventario Médico';
$flash         = obtener_flash();

// Opción del menú que, si el rol no tiene permiso, aparece bloqueada y avisa al hacer clic
function item_menu_permiso(string $clave, string $activa, string $url, string $texto, string $accion): string
{
    if (puede($accion)) {
        return item_menu($clave, $activa, $url, $texto);
    }
    return '<li class="nav-item"><a class="nav-link bloqueado" href="#" data-bloqueado="' . e(mensaje_permiso($accion)) . '">' . $texto . ' &#128274;</a></li>';
}

// Opción activa del menú según la página actual
$script = basename($_SERVER['SCRIPT_NAME']);
$activa = 'inicio';
if (in_array($script, ['listar.php', 'agregar.php', 'editar.php'], true)) $activa = 'inventario';
elseif ($script === 'reporte.php') $activa = 'reportes';
elseif (in_array($script, ['beneficiarios.php', 'beneficiario_form.php'], true)) $activa = 'beneficiarios';
elseif (in_array($script, ['ordenes.php', 'orden_form.php', 'orden_ver.php'], true)) $activa = 'ordenes';
elseif (in_array($script, ['movimientos.php', 'movimiento_form.php'], true)) $activa = 'movimientos';
elseif ($script === 'accesos.php') $activa = 'accesos';
elseif ($script === 'auditoria.php') $activa = 'auditoria';
elseif (in_array($script, ['usuarios.php', 'usuario_form.php'], true)) $activa = 'usuarios';

function item_menu(string $clave, string $activa, string $url, string $texto): string
{
    return '<li class="nav-item"><a class="nav-link' . ($clave === $activa ? ' active' : '') . '" href="' . $url . '">' . $texto . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo_pagina) ?> - Inventario Médico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= $base ?>css/estilos.css" rel="stylesheet">
</head>
<body>
<!-- Barra superior solo en pantallas pequeñas -->
<nav class="navbar barra-institucional d-lg-none no-imprimir">
  <div class="container-fluid">
    <span class="navbar-brand text-white fw-bold">Inventario Médico</span>
    <button class="btn btn-outline-light btn-sm" type="button" id="botonMenu" aria-controls="menuLateral" aria-expanded="false">Menú</button>
  </div>
</nav>

<div class="contenedor-app">
  <aside class="barra-lateral no-imprimir" id="menuLateral">
    <div class="marca-lateral">
      <div class="fw-bold fs-5">Inventario Médico</div>
      <div class="small opacity-75">PST Trayecto II</div>
    </div>
    <ul class="nav flex-column menu-lateral">
      <?= item_menu('inicio', $activa, $base . 'index.php', 'Inicio') ?>
      <?= item_menu('inventario', $activa, $base . 'modulos/listar.php', 'Inventario general') ?>
      <?php if (puede('movimientos')) echo item_menu('movimientos', $activa, $base . 'modulos/movimientos.php', 'Entradas y salidas'); ?>
      <?php if (puede('beneficiarios')) echo item_menu('beneficiarios', $activa, $base . 'modulos/beneficiarios.php', 'Beneficiarios'); ?>
      <?php if (puede('ordenes')) echo item_menu('ordenes', $activa, $base . 'modulos/ordenes.php', 'Órdenes de entrega'); ?>
      <?php if (puede('reportes')) echo item_menu('reportes', $activa, $base . 'modulos/reporte.php', 'Reportes'); ?>
      <?= item_menu_permiso('accesos', $activa, $base . 'modulos/accesos.php', 'Quién entra y sale', 'auditoria') ?>
      <?= item_menu_permiso('auditoria', $activa, $base . 'modulos/auditoria.php', 'Auditoría', 'auditoria') ?>
      <?= item_menu_permiso('usuarios', $activa, $base . 'modulos/usuarios.php', 'Usuarios', 'usuarios') ?>
    </ul>
    <div class="usuario-lateral">
      <div class="fw-semibold"><?= e($_SESSION['nombre_completo'] ?? $_SESSION['usuario']) ?></div>
      <div class="small opacity-75 mb-2"><?= e($_SESSION['rol']) ?></div>
      <a class="btn btn-outline-light btn-sm w-100" href="<?= $base ?>logout.php">Cerrar sesión</a>
    </div>
  </aside>

  <div class="contenido-app">
    <main class="p-3 p-lg-4">
<?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
<?php endif; ?>
