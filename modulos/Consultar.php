<?php
require_once '../config/conexion.php';

// Consulta con JOIN para traer nombres de categoría y presentación
$sql = "SELECT i.*, c.nombre_categoria, p.nombre_presentacion 
        FROM inventario_general i
        INNER JOIN categorias c ON i.id_categoria = c.id_categoria
        INNER JOIN presentaciones p ON i.id_presentacion = p.id_presentacion
        ORDER BY i.id_producto DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Inventario - Trayecto II</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #0284c7; color: white; }
        .btn { padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; }
        .btn-add { background: #16a34a; }
        .btn-edit { background: #eab308; }
        .btn-delete { background: #dc2626; }
        .btn-back { background: #4b5563; }
    </style>
</head>
<body>

<div class="container">
    <h2>Gestión de Inventario Médico</h2>
    <br>
    <a href="agregar.php" class="btn btn-add">+ Registrar Nuevo Insumo</a>
    <a href="../index.php" class="btn btn-back">Volver al Inicio</a>
    <br><br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Presentación</th>
                <th>Cantidad</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= $p['id_producto'] ?></td>
                <td><?= htmlspecialchars($p['nombre_producto']) ?></td>
                <td><?= htmlspecialchars($p['nombre_categoria']) ?></td>
                <td><?= htmlspecialchars($p['nombre_presentacion']) ?></td>
                <td><?= $p['cantidad'] ?></td>
                <td><?= $p['fecha_vencimiento'] ?? 'N/A' ?></td>
                <td><?= $p['estado'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-edit">Editar</a>
                    <a href="eliminar.php?id=<?= $p['id_producto'] ?>" class="btn btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este insumo?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
