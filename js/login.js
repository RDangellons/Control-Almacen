document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('login-form');
  const msg  = document.getElementById('login-msg');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    msg.style.color = '#d9534f';
    msg.textContent = 'Verificando...';

    const formData = new FormData(form);

    try {
      const res = await fetch('/Control-Almacen/api/auth/login.php', { // 👈 RUTA CORRECTA
        method: 'POST',
        body: formData
      });

      const text = await res.text();
      console.log('Respuesta cruda del servidor:', text);

      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        console.error('No es JSON válido:', err);
        msg.textContent = 'Error inesperado en el servidor (JSON inválido).';
        return;
      }

      if (!res.ok || data.error) {
        msg.textContent = data.error || 'Error al iniciar sesión.';
        return;
      }

      msg.style.color = 'green';
      msg.textContent = data.mensaje || 'Login correcto.';

      setTimeout(() => {
        // A dónde quieres ir después del login
        window.location.href = '/Control-Almacen/public/index.html';
      }, 1000);

    } catch (e2) {
      console.error('Error en fetch:', e2);
      msg.textContent = 'Error de conexión con el servidor.';
    }
  });
});
