document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formInsumo');
    if (!form) return;

    soloDigitos(document.getElementById('cantidad'));

    form.addEventListener('submit', function (e) {
        var errores = [];
        var nombre = document.getElementById('nombre_producto').value.replace(/\s+/g, ' ').trim();
        var categoria = document.getElementById('id_categoria').value;
        var presentacion = document.getElementById('id_presentacion').value;
        var cantidad = document.getElementById('cantidad').value.trim();
        var campoFecha = document.getElementById('fecha_vencimiento');
        var fecha = campoFecha.value;

        if (nombre.length < 3 || nombre.length > 150) {
            errores.push('El nombre debe tener entre 3 y 150 caracteres.');
        } else if (!/^[\p{L}\p{N}\s.,%\/()\-+ºª°#']+$/u.test(nombre)) {
            errores.push('El nombre contiene caracteres no permitidos.');
        }
        if (categoria === '') errores.push('Seleccione una categoría.');
        if (presentacion === '') errores.push('Seleccione una presentación.');

        if (!/^\d+$/.test(cantidad)) {
            errores.push('La cantidad debe ser un número entero (solo dígitos).');
        } else if (parseInt(cantidad, 10) > 1000000) {
            errores.push('La cantidad no puede superar 1.000.000.');
        }

        if (fecha !== '') {
            if (!fechaValida(fecha)) {
                errores.push('La fecha de vencimiento no es válida.');
            } else if (fecha < campoFecha.min || fecha > campoFecha.max) {
                errores.push('La fecha de vencimiento está fuera del rango permitido.');
            }
        }

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
