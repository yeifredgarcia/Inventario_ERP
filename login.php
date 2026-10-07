<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Inventario Médico</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); width: 100%; max-width: 360px; }
        .login-container h2 { text-align: center; color: #1e293b; margin-bottom: 5px; }
        .login-container p { text-align: center; color: #64748b; font-size: 0.85rem; margin-bottom: 20px; }
        .error-msg { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.85rem; text-align: center; border: 1px solid #fca5a5; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; color: #334155; font-size: 0.9rem; font-weight: bold; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; outline: none; }
        .btn-login { width: 100%; padding: 10px; background: #0284c7; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-login:hover { background: #0369a1; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Inventario Médico</h2>
        <p>Ingrese sus credenciales de acceso</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">Usuario o contraseña incorrectos.</div>
        <?php endif; ?>

        <form action="validar_login.php" method="POST" id="loginForm">
            <div class="input-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            
            <div class="input-group">
                <label for="clave">Contraseña:</label>
                <input type="password" id="clave" name="clave" required>
            </div>

            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>

    <script src="js/login.js"></script>
</body>
</html>