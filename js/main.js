function confirmarEliminacion(e, url) {
    e.preventDefault();
    if (confirm('¿Seguro que deseas eliminar este registro?')) {
        window.location.href = url;
    }
}