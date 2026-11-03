document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formReporte');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var errores = [];
        var desde = document.getElementById('desde').value;
        var hasta = document.getElementById('hasta').value;

        if (desde !== '' && !fechaValida(desde)) errores.push('La fecha "desde" no es válida.');
        if (hasta !== '' && !fechaValida(hasta)) errores.push('La fecha "hasta" no es válida.');
        if (errores.length === 0 && desde !== '' && hasta !== '' && desde > hasta) {
            errores.push('La fecha "desde" no puede ser posterior a la fecha "hasta".');
        }

        mostrarErrores(form, errores);
        if (errores.length > 0) e.preventDefault();
    });
});
