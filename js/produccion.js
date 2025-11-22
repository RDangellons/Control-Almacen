document.addEventListener('DOMContentLoaded', () => {
  cargarProduccionEnTransito();
});

function cargarProduccionEnTransito() {
  fetch('../api/produccion/listar_en_transito.php', { cache: 'no-store' })
    .then(res => {
      if (res.status === 401) {
        window.location.href = 'login.html';
        return null;
      }
      return res.json();
    })
    .then(data => {
      if (!data) return;
      renderTablaProduccion(data);
    })
    .catch(err => {
      console.error('Error cargando producción en tránsito:', err);
      const tbody = document.getElementById('tabla-produccion-body');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="10">Error al cargar los datos.</td></tr>';
      }
    });
}

function renderTablaProduccion(lista) {
  const tbody = document.getElementById('tabla-produccion-body');
  tbody.innerHTML = '';

  if (!Array.isArray(lista) || lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="10">No hay órdenes en tránsito.</td></tr>';
    return;
  }

  lista.forEach(op => {
    const tr = document.createElement('tr');

    const productoTexto = `${op.producto_codigo || ''} - ${op.producto_nombre || ''}`;
    const colorTalla = `${op.color || ''} ${op.talla || ''}`.trim();
    const estadoLabel = formatearEstado(op.estado);

    const fechaInicio = op.fecha_inicio ? formatearFechaHora(op.fecha_inicio) : '';
    const fechaEntrega = op.fecha_entrega_estimada ? formatearFecha(op.fecha_entrega_estimada) : '';

    tr.innerHTML = `
      <td>${op.id}</td>
      <td>${productoTexto}</td>
      <td>${colorTalla}</td>
      <td>${op.cantidad_total}</td>
      <td>${op.cantidad_terminada}</td>
      <td>${op.cantidad_pendiente}</td>
      <td>${estadoLabel}</td>
      <td>${fechaInicio}</td>
      <td>${fechaEntrega}</td>
      <td>${op.responsable || ''}</td>
    `;

    tbody.appendChild(tr);
  });
}

function formatearEstado(estado) {
  if (!estado) return '';
  switch (estado) {
    case 'en_transito':
      return 'En tránsito';
    case 'terminado':
      return 'Terminado';
    case 'pausado':
      return 'Pausado';
    default:
      return estado;
  }
}

function formatearFecha(fechaStr) {
  // Asume formato YYYY-MM-DD
  if (!fechaStr) return '';
  const partes = fechaStr.split('-');
  if (partes.length !== 3) return fechaStr;
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function formatearFechaHora(fechaHoraStr) {
  // Asume formato 'YYYY-MM-DD HH:MM:SS'
  if (!fechaHoraStr) return '';
  const [fecha, hora] = fechaHoraStr.split(' ');
  return `${formatearFecha(fecha)} ${hora ? hora.substring(0,5) : ''}`;
}
