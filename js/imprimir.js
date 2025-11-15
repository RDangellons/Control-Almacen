let productosOriginales = [];

document.addEventListener('DOMContentLoaded', () => {
  cargarProductos();

  const inputBusqueda = document.getElementById('busqueda');
  if (inputBusqueda) {
    inputBusqueda.addEventListener('input', filtrarTabla);
  }

  const btnImprimir = document.getElementById('btn-imprimir');
  if (btnImprimir) {
    btnImprimir.addEventListener('click', () => {
      window.print();
    });
  }
});

function cargarProductos() {
  fetch('../api/productos/listar.php')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('tabla-productos-body');

      if (!Array.isArray(data)) {
        tbody.innerHTML = `<tr><td colspan="5">Error al obtener productos.</td></tr>`;
        return;
      }

      // Guardamos todos los productos para poder filtrar después
      productosOriginales = data;
      renderTabla(productosOriginales);
    })
    .catch(err => {
      const tbody = document.getElementById('tabla-productos-body');
      tbody.innerHTML = `<tr><td colspan="5">Error de conexión: ${err}</td></tr>`;
    });
}

function renderTabla(lista) {
  const tbody = document.getElementById('tabla-productos-body');
  tbody.innerHTML = '';

  if (!lista || lista.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5">No hay productos para mostrar.</td></tr>`;
    return;
  }

  lista.forEach(prod => {
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td>${prod.codigo}</td>
      <td>${prod.nombre}</td>
      <td>${prod.color || ''}</td>
      <td>${prod.talla || ''}</td>
      <td>${prod.precio_referencia || ""}</td>
      <td>${typeof prod.existencias !== 'undefined' ? prod.existencias : ''}</td>
    `;

    tbody.appendChild(tr);
  });
}

function filtrarTabla() {
  const inputBusqueda = document.getElementById('busqueda');
  const texto = inputBusqueda.value.toLowerCase().trim();

  if (!texto) {
    // Si el buscador está vacío, mostramos todo
    renderTabla(productosOriginales);
    return;
  }

  const filtrados = productosOriginales.filter(prod => {
    const codigo = (prod.codigo || '').toLowerCase();
    const nombre = (prod.nombre || '').toLowerCase();
    const color  = (prod.color  || '').toLowerCase();
    const talla  = (prod.talla  || '').toLowerCase();

    return (
      codigo.includes(texto) ||
      nombre.includes(texto) ||
      color.includes(texto) ||
      talla.includes(texto)
    );
  });

  renderTabla(filtrados);
}
