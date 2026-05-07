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
            --duran-verde: #10b981;
            --duran-naranja: #f59e0b;
            --duran-rojo: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 42%),
                linear-gradient(180deg, var(--duran-gris-claro) 0%, #e9eef7 100%);
            color: var(--duran-texto-principal);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .page-container {
            padding: 40px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--duran-blanco);
            border: 2px solid var(--duran-azul-brillante);
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.3s ease;
            flex: 0 1 300px;
        }

        .search-container:focus-within {
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            border-color: var(--duran-azul-oscuro);
        }

        .search-container input {
            border: none;
            background: none;
            outline: none;
            font-size: 0.95rem;
            flex: 1;
            color: var(--duran-texto-principal);
        }

        .search-container input::placeholder {
            color: var(--duran-texto-secundario);
        }

        .search-container i {
            color: var(--duran-azul-brillante);
            font-size: 1.1rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--duran-azul-oscuro);
            margin: 0;
            text-shadow: 0 2px 4px rgba(10, 31, 68, 0.08);
        }

        .btn-group-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-custom {
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .btn-custom:active {
            transform: translateY(0);
        }

        .btn-inicio {
            background: linear-gradient(90deg, var(--duran-azul-medio), var(--duran-azul-oscuro));
            color: white;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .btn-inicio:hover {
            background: linear-gradient(90deg, var(--duran-azul-oscuro), #051a38);
            color: white;
            text-decoration: none;
        }

        .btn-success-custom {
            background: linear-gradient(90deg, var(--duran-verde), #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-success-custom:hover {
            background: linear-gradient(90deg, #059669, #047857);
            color: white;
            text-decoration: none;
        }

        .btn-danger-custom {
            background: linear-gradient(90deg, var(--duran-rojo), #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger-custom:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            color: white;
            text-decoration: none;
        }

        .alert-custom {
            border: none;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15);
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border-left: 4px solid var(--duran-verde);
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #0c4a6e;
            border-left: 4px solid var(--duran-azul-brillante);
        }

        .table-container {
            background: var(--duran-blanco);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--duran-sombra);
            border: 1px solid rgba(30, 58, 138, 0.08);
            margin-bottom: 30px;
        }

        .table-custom {
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .table-custom thead {
            background: linear-gradient(90deg, var(--duran-azul-oscuro), #102c63);
            color: white;
        }

        .table-custom thead th {
            border: none;
            padding: 18px 16px;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: inherit;
        }

        .table-custom tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s ease;
        }

        .table-custom tbody tr:hover {
            background-color: #f9fafb;
        }

        .table-custom tbody td {
            padding: 16px;
            vertical-align: middle;
        }

        .table-custom tbody td:first-child {
            font-weight: 600;
            color: var(--duran-azul-oscuro);
        }

        .price-badge {
            display: inline-block;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #78350f;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .stock-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .stock-alto {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-medio {
            background: #fed7aa;
            color: #92400e;
        }

        .stock-bajo {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-edit {
            background: linear-gradient(90deg, var(--duran-naranja), #f97316);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: linear-gradient(90deg, #f97316, #ea580c);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .btn-delete {
            background: linear-gradient(90deg, var(--duran-rojo), #dc2626);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-delete:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--duran-blanco);
            border-radius: 14px;
            box-shadow: var(--duran-sombra);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.6;
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--duran-texto-principal);
            margin-bottom: 10px;
        }

        .empty-state-text {
            color: var(--duran-texto-secundario);
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .search-container {
                width: 100%;
                flex: 1 1 100%;
            }

            .btn-group-actions {
                width: 100%;
            }

            .btn-custom {
                flex: 1;
                justify-content: center;
            }

            .table-custom thead th,
            .table-custom tbody td {
                padding: 12px 8px;
                font-size: 0.85rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-edit, .btn-delete {
                width: 100%;
                text-align: center;
            }
        }

    </style>
</head>
<body>
    <?php include '../incluir/encabezado.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-box-open"></i> Gestión de Productos</h1>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="buscadorProductos" placeholder="Buscar por nombre..." autocomplete="off">
            </div>
            <div class="btn-group-actions">
                <a href="../index.php" class="btn-custom btn-inicio"><i class="fas fa-home"></i> Volver al inicio</a>
                <button type="button" onclick="abrirModalAgregar()" class="btn-custom btn-success-custom"><i class="fas fa-plus"></i> Agregar Producto</button>
            </div>
        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-custom alert-info">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table-custom w-100">
                <thead>
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
                    <?php 
                    if (!empty($productos)) {
                        include '../Vista/productos/tabla_productos.php';
                    } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--duran-texto-secundario);"><i class="fas fa-inbox"></i> No hay productos disponibles</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Agregar Producto -->
    <div class="modal-overlay" id="modalAgregar" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h1><i class="fas fa-plus-circle"></i> Nuevo Producto</h1>
                <button class="modal-close" onclick="cerrarModalAgregar()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="formAgregarProducto" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nombre"><i class="fas fa-tag"></i> Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Laptop Dell XPS" required minlength="3" maxlength="255">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="precio"><i class="fas fa-money-bill"></i> Precio (Bs.)</label>
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" placeholder="1499.99" required min="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="stock"><i class="fas fa-boxes"></i> Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" placeholder="50" required min="0">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="id_marca"><i class="fas fa-building"></i> Marca</label>
                                <select class="form-control" id="id_marca" name="id_marca" required>
                                    <option value="">Seleccionar...</option>
                                    <?php 
                                    $marcas = $conexion->query("SELECT id_marca, nombre FROM Marca ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
                                    foreach ($marcas as $marca): ?>
                                        <option value="<?php echo (int)$marca['id_marca']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="id_industria"><i class="fas fa-industry"></i> Industria</label>
                                <select class="form-control" id="id_industria" name="id_industria" required>
                                    <option value="">Seleccionar...</option>
                                    <?php 
                                    $industrias = $conexion->query("SELECT id_industria, nombre FROM Industria ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
                                    foreach ($industrias as $industria): ?>
                                        <option value="<?php echo (int)$industria['id_industria']; ?>"><?php echo htmlspecialchars($industria['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="id_categoria"><i class="fas fa-list"></i> Categoría</label>
                                <select class="form-control" id="id_categoria" name="id_categoria" required>
                                    <option value="">Seleccionar...</option>
                                    <?php 
                                    $categorias = $conexion->query("SELECT id_categoria, nombre FROM Categoria ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
                                    foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo (int)$categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="id_proveedor"><i class="fas fa-truck"></i> Proveedor</label>
                                <select class="form-control" id="id_proveedor" name="id_proveedor">
                                    <option value="">Opcional</option>
                                    <?php 
                                    $proveedores = $conexion->query("SELECT id_proveedor, nombre FROM Proveedor ORDER BY nombre")?->fetch_all(MYSQLI_ASSOC) ?? [];
                                    foreach ($proveedores as $proveedor): ?>
                                        <option value="<?php echo (int)$proveedor['id_proveedor']; ?>"><?php echo htmlspecialchars($proveedor['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="imagen"><i class="fas fa-image"></i> Imagen</label>
                                <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Detalles del producto..." required minlength="5" rows="3" maxlength="1000"></textarea>
                    </div>

                    <div id="imagenPreview" style="display: none;" class="preview-container">
                        <img id="previsualizacion">
                    </div>

                    <div id="alertaError" class="alert alert-danger" style="display: none;"></div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-agregar">
                            <i class="fas fa-check"></i> Agregar
                        </button>
                        <button type="button" class="btn-cancelar" onclick="cerrarModalAgregar()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
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
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(10, 31, 68, 0.3);
            max-width: 720px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 28px;
            border-bottom: 2px solid #f0f0f0;
            position: sticky;
            top: 0;
            background: var(--duran-blanco);
            border-radius: 12px 12px 0 0;
        }

        .modal-header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--duran-azul-oscuro);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.6rem;
            cursor: pointer;
            color: var(--duran-texto-secundario);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: var(--duran-azul-oscuro);
        }

        .modal-body {
            padding: 24px 28px 28px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .form-col {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .form-group {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 700;
            color: var(--duran-azul-oscuro);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control,
        .form-control-file,
        select.form-control {
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: var(--duran-texto-principal);
            background: var(--duran-blanco);
            font-family: inherit;
            width: 100%;
            box-sizing: border-box;
            line-height: 1.4;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            padding-right: 36px;
            cursor: pointer;
        }

        .form-control:focus,
        select.form-control:focus {
            border-color: var(--duran-azul-brillante);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        textarea.form-control {
            grid-column: 1 / -1;
            resize: vertical;
            min-height: 90px;
        }

        .form-control-file {
            padding: 12px;
            cursor: pointer;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(59, 130, 246, 0.02));
            border: 1.5px dashed var(--duran-azul-brillante);
        }

        .preview-container {
            grid-column: 1 / -1;
            text-align: center;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-top: 12px;
        }

        #previsualizacion {
            max-width: 100%;
            max-height: 280px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(10, 31, 68, 0.15);
        }

        .form-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 28px;
        }

        .btn-agregar {
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px 24px;
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
        }

        .btn-cancelar {
            background: linear-gradient(90deg, #e5e7eb, #d1d5db);
            color: var(--duran-texto-principal);
            border: none;
            border-radius: 8px;
            padding: 13px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cancelar:hover {
            background: linear-gradient(90deg, #d1d5db, #b3b9c2);
            transform: translateY(-2px);
        }

        .alert-danger {
            padding: 14px 16px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            padding: 14px 16px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .modal-content {
                max-width: 100%;
            }

            .form-buttons {
                grid-template-columns: 1fr;
            }

            .modal-header {
                padding: 20px 20px;
            }

            .modal-body {
                padding: 20px;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Funciones del Modal
        function abrirModalAgregar() {
            document.getElementById('modalAgregar').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalAgregar() {
            document.getElementById('modalAgregar').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('formAgregarProducto').reset();
            document.getElementById('imagenPreview').style.display = 'none';
            document.getElementById('alertaError').style.display = 'none';
        }

        // Cerrar modal al hacer click fuera del contenido
        document.getElementById('modalAgregar').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalAgregar();
            }
        });

        // Tecla ESC para cerrar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalAgregar();
            }
        });

        // Preview de imagen
        document.getElementById('imagen').addEventListener('change', function(e) {
            const archivo = e.target.files[0];
            if (archivo) {
                // Validar que sea una imagen
                if (!archivo.type.startsWith('image/')) {
                    alert('Por favor selecciona una imagen válida');
                    this.value = '';
                    return;
                }

                // Validar tamaño (5MB)
                if (archivo.size > 5 * 1024 * 1024) {
                    alert('La imagen no puede exceder 5MB');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('previsualizacion').src = event.target.result;
                    document.getElementById('imagenPreview').style.display = 'block';
                };
                reader.readAsDataURL(archivo);
            } else {
                document.getElementById('imagenPreview').style.display = 'none';
            }
        });

        // Enviar formulario
        document.getElementById('formAgregarProducto').addEventListener('submit', function(e) {
            e.preventDefault();

            const nombre = document.getElementById('nombre').value.trim();
            const descripcion = document.getElementById('descripcion').value.trim();
            const precio = parseFloat(document.getElementById('precio').value);
            const stock = parseInt(document.getElementById('stock').value);
            const imagen = document.getElementById('imagen');
            const alertaError = document.getElementById('alertaError');

            // Limpiar alerta anterior
            alertaError.style.display = 'none';

            // Validaciones más estrictas
            if (nombre.length < 3) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> El nombre debe tener al menos 3 caracteres';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            if (descripcion.length < 5) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> La descripción debe tener al menos 5 caracteres';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            if (precio <= 0) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> El precio debe ser mayor a 0';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            if (stock < 0) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> El stock no puede ser negativo';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            if (imagen.files.length === 0) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Debes seleccionar una imagen';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            // Verificar que los dropdowns tengan valores
            if (!document.getElementById('id_marca').value) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Selecciona una marca';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            if (!document.getElementById('id_industria').value) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Selecciona una industria';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            if (!document.getElementById('id_categoria').value) {
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Selecciona una categoría';
                alertaError.classList.remove('alert-success');
                alertaError.classList.add('alert-danger');
                alertaError.style.display = 'block';
                return false;
            }

            // Enviar formulario con AJAX
            const formData = new FormData(this);

            fetch('agregar_producto_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    // Mostrar mensaje de éxito
                    alertaError.className = 'alert-success';
                    alertaError.innerHTML = '<i class="fas fa-check-circle"></i> ¡Producto agregado con éxito!';
                    alertaError.style.display = 'block';

                    // Cerrar modal después de 1.5 segundos y recargar
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alertaError.className = 'alert-danger';
                    alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Error al agregar el producto');
                    alertaError.style.display = 'block';
                }
            })
            .catch(error => {
                alertaError.className = 'alert-danger';
                alertaError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error de conexión: ' + error.message;
                alertaError.style.display = 'block';
            });
        });

        // Buscador de productos
        document.getElementById('buscadorProductos').addEventListener('keyup', function() {
            const busqueda = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.table-custom tbody tr');
            let productosEncontrados = 0;

            filas.forEach(fila => {
                const nombre = fila.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const descripcion = fila.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (nombre.includes(busqueda) || descripcion.includes(busqueda) || busqueda === '') {
                    fila.style.display = '';
                    if (busqueda !== '') productosEncontrados++;
                } else {
                    fila.style.display = 'none';
                }
            });

            // Mostrar mensaje si no hay resultados
            const tbody = document.querySelector('.table-custom tbody');
            const mensajeVacio = tbody.querySelector('.mensaje-vacio');
            
            if (busqueda !== '' && productosEncontrados === 0) {
                if (!mensajeVacio) {
                    const tr = document.createElement('tr');
                    tr.className = 'mensaje-vacio';
                    tr.innerHTML = '<td colspan="6" style="text-align: center; padding: 40px; color: var(--duran-texto-secundario);"><i class="fas fa-search"></i> No se encontraron productos</td>';
                    tbody.appendChild(tr);
                }
            } else if (mensajeVacio) {
                mensajeVacio.remove();
            }
        });
    </script>
</body>
</html>