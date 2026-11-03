document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formBeneficiario');
    if (!form) return;

    var selTipo = document.getElementById('tipo_documento');
    var campo = document.getElementById('cedula');
    var ayuda = document.getElementById('ayudaDocumento');

    var reglas = {
        V:   { patron: /^\d{6,10}$/,              ayuda: 'Cédula venezolana: solo números, de 6 a 10 dígitos.', error: 'La cédula (V) debe contener solo números, entre 6 y 10 dígitos.' },
        E:   { patron: /^\d{6,10}$/,              ayuda: 'Cédula de extranjero: solo números, de 6 a 10 dígitos.', error: 'La cédula de extranjero (E) debe contener solo números, entre 6 y 10 dígitos.' },
        RIF: { patron: /^[VEJGPC]\d{9}$/,         ayuda: 'RIF: letra (V, E, J, G, P o C) y 9 dígitos. Ejemplo: J-12345678-9.', error: 'El RIF debe tener una letra (V, E, J, G, P o C) seguida de 9 dígitos. Ejemplo: J-12345678-9.' },
        P:   { patron: /^[A-Z0-9]{5,15}$/,        ayuda: 'Pasaporte: de 5 a 15 letras o números, sin símbolos.', error: 'El pasaporte debe tener entre 5 y 15 letras o números, sin símbolos.' }
    };

    // Quita guiones, puntos y espacios y pasa a mayúsculas
    function normalizar(t) { return t.toUpperCase().replace(/[^A-Z0-9]/g, ''); }

    function actualizarAyuda() {
        ayuda.textContent = reglas[selTipo.value].ayuda;
        // En cédula V o E solo se escriben dígitos
        if (selTipo.value === 'V' || selTipo.value === 'E') campo.value = campo.value.replace(/\D/g, '');
    }
    selTipo.addEventListener('change', actualizarAyuda);
    campo.addEventListener('input', function () {
        if (selTipo.value === 'V' || selTipo.value === 'E') {
            campo.value = campo.value.replace(/\D/g, '');
        } else {
            campo.value = campo.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
        }
    });
    actualizarAyuda();

    form.addEventListener('submit', function (e) {
        var errores = [];
        var nombres = document.getElementById('nombres').value.replace(/\s+/g, ' ').trim();
        var apellidos = document.getElementById('apellidos').value.replace(/\s+/g, ' ').trim();
        var telefono = document.getElementById('telefono').value.replace(/\s+/g, ' ').trim();
        var tipo = form.querySelector('input[name="tipo_beneficiario"]:checked');
        var regla = reglas[selTipo.value];

        if (!/^[\p{L}\s.'-]{2,60}$/u.test(nombres)) errores.push('Los nombres deben tener entre 2 y 60 caracteres y solo letras.');
        if (!/^[\p{L}\s.'-]{2,60}$/u.test(apellidos)) errores.push('Los apellidos deben tener entre 2 y 60 caracteres y solo letras.');
        if (!regla || !regla.patron.test(normalizar(campo.value))) errores.push(regla ? regla.error : 'Seleccione el tipo de documento.');

        if (telefono !== '') {
            var digitos = telefono.replace(/\D/g, '');
            if (!/^[0-9+() -]{7,20}$/.test(telefono) || digitos.length < 7 || digitos.length > 15) {
                errores.push('El teléfono debe tener entre 7 y 15 dígitos (puede usar +, espacios, guiones y paréntesis).');
            }
        }
        if (!tipo) errores.push('Indique si el beneficiario es Trabajador o Afiliado.');

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
