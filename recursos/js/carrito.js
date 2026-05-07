// FUNCIONES AJAX PARA CARRITO

function agregarAlCarrito(idProducto, cantidad = 1) {
    fetch('carrito_api.php', {
        method: 'POST',
        body: new URLSearchParams({
            accion: 'agregar',
            id_producto: idProducto,
            cantidad: cantidad
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert(data.mensaje);
            recargarCarrito();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => alert('Error de conexión: ' + e));
}

function eliminarDelCarrito(idProducto) {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        fetch('carrito_api.php', {
            method: 'POST',
            body: new URLSearchParams({
                accion: 'eliminar',
                id_producto: idProducto
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                recargarCarrito();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(e => alert('Error de conexión: ' + e));
    }
}

function actualizarCantidad(idProducto, nuevaCantidad) {
    if (nuevaCantidad < 0) {
        alert('La cantidad no puede ser negativa');
        return;
    }
    
    fetch('carrito_api.php', {
        method: 'POST',
        body: new URLSearchParams({
            accion: 'actualizar',
            id_producto: idProducto,
            cantidad: nuevaCantidad
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            recargarCarrito();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => alert('Error de conexión: ' + e));
}

function recargarCarrito() {
    location.reload();
}
