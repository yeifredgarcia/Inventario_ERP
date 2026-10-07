<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/conexion.php';

$id = $_GET['id'];
$stmt = $conexion->prepare("SELECT * FROM inventario_general WHERE id_producto = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

$categorias = $conexion->query("SELECT * FROM categorias")->fetchAll();
$presentaciones = $conexion->query("SELECT * FROM presentaciones")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Producto</title>
  <style>
    body { font-family: sans-serif; background: #f4f6f9; padding: 20px; }
    form { background: white; padding: 20px; max-width: 400px; margin: auto; border-radius: 8px; }
    div { margin-bottom: 12px; }
    label { display: block; font-weight: bold; margin-bottom: 5px; }
    input, select { width: 100%; padding: 8px; box-sizing: border-box; }
    button { background: #f59e0b; color: white; padding: 10px; border: none; width: 100%; cursor: pointer; }
  </style>
</head>
<body>

<form action="actualizar.php" method="POST" id="formInsumo">
  <h2>Editar Producto</h2>
  <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">

  <div>
    <label>Nombre:</label>
    <input type="text" id="nombre_producto" name="nombre_producto" value="<?= htmlspecialchars($producto['nombre_producto']) ?>" required>
  </div>

  <div>
    <label>Categoría:</label>
    <select id="id_categoria" name="id_categoria" required>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= $c['id_categoria'] ?>" <?= $c['id_categoria'] == $producto['id_categoria'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['nombre_categoria']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <label>Presentación:</label>
    <select id="id_presentacion" name="id_presentacion" required>
      <?php foreach ($presentaciones as $p): ?>
        <option value="<?= $p['id_presentacion'] ?>" <?= $p['id_presentacion'] == $producto['id_presentacion'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['nombre_presentacion']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <label>Cantidad:</label>
    <input type="number" id="cantidad" name="cantidad" value="<?= $producto['cantidad'] ?>" required>
  </div>

  <div>
    <label>Estado:</label>
    <select name="estado" required>
      <option value="Disponible" <?= $producto['estado'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
      <option value="Por Vencer" <?= $producto['estado'] == 'Por Vencer' ? 'selected' : '' ?>>Por Vencer</option>
      <option value="Vencido" <?= $producto['estado'] == 'Vencido' ? 'selected' : '' ?>>Vencido</option>
    </select>
  </div>

  <button type="submit">Actualizar</button>
  <br><br>
  <a href="listar.php">Cancelar</a>
</form>

<script src="../js/inventario.js"></script>
</body>
</html>