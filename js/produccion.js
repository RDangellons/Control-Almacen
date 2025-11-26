document.addEventListener('DOMContentLoaded', () => {
  cargarProductosSelect();
  cargarOrdenesProduccion();

  const form = document.getElementById('form-produccion');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      await crearOrdenProduccion();
    });
  }
});

// Llenar select de productos
async function cargarProductosSelect() {
  const select = document.getElementById('producto_id');
  if (!select) return;

  try {
    // Ajusta la ruta si tu endpoint de productos se llama distinto
    const res = await fetch('../api/productos/listar.php');
    const data = await res.json();

    select.innerHTML = '<option value="">Selecciona un producto...</option>';

    data.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = `${p.codigo} - ${p.nombre} (${p.color})`;
      select.appendChild(opt);
    });
  } catch (err) {
    console.error('Error cargando productos para producción:', err);
  }
}

// Crear orden
async function crearOrdenProduccion() {
  const selectProd = document.getElementById('producto_id');
  const inputCant  = document.getElementById('cantidad');
  const inputRef   = document.getElementById('referencia');

  const producto_id = selectProd.value;
  const cantidad    = inputCant.value;
  const referencia  = inputRef.value;

  if (!producto_id || !cantidad || Number(cantidad) <= 0) {
    alert('Selecciona un producto y una cantidad válida.');
    return;
  }

  try {
    const res = await fetch('../api/produccion/crear.php', {
      method: 'POST',
      body: new URLSearchParams({
        producto_id,
        cantidad,
        referencia
      })
    });

    const data = await res.json();

    if (data.ok) {
      alert('Orden de producción creada.');
      inputCant.value = '';
      inputRef.value = '';
      cargarOrdenesProduccion();
    } else {
      alert('Error: ' + (data.error || 'No se pudo crear la orden.'));
    }
  } catch (err) {
    console.error('Error creando orden:', err);
    alert('Error al crear la orden.');
  }
}

// Listar órdenes en tránsito
async function cargarOrdenesProduccion() {
  const tbody = document.getElementById('tabla-op-body');
  if (!tbody) return;

  tbody.innerHTML = '<tr><td colspan="9">Cargando...</td></tr>';

  try {
    const res = await fetch('../api/produccion/listar.php?_= ' + Date.now(), {
      cache: 'no-store'
    });
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9">No hay órdenes en tránsito.</td></tr>';
      return;
    }

    tbody.innerHTML = '';

    data.forEach(op => {
      const tr = document.createElement('tr');
      const fecha = op.fecha_creacion || '';

      const siguienteEstado = getSiguienteEstado(op.estado);
      let botones = '';

      if (siguienteEstado) {
        botones += `<button data-id="${op.id}" data-estado="${siguienteEstado}" class="btn-op-sig">
                      ${textoEstadoBonito(siguienteEstado)}
                    </button>`;
      }

      botones += ` <button data-id="${op.id}" data-estado="terminada" class="btn-op-term">
                     Terminar
                   </button>`;

      botones += ` <button data-id="${op.id}" data-estado="cancelada" class="btn-op-canc">
                     Cancelar
                   </button>`;

      tr.innerHTML = `
        <td>${op.id}</td>
        <td>${op.codigo || ''}</td>
        <td>${op.nombre || ''}</td>
        <td>${op.color || ''}</td>
        <td>${op.cantidad}</td>
        <td>${textoEstadoBonito(op.estado)}</td>
        <td>${op.referencia || ''}</td>
        <td>${fecha}</td>
        <td>${botones}</td>
      `;

      tbody.appendChild(tr);
    });

    // Delegar eventos de botones (una vez por recarga)
    tbody.onclick = async (e) => {
      const btn = e.target;
      const id = btn.dataset.id;
      const estado = btn.dataset.estado;
      if (id && estado) {
        await cambiarEstadoOP(id, estado);
      }
    };

  } catch (err) {
    console.error('Error cargando órdenes de producción:', err);
    tbody.innerHTML = '<tr><td colspan="9">Error al cargar las órdenes.</td></tr>';
  }
}

function getSiguienteEstado(estadoActual) {
  switch (estadoActual) {
    case 'tejido':      return 'confeccion';
    case 'confeccion':  return 'revisado';
    case 'revisado':    return 'bodega';
    case 'bodega':      return 'terminada';
    default:            return null;
  }
}

function textoEstadoBonito(estado) {
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

// Cambiar estado
async function cambiarEstadoOP(id, estado) {
  if (!confirm('¿Cambiar estado a "' + textoEstadoBonito(estado) + '"?')) return;

  try {
    const res = await fetch('../api/produccion/cambiar_estado.php', {
      method: 'POST',
      body: new URLSearchParams({ id, estado })
    });
    const data = await res.json();

    if (data.ok) {
      cargarOrdenesProduccion();
    } else {
      alert('Error: ' + (data.error || 'No se pudo actualizar el estado.'));
    }
  } catch (err) {
    console.error('Error cambiando estado:', err);
    alert('Error al cambiar el estado.');
  }
}
