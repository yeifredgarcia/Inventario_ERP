<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenidos</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { display: flex; background-color: #f4f6f9; color: #333; min-height: 100vh; }

    .sidebar { width: 240px; height: 100vh; background: #1e293b; color: #fff; padding: 20px; position: fixed; }
    .sidebar h2 { font-size: 1.2rem; margin-bottom: 40px; text-align: center; color: #e1e7e9; }
    .sidebar ul { list-style: none; }
    .sidebar li { margin-bottom: 15px; }
    .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 10px; border-radius: 6px; transition: 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: #334155; color: #fff; }

    
    .main-content {
      margin-left: 240px;
      width: calc(100% - 240px);
      padding: 15px 30px 30px 30px; 
    }

    .actions-grid {
      display: flex;
      gap: 40px; 
      width: 100%;
      margin-top: 0px; 
    }

    /* Tarjetas individuales */
    .card-option {
      flex: 1;
      background: #fff;
      padding:  25px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s, box-shadow 0.2s;
      display: flex;
      flex-direction: column;
      align-items: center;    
      justify-content: center; 
      text-align: center;
    }

    .card-option:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-option h2 {
      font-size: 1.3rem;
      color: #0f172a;
      margin-bottom: 15px;
    }

    .card-option button {
      background-color: #3b82f6;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.2s;
    }

    .card-option button:hover {
      background-color: #2563eb;
    }
  </style>
</head>
<body>

  <aside class="sidebar">
    <h2>Inventario</h2>
    <ul>
      <li><a href="#" class="active">Inicio</a></li>
      <li><a href="#">Control</a></li>
      <li><a href="#">Insumos</a></li>
      <li><a href="#">Medicamentos</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <div class="actions-grid">
      
      <!-- Bloque 1: Insumos -->
      <div class="card-option">
        <h2>Insumos</h2>
        <form method="post" action="insumos.php">
          <button type="submit">Gestionar Insumos</button>
        </form>
      </div>

      <!-- Bloque 2: Medicamentos -->
      <div class="card-option">
        <h2>Medicamentos</h2>
        <form method="post" action="medicamentos.php">
          <button type="submit">Gestionar Medicamentos</button>
        </form>
      </div>

    </div>
  </main>

</body>
</html>