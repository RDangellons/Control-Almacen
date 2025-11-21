async function verificarSesionYOIrALoginSiNo() {
  try {
    // OJO: esta ruta es relativa al HTML (public/*), no al JS
    const res = await fetch('../api/auth/me.php', { cache: 'no-store' });

    // Si no hay sesión, mandar a login
    if (res.status === 401) {
      window.location.href = 'login.html';
      return;
    }

    const data = await res.json();
    console.log('Respuesta de me.php en auth-guard:', data);

    if (!data || !data.usuario) {
      window.location.href = 'login.html';
      return;
    }

    const usuario = data.usuario; // {id, nombre, rol}

    // Pintar nombre y rol si existe el span
    const infoSpan = document.getElementById('user-info');
    if (infoSpan) {
      infoSpan.textContent = `👤 ${usuario.nombre} (${usuario.rol})`;
    }

  } catch (e) {
    console.error('Error verificando sesión desde auth-guard:', e);
    window.location.href = 'login.html';
  }
}
