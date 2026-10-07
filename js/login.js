document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario').value.trim();
            const clave = document.getElementById('clave').value.trim();

            if (usuario === '' || clave === '') {
                e.preventDefault();
                alert('Por favor, complete todos los campos de acceso.');
            }
        });
    }
});