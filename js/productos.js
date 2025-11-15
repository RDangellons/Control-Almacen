let productosLista = [];

document.addEventListener('DOMContentLoaded', () => {
  cargarProductos();

  const form = document.getElementById('form-producto');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    guardarProducto();
  });

  document.getElementById('btn-limpiar').addEventListener('click', limpiarFormulario);
});

function cargarProductos() {
  fetch('../api/productos/listar.php')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data)) {
        alert('Error al obtener productos.');
        return;
      }

      productosLista = data;
      renderTablaProductos();
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión al cargar productos.');
    });
}

function renderTablaProductos() {
  const tbody = document.getElementById('tabla-productos-body');
  tbody.innerHTML = '';

  if (productosLista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7">No hay productos registrados.</td></tr>';
    return;
  }

  productosLista.forEach(prod => {
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td>${prod.codigo}</td>
      <td>${prod.nombre}</td>
      <td>${prod.color || ''}</td>
      <td>${prod.talla || ''}</td>
      <td>${prod.precio_referencia || ''}</td>
      <td>${prod.existencias || 0}</td>
      <td>
        <button class="btn-accion-tabla btn-editar" onclick="editarProducto(${prod.id})">Editar</button>
        <button class="btn-accion-tabla btn-eliminar" onclick="eliminarProducto(${prod.id})">Eliminar</button>
      </td>
    `;

    tbody.appendChild(tr);
  });
}

function limpiarFormulario() {
  document.getElementById('id').value = '';
  document.getElementById('codigo').value = '';
  document.getElementById('nombre').value = '';
  document.getElementById('color').value = '';
  document.getElementById('talla').value = '';
  document.getElementById('precio_referencia').value = '';
  document.getElementById('btn-guardar').textContent = 'Guardar producto';
}

function editarProducto(id) {
  const prod = productosLista.find(p => parseInt(p.id) === parseInt(id));
  if (!prod) return;

  document.getElementById('id').value = prod.id;
  document.getElementById('codigo').value = prod.codigo;
  document.getElementById('nombre').value = prod.nombre;
  document.getElementById('color').value = prod.color || '';
  document.getElementById('talla').value = prod.talla || '';
  document.getElementById('precio_referencia').value = prod.precio_referencia || '';

  document.getElementById('btn-guardar').textContent = 'Actualizar producto';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function guardarProducto() {
  const id     = document.getElementById('id').value;
  const codigo = document.getElementById('codigo').value.trim();
  const nombre = document.getElementById('nombre').value.trim();

  if (!codigo || !nombre) {
    alert('Código y nombre son obligatorios.');
    return;
  }

  const formData = new FormData(document.getElementById('form-producto'));

  let url = '../api/productos/crear.php';
  if (id) {
    url = '../api/productos/actualizar.php';
  }

  fetch(url, {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.ok) {
        alert(data.mensaje || 'Operación exitosa.');
        limpiarFormulario();
        cargarProductos();
      } else if (data.error) {
        alert('Error: ' + data.error);
      } else {
        alert('Ocurrió un problema al guardar el producto.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión al guardar el producto.');
    });
}

function eliminarProducto(id) {
  if (!confirm('¿Seguro que deseas eliminar este producto?')) return;

  const formData = new FormData();
  formData.append('id', id);

  fetch('../api/productos/eliminar.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.ok) {
        alert(data.mensaje || 'Producto eliminado.');
        cargarProductos();
      } else if (data.error) {
        alert('Error: ' + data.error);
      } else {
        alert('Ocurrió un problema al eliminar el producto.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión al eliminar el producto.');
    });
}
