<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: inicio_sesion.php");
    exit();
}
include '../incluir/conexion.php';
require_once '../Controlador/ProductoControlador.php';

$productoControlador = new ProductoControlador($conexion);

if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $id_producto = (int)$_GET['id'];
    $ok = $productoControlador->eliminarProducto($id_producto);
    $mensaje = $ok ? 'Producto eliminado con exito.' : 'No se pudo eliminar el producto.';
    header('Location: gestion_productos.php?mensaje=' . urlencode($mensaje));
    exit();
}

$productos = $productoControlador->listarProductos();
$mensaje = isset($_GET['mensaje']) ? trim((string)$_GET['mensaje']) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Gestión de Productos</h2>
        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="text-right mb-3">
            <a href="../index.php" class="btn btn-info mr-2">Volver al inicio</a>
            <a href="agregar_producto.php" class="btn btn-success">Agregar Producto</a>
            <a href="cerrar_sesion.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php include '../Vista/productos/tabla_productos.php'; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>