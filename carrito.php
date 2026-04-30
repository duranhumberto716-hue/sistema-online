<?php
// Incluir la conexión a la base de datos
include 'incluir/conexion.php';
require_once 'Controlador/CompraControlador.php';

session_start();
$compraControlador = new CompraControlador($conexion);
$error = '';

if (!$compraControlador->asegurarEstructura()) {
    $error = 'No se pudo preparar la estructura de carrito en la base de datos.';
}

// Procesar acciones del carrito
if ($error === '' && isset($_GET['accion']) && isset($_GET['id'])) {
    $id_producto = (int)$_GET['id'];
    $sessionId = session_id();

    if ($_GET['accion'] == 'agregar') {
        $cantidadAgregar = (int)($_GET['cantidad'] ?? 1);
        if ($cantidadAgregar < 1) {
            $cantidadAgregar = 1;
        }

        if (!$compraControlador->agregarAlCarrito($sessionId, $id_producto, $cantidadAgregar)) {
            $error = 'No se pudo agregar el producto al carrito.';
        }
    } elseif ($_GET['accion'] == 'actualizar') {
        $cantidadNueva = (int)($_GET['cantidad'] ?? 0);
        if ($cantidadNueva < 0) {
            $cantidadNueva = 0;
        }

        if (!$compraControlador->actualizarCantidadCarrito($sessionId, $id_producto, $cantidadNueva)) {
            $error = 'No se pudo actualizar la cantidad del producto.';
        }
    } elseif ($_GET['accion'] == 'eliminar') {
        if (!$compraControlador->eliminarDelCarrito($sessionId, $id_producto)) {
            $error = 'No se pudo eliminar el producto del carrito.';
        }
    }
}

$sessionId = session_id();
$itemsCarritoDetallado = $error === '' ? $compraControlador->obtenerCarritoDetallado($sessionId) : [];
$carritoSesion = $_SESSION['carrito'] ?? [];
if (!is_array($carritoSesion)) {
    $carritoSesion = [];
}
$compraControlador->sincronizarSesionDesdeBd($sessionId, $carritoSesion);

$total = 0.0;
foreach ($itemsCarritoDetallado as $itemCarrito) {
    $total += (float)$itemCarrito['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="recursos/css/estilos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'incluir/encabezado.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Carrito de Compras</h1>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php include 'Vista/carrito/filas_carrito.php'; ?>
                </tbody>
            </table>
        </div>
        <h3 class="text-right">Total: $<?php echo number_format($total, 2); ?></h3>
        <div class="text-right">
            <a href="pago.php" class="btn btn-success">Proceder al Pago</a>
        </div>
    </div>

    <?php include 'incluir/pie.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>