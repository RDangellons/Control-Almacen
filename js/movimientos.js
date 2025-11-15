document.addEventListener('DOMContentLoaded', () => {
  cargarProductos();
  
  const form = document.getElementById('form-movimiento');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    registrarMovimiento();
  });

  const selectProducto = document.getElementById('producto_id');
  selectProducto.addEventListener('change', actualizarExistenciasLabel);
});

let productosCache = [];

function cargarProductos() {
  fetch('../api/productos/listar.php')
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById('producto_id');
      select.innerHTML = '<option value="">Selecciona un producto</option>';

      if (Array.isArray(data)) {
        productosCache = data; // guardamos para usar sus existencias

        data.forEach(prod => {
          const opt = document.createElement('option');
          opt.value = prod.id;
          opt.textContent = `${prod.codigo} - ${prod.nombre} (${prod.color || ''} ${prod.talla || ''})`;
          select.appendChild(opt);
        });
      } else {
        alert('No se pudieron cargar los productos.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error al cargar productos.');
    });
}

function actualizarExistenciasLabel() {
  const span = document.getElementById('existencias-actuales');
  const select = document.getElementById('producto_id');
  const idSeleccionado = parseInt(select.value);

  if (!idSeleccionado) {
    span.textContent = '-';
    return;
  }

  const prod = productosCache.find(p => parseInt(p.id) === idSeleccionado);
  if (prod) {
    span.textContent = prod.existencias;
  } else {
    span.textContent = '-';
  }
}

function registrarMovimiento() {
  const form = document.getElementById('form-movimiento');
  const formData = new FormData(form);

  fetch('../api/movimientos/registrar.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.ok) {
        alert(data.mensaje || 'Movimiento registrado correctamente.');
        
        // Actualizar existencias mostradas
        const select = document.getElementById('producto_id');
        const idSeleccionado = parseInt(select.value);
        const prodIndex = productosCache.findIndex(p => parseInt(p.id) === idSeleccionado);
        
        if (prodIndex !== -1 && typeof data.existencia_actualizada !== 'undefined') {
          productosCache[prodIndex].existencias = data.existencia_actualizada;
          actualizarExistenciasLabel();
        }

        // Limpiar solo cantidad, motivo y referencia
        document.getElementById('cantidad').value = '';
        document.getElementById('motivo').value = '';
        document.getElementById('referencia').value = '';

      } else if (data.error) {
        alert('Error: ' + data.error);
      } else {
        alert('Ocurrió un problema al registrar el movimiento.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión al registrar el movimiento.');
    });
}
