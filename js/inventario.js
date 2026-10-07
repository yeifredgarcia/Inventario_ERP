document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formInsumo');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre_producto').value.trim();
            const cantidad = document.getElementById('cantidad').value;

            if (nombre === '') {
                e.preventDefault();
                alert('Ingrese el nombre del insumo.');
                return;
            }

            if (cantidad < 0 || cantidad === '') {
                e.preventDefault();
                alert('La cantidad no puede ser negativa.');
            }
        });
    }
});