<?php
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin'])) {
    header("Location: inicio_sesion.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../incluir/conexion.php';
include '../incluir/encabezado.php';
require_once '../Controlador/ProductoControlador.php';

$productoControlador = new ProductoControlador($conexion);

// Obtener datos para los selects
$marcas = $conexion->query("SELECT id_marca, nombre FROM Marca ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
$industrias = $conexion->query("SELECT id_industria, nombre FROM Industria ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
$categorias = $conexion->query("SELECT id_categoria, nombre FROM Categoria ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
$proveedores = $conexion->query("SELECT id_proveedor, nombre FROM Proveedor ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];

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
    <title>Agregar Producto - Duran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --duran-azul-oscuro: #0a1f44;
            --duran-azul-medio: #1e3a8a;
            --duran-azul-brillante: #3b82f6;
            --duran-gris-claro: #f3f4f6;
            --duran-texto-principal: #111827;
            --duran-texto-secundario: #6b7280;
            --duran-blanco: #ffffff;
            --duran-sombra: 0 20px 50px rgba(10, 31, 68, 0.14);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: transparent;
            color: var(--duran-texto-principal);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background: var(--duran-blanco);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(10, 31, 68, 0.3);
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
            border-bottom: 2px solid #f0f0f0;
            position: sticky;
            top: 0;
            background: var(--duran-blanco);
            border-radius: 16px 16px 0 0;
        }

        .modal-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--duran-azul-oscuro);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: var(--duran-texto-secundario);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border-radius: 50%;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: var(--duran-azul-oscuro);
        }

        .modal-body {
            padding: 30px;
        }

        .form-container {
            max-width: none;
            margin: 0;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 700;
            color: var(--duran-azul-oscuro);
            margin-bottom: 10px;
            display: block;
            font-size: 0.95rem;
        }

        .form-control,
        .form-control-file,
        select.form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: var(--duran-texto-principal);
            background: var(--duran-blanco);
        }

        .form-control:focus,
        select.form-control:focus {
            border-color: var(--duran-azul-brillante);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-control-file {
            padding: 12px;
            cursor: pointer;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(59, 130, 246, 0.02));
            border: 2px dashed var(--duran-azul-brillante);
        }

        .form-text {
            color: var(--duran-texto-secundario) !important;
            font-size: 0.85rem;
            margin-top: 6px;
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 25px;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        #imagenPreview {
            text-align: center;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        #previsualizacion {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(10, 31, 68, 0.15);
        }

        .form-buttons {
            display: flex;
            gap: 12px;
            margin-top: 35px;
        }

        .btn-agregar {
            flex: 1;
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-agregar:hover {
            background: linear-gradient(90deg, #2f6fed, #275fd1);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.35);
            color: white;
            text-decoration: none;
        }

        .btn-volver {
            flex: 1;
            background: linear-gradient(90deg, var(--duran-azul-medio), var(--duran-azul-oscuro));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 18px rgba(30, 58, 138, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-volver:hover {
            background: linear-gradient(90deg, var(--duran-azul-oscuro), #051a38);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(30, 58, 138, 0.35);
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .form-container {
                margin: 30px auto;
            }

            .form-card {
                padding: 25px;
            }

            .form-header h1 {
                font-size: 1.8rem;
            }

            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="modal-overlay" id="modalAgregar">
        <div class="modal-content">
            <div class="modal-header">
                <h1><i class="fas fa-plus-circle"></i> Agregar Producto</h1>
                <button class="modal-close" onclick="cerrarModal()" id="btnCerrar" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-container">
                    <?php if (isset($error)) { 
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                        echo '<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>';
                        echo '<strong>Error:</strong> ' . htmlspecialchars($error);
                        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                        echo '</div>'; 
                    } ?>
                    
                    <form action="" method="POST" enctype="multipart/form-data" id="formProducto">
                        <div class="form-group">
                            <label for="nombre"><i class="fas fa-tag"></i> Nombre del producto:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Laptop Dell XPS 15" required minlength="3" maxlength="255">
                            <small class="form-text">Mínimo 3 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="descripcion"><i class="fas fa-align-left"></i> Descripción:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Describe los detalles del producto..." required minlength="5" rows="4"></textarea>
                            <small class="form-text">Mínimo 5 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="precio"><i class="fas fa-money-bill"></i> Precio (Bs.):</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" placeholder="Ej: 1499.99" required min="0.01">
                            <small class="form-text">En bolivianos. Debe ser mayor a 0</small>
                        </div>

                        <div class="form-group">
                            <label for="stock"><i class="fas fa-boxes"></i> Stock:</label>
                            <input type="number" class="form-control" id="stock" name="stock" placeholder="Ej: 50" required min="0">
                            <small class="form-text">No puede ser negativo</small>
                        </div>

                        <div class="form-group">
                            <label for="id_marca"><i class="fas fa-building"></i> Marca:</label>
                            <select class="form-control" id="id_marca" name="id_marca" required>
                                <option value="">-- Selecciona una marca --</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?php echo (int)$marca['id_marca']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_industria"><i class="fas fa-industry"></i> Industria:</label>
                            <select class="form-control" id="id_industria" name="id_industria" required>
                                <option value="">-- Selecciona una industria --</option>
                                <?php foreach ($industrias as $industria): ?>
                                    <option value="<?php echo (int)$industria['id_industria']; ?>"><?php echo htmlspecialchars($industria['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_categoria"><i class="fas fa-list"></i> Categoría:</label>
                            <select class="form-control" id="id_categoria" name="id_categoria" required>
                                <option value="">-- Selecciona una categoría --</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo (int)$categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_proveedor"><i class="fas fa-truck"></i> Proveedor (opcional):</label>
                            <select class="form-control" id="id_proveedor" name="id_proveedor">
                                <option value="">-- Selecciona un proveedor --</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?php echo (int)$proveedor['id_proveedor']; ?>"><?php echo htmlspecialchars($proveedor['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="imagen"><i class="fas fa-image"></i> Imagen:</label>
                            <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*" required>
                            <small class="form-text">Formatos: JPG, PNG, GIF, WebP. Máximo 5MB</small>
                            <div id="imagenPreview" style="margin-top: 15px; display: none;">
                                <img id="previsualizacion">
                            </div>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn-agregar">
                                <i class="fas fa-check-circle"></i> Agregar Producto
                            </button>
                            <button type="button" class="btn-volver" onclick="cerrarModal()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        function cerrarModal() {
            const modal = document.getElementById('modalAgregar');
            modal.style.display = 'none';
            // Opcional: regresar a gestion_productos.php
            window.history.back();
        }

        // Cerrar modal al hacer click fuera del contenido
        document.getElementById('modalAgregar').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Tecla ESC para cerrar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });

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