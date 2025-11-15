function verificarSesionYOIrALoginSiNo() {
  fetch('../api/auth/me.php')
    .then(res => {
      if (res.status === 401) {
        // No hay sesión -> mandar a login
        window.location.href = 'login.html';
        return null;
      }
      return res.json();
    })
    .then(data => {
      if (!data) return;
      // Aquí podrías poner el nombre del usuario en el header si quieres
      console.log('Usuario autenticado:', data.usuario);
    })
    .catch(err => {
      console.error('Error verificando sesión', err);
      // En caso de error fuerte, también puedes mandar a login:
      // window.location.href = 'login.html';
    });
}
