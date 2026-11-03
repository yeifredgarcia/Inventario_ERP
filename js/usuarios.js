document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formUsuario');
    if (!form) return;

    soloDigitos(document.getElementById('cedula'));

    form.addEventListener('submit', function (e) {
        var errores = [];
        var cedula = document.getElementById('cedula').value.trim();
        var nombre = document.getElementById('nombre_completo').value.replace(/\s+/g, ' ').trim();
        var usuario = document.getElementById('nombre_usuario').value.trim();
        var correo = document.getElementById('correo').value.trim();
        var clave = document.getElementById('clave');
        var obligatoria = clave.required;

        if (!/^\d{6,10}$/.test(cedula)) errores.push('La cédula debe contener solo números (entre 6 y 10 dígitos).');
        if (!/^[\p{L}\s.'-]{3,100}$/u.test(nombre)) errores.push('El nombre completo debe tener entre 3 y 100 caracteres y solo letras.');
        if (!/^[A-Za-z0-9_.]{3,30}$/.test(usuario)) errores.push('El usuario debe tener entre 3 y 30 caracteres (letras, números, punto o guion bajo).');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo) || correo.length > 100) errores.push('El correo electrónico no es válido.');

        if (obligatoria || clave.value !== '') {
            var v = clave.value;
            if (v.length < 8 || v.length > 72 || !/[A-Za-z]/.test(v) || !/\d/.test(v)) {
                errores.push('La contraseña debe tener entre 8 y 72 caracteres e incluir letras y números.');
            }
        }

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
