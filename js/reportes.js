document.addEventListener('DOMContentLoaded', () => {
  // Cargar existencias al inicio
  cargarExistencias();

  // Eventos formularios
  const formMov = document.getElementById('form-movimientos');
  formMov.addEventListener('submit', (e) => {
    e.preventDefault();
    cargarMovimientos();
  });

  const formExist = document.getElementById('form-existencias');
  formExist.addEventListener('submit', (e) => {
    e.preventDefault();
    cargarExistencias();
  });

  // Botones imprimir
  document.getElementById('btn-imprimir-mov').addEventListener('click', () => {
    window.print();
  });

  document.getElementById('btn-imprimir-exist').addEventListener('click', () => {
    window.print();
  });
});

function cargarMovimientos() {
  const fecha_inicio = document.getElementById('fecha_inicio').value;
  const fecha_fin    = document.getElementById('fecha_fin').value;
  const producto_id  = document.getElementById('producto_id').value;
  const tipo         = document.getElementById('tipo').value;

  // Construimos query string
  const params = new URLSearchParams();

  if (fecha_inicio) params.append('fecha_inicio', fecha_inicio);
  if (fecha_fin)    params.append('fecha_fin', fecha_fin);
  if (producto_id)  params.append('producto_id', producto_id);
  if (tipo)         params.append('tipo', tipo);

  fetch('../api/reportes/movimientos.php?' + params.toString())
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('tabla-movimientos-body');
      tbody.innerHTML = '';

      if (Array.isArray(data)) {
        if (data.length === 0) {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td colspan="7">No hay movimientos con esos filtros.</td>`;
          tbody.appendChild(tr);
          return;
        }

        data.forEach(mov => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${mov.fecha}</td>
            <td>${mov.producto_codigo} - ${mov.producto_nombre}</td>
            <td>${mov.tipo}</td>
            <td>${mov.cantidad}</td>
            <td>${mov.motivo || ''}</td>
            <td>${mov.referencia || ''}</td>
            <td>${mov.usuario_nombre || ''}</td>
          `;
          tbody.appendChild(tr);
        });
      } else if (data.error) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="7">Error: ${data.error}</td>`;
        tbody.appendChild(tr);
      }
    })
    .catch(err => {
      const tbody = document.getElementById('tabla-movimientos-body');
      tbody.innerHTML = `<tr><td colspan="7">Error de conexión: ${err}</td></tr>`;
    });
}

function cargarExistencias() {
  const busqueda = document.getElementById('busqueda').value;

  const params = new URLSearchParams();
  if (busqueda) params.append('q', busqueda);

  fetch('../api/reportes/existencias.php?' + params.toString())
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('tabla-existencias-body');
      tbody.innerHTML = '';

      if (Array.isArray(data)) {
        if (data.length === 0) {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td colspan="5">No hay productos con esa búsqueda.</td>`;
          tbody.appendChild(tr);
          return;
        }

        data.forEach(prod => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${prod.codigo}</td>
            <td>${prod.nombre}</td>
            <td>${prod.color || ''}</td>
            <td>${prod.talla || ''}</td>
            <td>${prod.existencias}</td>
          `;
          tbody.appendChild(tr);
        });
      } else if (data.error) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="5">Error: ${data.error}</td>`;
        tbody.appendChild(tr);
      }
    })
    .catch(err => {
      const tbody = document.getElementById('tabla-existencias-body');
      tbody.innerHTML = `<tr><td colspan="5">Error de conexión: ${err}</td></tr>`;
    });
}
