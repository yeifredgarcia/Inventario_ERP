// Utilidades comunes a todas las páginas

// Confirmación antes de enviar formularios marcados con data-confirmar (baja / reactivación)
document.addEventListener('submit', function (e) {
    var mensaje = e.target.getAttribute('data-confirmar');
    if (mensaje && !window.confirm(mensaje)) {
        e.preventDefault();
    }
});

// Muestra una lista de errores de validación encima del formulario
function mostrarErrores(form, errores) {
    var previo = form.querySelector('.alerta-validacion');
    if (previo) previo.remove();
    if (errores.length === 0) return;

    var caja = document.createElement('div');
    caja.className = 'alert alert-danger alerta-validacion';
    var lista = document.createElement('ul');
    lista.className = 'mb-0';
    errores.forEach(function (texto) {
        var li = document.createElement('li');
        li.textContent = texto;
        lista.appendChild(li);
    });
    caja.appendChild(lista);
    form.insertBefore(caja, form.firstChild);
    caja.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Deja solo dígitos en los campos numéricos (cédula, cantidad)
function soloDigitos(input) {
    if (!input) return;
    input.addEventListener('input', function () {
        input.value = input.value.replace(/\D/g, '');
    });
}

function fechaValida(texto) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(texto)) return false;
    var partes = texto.split('-').map(Number);
    var d = new Date(partes[0], partes[1] - 1, partes[2]);
    return d.getFullYear() === partes[0] && d.getMonth() === partes[1] - 1 && d.getDate() === partes[2];
}

// Botón "Menú" de la barra lateral en pantallas pequeñas
document.addEventListener('DOMContentLoaded', function () {
    var boton = document.getElementById('botonMenu');
    var menu = document.getElementById('menuLateral');
    if (!boton || !menu) return;
    boton.addEventListener('click', function () {
        var abierta = menu.classList.toggle('abierta');
        boton.setAttribute('aria-expanded', abierta ? 'true' : 'false');
    });
});

// Opciones bloqueadas para el rol: muestran un aviso en lugar de abrir la página
document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-bloqueado]');
    if (!el) return;
    e.preventDefault();

    var zona = document.querySelector('main');
    if (!zona) { window.alert(el.getAttribute('data-bloqueado')); return; }
    var previo = zona.querySelector('.aviso-bloqueado');
    if (previo) previo.remove();

    var aviso = document.createElement('div');
    aviso.className = 'alert alert-warning alert-dismissible fade show aviso-bloqueado';
    aviso.setAttribute('role', 'alert');
    aviso.textContent = el.getAttribute('data-bloqueado');
    var cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.className = 'btn-close';
    cerrar.setAttribute('data-bs-dismiss', 'alert');
    cerrar.setAttribute('aria-label', 'Cerrar');
    aviso.appendChild(cerrar);
    zona.insertBefore(aviso, zona.firstChild);
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
