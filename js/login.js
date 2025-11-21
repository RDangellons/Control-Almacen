document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('login-form');
  const msg  = document.getElementById('login-msg');

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    msg.textContent = '';

    const formData = new FormData(form);

    fetch('../api/auth/login.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          msg.style.color = 'green';
          msg.textContent = 'Ingreso correcto, redirigiendo...';
          // Redirigimos al menú principal
          setTimeout(() => {
            window.location.href = 'index.html';
          }, 500);
        } else if (data.error) {
          msg.style.color = '#d9534f';
          msg.textContent = data.error;
        } else {
          msg.style.color = '#d9534f';
          msg.textContent = 'Error desconocido al iniciar sesión.';
        }
      })
      .catch(err => {
        console.error(err);
        msg.style.color = '#d9534f';
        msg.textContent = 'Error de conexión con el servidor.';
      });
  });
});
