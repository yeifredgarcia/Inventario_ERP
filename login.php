

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css"
</head>
<body>
    <img class="wave" src=>
    <div class="container"> 
       <div class="img">
       </div>
       <div class="login-content">
         <form method="post" action="">
            <img src="img/avatar.svg">
            <h2 class="title">BIENVENIDO</h2>

            <div class="input-div one">
                <div class="i">
                    <i class="fas fa-user"></i>
                </div>
            <div class="div">
                <h5>Usuario</h5>
                <input id="usuario" type="text" class="input" name="usuario">
            </div>
        </div>
        <div class="input-div pass">
            <div class="i">
                <i class="fas fa-lock"></i>
            </div>
        <div class="div">
            <h5>Contraseña</h5>
            <input type="Password" id="input" class="input" name="password">
        </div>
    </div>
    <div class="view">
        <div class="fas fa-eye verPassword" onclick="vista()" id="verPassword"></div>
    </div>

    <div class="text-center">
        <a class="font-italic isai5" href="">Olvide mi contraseña</a>
    </div>
    <input name="btningresar" class="btn" type="submit" value="INICIAR SESION">
         </form>
       </div>
    </div>
    <script src="js/fontawesome.js"></script> 
</body>
</html>

