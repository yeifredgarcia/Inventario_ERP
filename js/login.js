document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var errores = [];
        var usuario = document.getElementById('usuario').value.trim();
        var clave = document.getElementById('clave').value;

        if (usuario === '') {
            errores.push('Ingrese su nombre de usuario.');
        } else if (usuario.length < 3 || usuario.length > 30) {
            errores.push('El usuario debe tener entre 3 y 30 caracteres.');
        }
        if (clave === '') {
            errores.push('Ingrese su contraseña.');
        }

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
