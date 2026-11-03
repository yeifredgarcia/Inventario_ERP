document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formMovimiento');
    if (!form) return;

    soloDigitos(document.getElementById('cantidad'));

    // Filtro de la lista de insumos
    var buscar = document.getElementById('buscarProducto');
    var lista = document.getElementById('id_producto');
    buscar.addEventListener('input', function () {
        var q = buscar.value.toLowerCase();
        Array.prototype.forEach.call(lista.options, function (op) {
            op.hidden = q !== '' && op.text.toLowerCase().indexOf(q) === -1;
        });
    });

    form.addEventListener('submit', function (e) {
        var errores = [];
        var esSalida = document.getElementById('tipo').value === 'Salida';
        var cantidad = document.getElementById('cantidad').value.trim();
        var fecha = document.getElementById('fecha_movimiento');
        var persona = document.getElementById('nombre_persona').value.replace(/\s+/g, ' ').trim();
        var cedula = document.getElementById('cedula_persona').value.trim();

        if (lista.value === '') errores.push('Seleccione un insumo de la lista.');
        if (!/^\d+$/.test(cantidad) || parseInt(cantidad, 10) < 1) {
            errores.push('La cantidad debe ser un número entero mayor que 0.');
        } else if (parseInt(cantidad, 10) > 1000000) {
            errores.push('La cantidad no puede superar 1.000.000.');
        }
        if (!fechaValida(fecha.value)) {
            errores.push('La fecha del movimiento no es válida.');
        } else if (fecha.value < fecha.min || fecha.value > fecha.max) {
            errores.push('La fecha no puede ser futura ni anterior al 01/01/2000.');
        }
        if (persona.length < 3 || persona.length > 100 || !/^[\p{L}\p{N}\s.,'-]+$/u.test(persona)) {
            errores.push('Escriba el nombre de la persona (entre 3 y 100 caracteres).');
        }
        if (!/^[A-Za-z0-9][A-Za-z0-9-]{4,19}$/.test(cedula)) errores.push('Indique la cédula, RIF, extranjero o pasaporte (5 a 20 caracteres: letras, números y guion).');
        if (esSalida) {
            var dest = document.getElementById('destinatario').value.replace(/\s+/g, ' ').trim();
            if (dest.length < 3 || dest.length > 100) errores.push('Indique para quién es el insumo (paciente o servicio).');
        }
        if (document.getElementById('observacion').value.length > 255) errores.push('La observación no puede superar los 255 caracteres.');

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
