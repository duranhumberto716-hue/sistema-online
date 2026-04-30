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

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resultado = $productoControlador->crearProducto($_POST, $_FILES['imagen'] ?? []);

    if (!empty($resultado['ok'])) {
        $mensaje = 'Producto agregado con exito.';
        header('Location: gestion_productos.php?mensaje=' . urlencode($mensaje));
        exit();
    }

    $error = (string)($resultado['error'] ?? 'No se pudo agregar el producto.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Agregar Nuevo Producto</h2>
        <div class="text-right mb-3">
            <a href="../index.php" class="btn btn-info">Volver al inicio</a>
        </div>
        
        <?php if (isset($error)) { 
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($error);
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            echo '</div>'; 
        } ?>
        
        <form action="" method="POST" enctype="multipart/form-data" id="formProducto">
            <div class="form-group">
                <label for="nombre">Nombre del producto:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required minlength="3" maxlength="255">
                <small class="form-text text-muted">Mínimo 3 caracteres</small>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required minlength="5" rows="4"></textarea>
                <small class="form-text text-muted">Mínimo 5 caracteres</small>
            </div>
            <div class="form-group">
                <label for="precio">Precio:</label>
                <input type="number" step="0.01" class="form-control" id="precio" name="precio" required min="0.01">
                <small class="form-text text-muted">Debe ser mayor a 0</small>
            </div>
            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" class="form-control" id="stock" name="stock" required min="0">
                <small class="form-text text-muted">No puede ser negativo</small>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen:</label>
                <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*" required>
                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 5MB</small>
                <div id="imagenPreview" style="margin-top: 15px; display: none;">
                    <img id="previsualizacion" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <button type="submit" class="btn btn-success">Agregar Producto</button>
            <a href="gestion_productos.php" class="btn btn-secondary">Volver</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        const inputImagen = document.getElementById('imagen');
        const imagenPreview = document.getElementById('imagenPreview');
        const previsualizacion = document.getElementById('previsualizacion');

        // Mostrar preview de imagen
        inputImagen.addEventListener('change', function(e) {
            const archivo = e.target.files[0];
            if (archivo) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previsualizacion.src = event.target.result;
                    imagenPreview.style.display = 'block';
                };
                reader.readAsDataURL(archivo);
            } else {
                imagenPreview.style.display = 'none';
            }
        });

        document.getElementById('formProducto').addEventListener('submit', function(e) {
            const precio = parseFloat(document.getElementById('precio').value);
            const stock = parseInt(document.getElementById('stock').value);
            const imagen = document.getElementById('imagen');
            
            if (precio <= 0) {
                e.preventDefault();
                alert('El precio debe ser mayor a 0');
                return false;
            }
            
            if (stock < 0) {
                e.preventDefault();
                alert('El stock no puede ser negativo');
                return false;
            }
            
            if (imagen.files.length === 0) {
                e.preventDefault();
                alert('Selecciona una imagen');
                return false;
            }
            
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (imagen.files[0].size > maxSize) {
                e.preventDefault();
                alert('La imagen no puede exceder 5MB');
                return false;
            }
        });
    </script>
</body>
</html>