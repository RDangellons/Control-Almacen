document.addEventListener('DOMContentLoaded', () => {
  cargarProductos();
});

function cargarProductos() {
  // Ajusta la ruta según dónde coloques la carpeta en XAMPP
  fetch('../api/productos/listar.php')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('tabla-productos-body');
      tbody.innerHTML = '';

      if (Array.isArray(data)) {
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
      const tbody = document.getElementById('tabla-productos-body');
      tbody.innerHTML = `<tr><td colspan="5">Error de conexión: ${err}</td></tr>`;
    });
}
