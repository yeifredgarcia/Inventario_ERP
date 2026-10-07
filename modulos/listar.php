<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/conexion.php';

// Obtener parámetros
$cat_id = isset($_GET['cat']) ? intval($_GET['cat']) : null;
$filtro_estado = $_GET['filtro'] ?? 'todos';

// 1. Calcular Totales según la categoría seleccionada
$where_cat = "";
$params_count = [];

if ($cat_id) {
    $where_cat = " WHERE id_categoria = ?";
    $params_count[] = $cat_id;
}

// Total de insumos en la sección actual
$stmtTotal = $conexion->prepare("SELECT COUNT(*) FROM inventario_general $where_cat");
$stmtTotal->execute($params_count);
$totalInsumos = $stmtTotal->fetchColumn();

// Total Por Vencer
$sqlPorVencer = "SELECT COUNT(*) FROM inventario_general WHERE estado = 'Por Vencer'";
if ($cat_id) $sqlPorVencer .= " AND id_categoria = ?";
$stmtPV = $conexion->prepare($sqlPorVencer);
$stmtPV->execute($params_count);
$porVencer = $stmtPV->fetchColumn();

// Total Vencidos
$sqlVencidos = "SELECT COUNT(*) FROM inventario_general WHERE estado = 'Vencido'";
if ($cat_id) $sqlVencidos .= " AND id_categoria = ?";
$stmtV = $conexion->prepare($sqlVencidos);
$stmtV->execute($params_count);
$vencidos = $stmtV->fetchColumn();

// Nombre del módulo actual
$titulo_modulo = "Inventario General";
if ($cat_id) {
    $stmtCat = $conexion->prepare("SELECT nombre_categoria FROM categorias WHERE id_categoria = ?");
    $stmtCat->execute([$cat_id]);
    $catData = $stmtCat->fetch();
    if ($catData) {
        $titulo_modulo = "Inventario de " . $catData['nombre_categoria'];
    }
}

// 2. Consulta filtrada estrictamente por la categoría elegida
$sql = "SELECT i.*, c.nombre_categoria, p.nombre_presentacion 
        FROM inventario_general i
        INNER JOIN categorias c ON i.id_categoria = c.id_categoria
        INNER JOIN presentaciones p ON i.id_presentacion = p.id_presentacion";

$condiciones = [];
$params_query = [];

if ($cat_id) {
    $condiciones[] = "i.id_categoria = ?";
    $params_query[] = $cat_id;
}

if ($filtro_estado === 'por_vencer') {
    $condiciones[] = "i.estado = 'Por Vencer'";
} elseif ($filtro_estado === 'vencidos') {
    $condiciones[] = "i.estado = 'Vencido'";
}

if (count($condiciones) > 0) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY i.id_producto DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params_query);
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($titulo_modulo) ?> - Inventario Médico</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { background-color: #f4f6f9; color: #333; }
    header { background: #1e293b; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
    header a { color: #38bdf8; text-decoration: none; font-weight: bold; }
    .container { padding: 30px; max-width: 1100px; margin: auto; }
    .cards { display: flex; gap: 20px; margin-bottom: 25px; }
    .card { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; }
    .card h3 { font-size: 2rem; color: #0284c7; }
    .card p { color: #64748b; margin-top: 5px; }
    .box-crud { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn { padding: 8px 14px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.85rem; color: white; display: inline-block; }
    .btn-back { background: #64748b; }
    .btn-add { background: #16a34a; }
    .btn-edit { background: #f59e0b; padding: 5px 10px; }
    .btn-delete { background: #ef4444; padding: 5px 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    th { background: #f8fafc; color: #475569; font-size: 0.85rem; text-transform: uppercase; }
  </style>
</head>
<body>

  <header>
    <h2><?= htmlspecialchars($titulo_modulo) ?></h2>
    <div>
      <a href="../index.php">⬅ Volver al Inicio</a>
    </div>
  </header>

  <div class="container">
    <!-- Cifras exclusivas de la categoría activa -->
    <div class="cards">
      <div class="card">
        <h3><?= $totalInsumos ?></h3>
        <p>Total en esta Categoría</p>
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

    <!-- Tabla filtrada -->
    <div class="box-crud">
      <div class="header-actions">
        <h3>Lista de Registros</h3>
        <a href="agregar.php<?= $cat_id ? '?cat='.$cat_id : '' ?>" class="btn btn-add">➕ Agregar Nuevo Insumo</a>
      </div>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Presentación</th>
            <th>Cantidad</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $p): ?>
            <tr>
              <td><?= $p['id_producto'] ?></td>
              <td><strong><?= htmlspecialchars($p['nombre_producto']) ?></strong></td>
              <td><?= htmlspecialchars($p['nombre_categoria']) ?></td>
              <td><?= htmlspecialchars($p['nombre_presentacion']) ?></td>
              <td><?= $p['cantidad'] ?></td>
              <td><?= $p['estado'] ?></td>
              <td>
                <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-edit">Editar</a>
                <a href="eliminar.php?id=<?= $p['id_producto'] ?>" class="btn btn-delete" onclick="confirmarEliminacion(event, this.href)">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">
                No hay insumos registrados en esta categoría.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="../js/main.js"></script>
</body>
</html>