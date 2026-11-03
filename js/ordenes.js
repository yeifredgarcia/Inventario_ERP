document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formOrden');
    if (!form) return;

    var contenedor = document.getElementById('lineasOrden');
    var botonAgregar = document.getElementById('agregarLinea');
    var MAX_LINEAS = 10;

    // Filtra las opciones de un desplegable según lo escrito
    function filtrarOpciones(select, texto) {
        var q = texto.toLowerCase();
        Array.prototype.forEach.call(select.options, function (op) {
            op.hidden = q !== '' && op.value !== '' && op.text.toLowerCase().indexOf(q) === -1;
        });
    }
    // Búsqueda del beneficiario por documento (V-, E-, RIF, pasaporte) o por nombre
    function normalizar(t) { return t.toUpperCase().replace(/[^A-Z0-9]/g, ''); }
    document.getElementById('buscarBen').addEventListener('input', function (ev) {
        var sel = document.getElementById('id_beneficiario');
        var texto = ev.target.value.toLowerCase().trim();
        var doc = normalizar(ev.target.value);
        var visibles = [];
        Array.prototype.forEach.call(sel.options, function (op) {
            if (op.value === '') return;
            var coincide = texto === '' ||
                op.text.toLowerCase().indexOf(texto) !== -1 ||
                (doc !== '' && (op.getAttribute('data-doc') || '').indexOf(doc) !== -1);
            op.hidden = !coincide;
            if (coincide) visibles.push(op);
        });
        if (texto !== '' && visibles.length === 1) sel.value = visibles[0].value; // un solo resultado: se elige solo
    });
    document.getElementById('buscarMed').addEventListener('input', function (ev) {
        Array.prototype.forEach.call(contenedor.querySelectorAll('select'), function (s) {
            filtrarOpciones(s, ev.target.value);
        });
    });

    // Solo dígitos en todas las cantidades (también en las líneas que se agreguen después)
    contenedor.addEventListener('input', function (ev) {
        if (ev.target.name === 'cantidad[]') ev.target.value = ev.target.value.replace(/\D/g, '');
    });

    function actualizarBoton() {
        botonAgregar.disabled = contenedor.querySelectorAll('.linea-orden').length >= MAX_LINEAS;
    }

    botonAgregar.addEventListener('click', function () {
        var lineas = contenedor.querySelectorAll('.linea-orden');
        if (lineas.length >= MAX_LINEAS) return;
        var nueva = lineas[lineas.length - 1].cloneNode(true);
        nueva.querySelector('select').value = '';
        nueva.querySelector('input').value = '';
        contenedor.appendChild(nueva);
        actualizarBoton();
    });

    contenedor.addEventListener('click', function (ev) {
        var boton = ev.target.closest('.quitar-linea');
        if (!boton) return;
        var fila = boton.closest('.linea-orden');
        if (contenedor.querySelectorAll('.linea-orden').length > 1) {
            fila.remove();
        } else {
            fila.querySelector('select').value = '';
            fila.querySelector('input').value = '';
        }
        actualizarBoton();
    });
    actualizarBoton();

    form.addEventListener('submit', function (e) {
        var errores = [];
        var fecha = document.getElementById('fecha_salida');

        if (document.getElementById('id_beneficiario').value === '') errores.push('Seleccione un beneficiario.');
        if (!fechaValida(fecha.value)) {
            errores.push('La fecha de salida no es válida.');
        } else if (fecha.value < fecha.min || fecha.value > fecha.max) {
            errores.push('La fecha no puede ser futura ni anterior al 01/01/2000.');
        }

        var validas = 0;
        Array.prototype.forEach.call(contenedor.querySelectorAll('.linea-orden'), function (fila, i) {
            var med = fila.querySelector('select').value;
            var cant = fila.querySelector('input').value.trim();
            if (med === '' && cant === '') return;      // fila vacía: se ignora
            if (med === '') { errores.push('Línea ' + (i + 1) + ': seleccione el medicamento.'); return; }
            if (!/^\d+$/.test(cant) || parseInt(cant, 10) < 1) { errores.push('Línea ' + (i + 1) + ': la cantidad debe ser un entero mayor que 0.'); return; }
            if (parseInt(cant, 10) > 1000000) { errores.push('Línea ' + (i + 1) + ': la cantidad no puede superar 1.000.000.'); return; }
            validas++;
        });
        if (validas === 0 && errores.length === 0) errores.push('Agregue al menos un medicamento a la orden.');
        var rnum = document.getElementById('recipe_numero');
        if (rnum) {
            var v = rnum.value.trim();
            if (v === '') {
                if (rnum.required) errores.push('Escriba el número del récipe de la persona.');
            } else if (!/^[A-Za-z0-9][A-Za-z0-9\/ -]{1,29}$/.test(v)) {
                errores.push('El número de récipe solo admite letras, números, guion y barra (de 2 a 30 caracteres).');
            }
        }
        var recipe = document.getElementById('recipe');
        if (recipe) {
            var archivo = recipe.files[0];
            if (!archivo) {
                if (recipe.required) errores.push('Adjunte el récipe médico (PDF, JPG o PNG).');
            } else {
                var ext = archivo.name.split('.').pop().toLowerCase();
                if (['pdf', 'jpg', 'jpeg', 'png'].indexOf(ext) === -1) errores.push('El récipe debe ser un archivo PDF, JPG o PNG.');
                if (archivo.size > 5 * 1024 * 1024) errores.push('El archivo del récipe no puede superar 5 MB.');
            }
        }
        if (document.getElementById('observacion').value.length > 255) errores.push('La observación no puede superar los 255 caracteres.');

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
