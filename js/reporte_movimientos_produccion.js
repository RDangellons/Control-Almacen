document.addEventListener('DOMContentLoaded', () => {
  const form      = document.getElementById('form-filtros');
  const btnLimpiar= document.getElementById('btn-limpiar');
  const btnImprimir = document.getElementById('btn-imprimir');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      cargarMovimientos();
    });
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', () => {
      document.getElementById('desde').value = '';
      document.getElementById('hasta').value = '';
      document.getElementById('estado').value = '';
      document.getElementById('usuario_id').value = '';
      cargarMovimientos();
    });
  }

  if (btnImprimir) {
    btnImprimir.addEventListener('click', () => {
      window.print();
    });
  }

  // Carga inicial sin filtros
  cargarMovimientos();
});

async function cargarMovimientos() {
  const tbody = document.getElementById('tabla-mov-body');
  if (!tbody) return;

  tbody.innerHTML = '<tr><td colspan="10">Cargando...</td></tr>';

  const desde      = document.getElementById('desde').value;
  const hasta      = document.getElementById('hasta').value;
  const estado     = document.getElementById('estado').value;
  const usuario_id = document.getElementById('usuario_id').value;

  const params = new URLSearchParams();
  if (desde)      params.append('desde', desde);
  if (hasta)      params.append('hasta', hasta);
  if (estado)     params.append('estado', estado);
  if (usuario_id) params.append('usuario_id', usuario_id);

  const url = '../api/produccion/reporte_movimientos.php' +
              (params.toString() ? ('?' + params.toString()) : '');

  try {
    const res = await fetch(url, { cache: 'no-store' });
    if (res.status === 401) {
      window.location.href = 'login.html';
      return;
    }
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="10">No hay movimientos con esos filtros.</td></tr>';
      return;
    }

    tbody.innerHTML = '';

    data.forEach(mov => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${mov.fecha_movimiento || ''}</td>
        <td>${mov.usuario_nombre || ''}</td>
        <td>${mov.orden_id}</td>
        <td>${mov.modelo || ''}</td>
        <td>${mov.producto_nombre || ''}</td>
        <td>${mov.color || ''}</td>
        <td>${mov.cantidad}</td>
        <td>${textoEstadoBonito(mov.estado_anterior)}</td>
        <td>${textoEstadoBonito(mov.estado_nuevo)}</td>
        <td>${mov.referencia || ''}</td>
      `;
      tbody.appendChild(tr);
    });

  } catch (err) {
    console.error('Error cargando movimientos de producción:', err);
    tbody.innerHTML = '<tr><td colspan="10">Error al cargar los movimientos.</td></tr>';
  }
}

function textoEstadoBonito(estado) {
  if (!estado) return '';
  switch (estado) {
    case 'tejido':     return 'Tejido';
    case 'confeccion': return 'Confección';
    case 'revisado':   return 'Revisado';
    case 'bodega':     return 'Bodega';
    case 'terminada':  return 'Terminada';
    case 'cancelada':  return 'Cancelada';
    default:           return estado;
  }
}
