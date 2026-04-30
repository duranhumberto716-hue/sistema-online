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

// Obtener datos para los selects
$marcas = $conexion->query("SELECT id_marca, nombre FROM Marca ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
$industrias = $conexion->query("SELECT id_industria, nombre FROM Industria ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
$categorias = $conexion->query("SELECT id_categoria, nombre FROM Categoria ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
$proveedores = $conexion->query("SELECT id_proveedor, nombre FROM Proveedor ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];

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
        <?php if (isset($error)) { 
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($error);
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            echo '</div>'; 
        } ?>
        <form action="" method="POST" enctype="multipart/form-data" id="formProducto">
            <div class="form-group">
                <label for="nombre">Nombre del producto:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required minlength="3" maxlength="255">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required minlength="5" rows="4"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="precio">Precio:</label>
                <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required min="0.01">
            </div>
            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($producto['stock']); ?>" required min="0">
            </div>
            <div class="form-group">
                <label for="id_marca">Marca:</label>
                <select class="form-control" id="id_marca" name="id_marca" required>
                    <option value="">Selecciona una marca</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?php echo (int)$marca['id_marca']; ?>" <?php echo ((int)$marca['id_marca'] === (int)$producto['id_marca']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($marca['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_industria">Industria:</label>
                <select class="form-control" id="id_industria" name="id_industria" required>
                    <option value="">Selecciona una industria</option>
                    <?php foreach ($industrias as $industria): ?>
                        <option value="<?php echo (int)$industria['id_industria']; ?>" <?php echo ((int)$industria['id_industria'] === (int)$producto['id_industria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($industria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_categoria">Categoría:</label>
                <select class="form-control" id="id_categoria" name="id_categoria" required>
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo (int)$categoria['id_categoria']; ?>" <?php echo ((int)$categoria['id_categoria'] === (int)$producto['id_categoria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_proveedor">Proveedor (opcional):</label>
                <select class="form-control" id="id_proveedor" name="id_proveedor">
                    <option value="">Selecciona un proveedor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo (int)$proveedor['id_proveedor']; ?>" <?php echo ($producto['id_proveedor'] && (int)$proveedor['id_proveedor'] === (int)$producto['id_proveedor']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($proveedor['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen (deja en blanco si no deseas cambiarla):</label>
                <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*">
                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 5MB</small>
                
                <?php 
                // Mostrar imagen actual
                $imagenActual = (string)($producto['imagen'] ?? '');
                if ($imagenActual !== '') {
                    if (strpos($imagenActual, 'data:image') === 0) {
                        echo '<div style="margin-top: 15px;">';
                        echo '<strong>Imagen actual:</strong><br>';
                        echo '<img src="' . htmlspecialchars($imagenActual, ENT_QUOTES, 'UTF-8') . '" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px;">';
                        echo '</div>';
                    } else {
                        echo '<div style="margin-top: 15px;">';
                        echo '<strong>Imagen actual:</strong> ' . htmlspecialchars($imagenActual);
                        echo '</div>';
                    }
                }
                ?>
                
                <div id="imagenPreview" style="margin-top: 15px; display: none;">
                    <strong>Nueva imagen:</strong><br>
                    <img id="previsualizacion" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Producto</button>
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
            
            const imagen = document.getElementById('imagen');
            if (imagen.files.length > 0) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (imagen.files[0].size > maxSize) {
                    e.preventDefault();
                    alert('La imagen no puede exceder 5MB');
                    return false;
                }
            }
        });
    </script>
</body>
</html>