<?php
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin'])) {
    header("Location: inicio_sesion.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../incluir/conexion.php';
require_once '../Controlador/ProductoControlador.php';

$productoControlador = new ProductoControlador($conexion);

// Obtener el producto a editar
if (isset($_GET['id'])) {
    $id_producto = (int)$_GET['id'];
    $producto = $productoControlador->obtenerProducto($id_producto);
    if ($producto === null) {
        header("Location: gestion_productos.php");
        exit();
    }
} else {
    header("Location: gestion_productos.php");
    exit();
}

// Manejar el envío del formulario para actualizar el producto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resultado = $productoControlador->actualizarProducto(
        $id_producto,
        $_POST,
        $_FILES['imagen'] ?? [],
        (string)$producto['imagen']
    );

    if (!empty($resultado['ok'])) {
        $mensaje = 'Producto actualizado con exito.';
        header('Location: gestion_productos.php?mensaje=' . urlencode($mensaje));
        exit();
    }

    $error = (string)($resultado['error'] ?? 'No se pudo actualizar el producto.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Editar Producto</h2>
        <div class="text-right mb-3">
            <a href="../index.php" class="btn btn-info">Volver al inicio</a>
        </div>
        <?php if (isset($error)) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre del producto:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $producto['nombre']; ?>" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required><?php echo $producto['descripcion']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="precio">Precio:</label>
                <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?php echo $producto['precio']; ?>" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $producto['stock']; ?>" required>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen (deja en blanco si no deseas cambiarla):</label>
                <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*">
                <small class="form-text text-muted">Imagen actual: <?php echo $producto['imagen']; ?></small>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Producto</button>
            <a href="gestion_productos.php" class="btn btn-secondary">Volver</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>