<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once 'config/conexion.php';

// Conteo total para las tarjetas de arriba
$totalInsumos = $conexion->query("SELECT COUNT(*) FROM inventario_general")->fetchColumn();
$porVencer    = $conexion->query("SELECT COUNT(*) FROM inventario_general WHERE estado = 'Por Vencer'")->fetchColumn();
$vencidos     = $conexion->query("SELECT COUNT(*) FROM inventario_general WHERE estado = 'Vencido'")->fetchColumn();

// Traer las categorías registradas para generar los módulos por separado
$categorias   = $conexion->query("SELECT * FROM categorias ORDER BY nombre_categoria ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Inventario Médico</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { background-color: #f4f6f9; color: #333; }
    header { background: #1e293b; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
    header a { color: #f87171; text-decoration: none; font-weight: bold; }
    .container { padding: 30px; max-width: 1100px; margin: auto; }
    .cards { display: flex; gap: 20px; margin-bottom: 30px; }
    .card { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; }
    .card h3 { font-size: 2rem; color: #0284c7; }
    .card p { color: #64748b; margin-top: 5px; }
    .grid-modulos { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
    .card-modulo { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #0284c7; }
    .card-modulo h4 { color: #1e293b; font-size: 1.2rem; margin-bottom: 10px; }
    .btn-main { display: inline-block; background: #0284c7; color: white; padding: 10px 16px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 0.9rem; }
    .btn-main:hover { background: #0369a1; }
  </style>
</head>
<body>

  <header>
    <h2>Sistema de Inventario Médico</h2>
    <div>
      <span>Bienvenido, <strong><?= $_SESSION['nombre_completo'] ?? $_SESSION['usuario'] ?></strong></span> | 
      <a href="logout.php">Cerrar Sesión</a>
    </div>
  </header>

  <div class="container">
    <!-- Tarjetas de Conteo General (Exactas al diseño original) -->
    <div class="cards">
      <div class="card">
        <h3><?= $totalInsumos ?></h3>
        <p>Total Insumos</p>
      </div>
      <div class="card">
        <h3 style="color: #f59e0b;"><?= $porVencer ?></h3>
        <p>Por Vencer</p>
      </div>
      <div class="card">
        <h3 style="color: #ef4444;"><?= $vencidos ?></h3>
        <p>Vencidos</p>
      </div>
    </div>

    <!-- Secciones / Inventarios Separados -->
    <h3 style="color: #1e293b; margin-bottom: 10px;">Seleccione el Inventario a Gestionar:</h3>
    
    <div class="grid-modulos">
      <!-- Opción 1: Ver Todo -->
      <div class="card-modulo">
        <h4>Inventario General</h4>
        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 15px;">Ver todos los insumos juntos sin filtrar.</p>
        <a href="modulos/listar.php" class="btn-main">Ver Todo</a>
      </div>

      <!-- Opciones por Categoría Separada -->
      <?php foreach ($categorias as $cat): ?>
        <div class="card-modulo">
          <h4><?= htmlspecialchars($cat['nombre_categoria']) ?></h4>
          <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 15px;">Gestión exclusiva de esta área.</p>
          <a href="modulos/listar.php?cat=<?= $cat['id_categoria'] ?>" class="btn-main">Gestionar <?= htmlspecialchars($cat['nombre_categoria']) ?></a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>
</html>