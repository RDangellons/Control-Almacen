// Cuando cargue la página
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filtro-modelo');
  const btnLimpiar = document.getElementById('btn-limpiar');
  const btnImprimir = document.getElementById('btn-imprimir');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      cargarExistenciasPorColor();
    });
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', () => {
      const inputModelo = document.getElementById('modelo');
      if (inputModelo) inputModelo.value = '';
      cargarExistenciasPorColor();
    });
  }
  
  if (btnImprimir) {
    btnImprimir.addEventListener('click', () => {
      window.print();
    });
  }

  // Cargar al inicio (sin filtro o con lo que haya escrito)
  cargarExistenciasPorColor();
});

// Cargar datos desde PHP
function cargarExistenciasPorColor() {
  const inputModelo = document.getElementById('modelo');
  const modelo = inputModelo ? inputModelo.value.trim() : '';

  const params = new URLSearchParams();
  if (modelo !== '') {
    params.append('modelo', modelo);
  }

  const url = '../api/reportes/existencia_colores.php' +
              (params.toString() ? ('?' + params.toString()) : '');

  fetch(url, { cache: 'no-store' })
    .then(res => {
      if (res.status === 401) {
        window.location.href = 'login.html';
        return null;
      }
      return res.json();
    })
    .then(data => {
      if (!data) return;
      renderTablaColores(data);
    })
    .catch(err => {
      console.error('Error cargando existencias por color:', err);
      const tbody = document.getElementById('tabla-colores-body');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="4">Error al cargar los datos.</td></tr>';
      }
    });
}

// Pintar la tabla con separadores y totales
function renderTablaColores(lista) {
  const tbody = document.getElementById('tabla-colores-body');
  if (!tbody) return;

  tbody.innerHTML = '';

  if (!Array.isArray(lista) || lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4">No hay datos de existencias.</td></tr>';
    return;
  }

  let modeloActual = null;
  let subtotalModelo = 0;
  let totalGeneral = 0;

  lista.forEach(item => {
    const modelo   = item.modelo || '';
    const producto = item.producto_nombre || '';
    const color    = item.color || '';
    const exist    = Number(item.existencias ?? 0);

    // Si cambia de modelo → agregamos separador y total del anterior
    if (modelo !== modeloActual) {

      // Total del modelo anterior (si ya había uno)
      if (modeloActual !== null) {
        const trTotal = document.createElement('tr');
        trTotal.classList.add('fila-total-modelo');
        trTotal.innerHTML = `
          <td colspan="3">Total del modelo ${modeloActual}</td>
          <td>${subtotalModelo}</td>
        `;
        tbody.appendChild(trTotal);

        subtotalModelo = 0;
      }

      // Fila separador del nuevo modelo
      const trSep = document.createElement('tr');
      trSep.classList.add('fila-separador');
      trSep.innerHTML = `
        <td colspan="4">MODELO ${modelo}</td>
      `;
      tbody.appendChild(trSep);

      modeloActual = modelo;
    }

    // Fila normal
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${modelo}</td>
      <td>${producto}</td>
      <td>${color}</td>
      <td>${exist}</td>
    `;
    tbody.appendChild(tr);

    subtotalModelo += exist;
    totalGeneral += exist;
  });

  // Total del último modelo
  if (modeloActual !== null) {
    const trTotalFin = document.createElement('tr');
    trTotalFin.classList.add('fila-total-modelo');
    trTotalFin.innerHTML = `
      <td colspan="3">Total del modelo ${modeloActual}</td>
      <td>${subtotalModelo}</td>
    `;
    tbody.appendChild(trTotalFin);
  }

  // Total general
  const trGeneral = document.createElement('tr');
  trGeneral.classList.add('fila-total-general');
  trGeneral.innerHTML = `
    <td colspan="3">TOTAL GENERAL</td>
    <td>${totalGeneral}</td>
  `;
  tbody.appendChild(trGeneral);
}
