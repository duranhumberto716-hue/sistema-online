<?php
// Incluir la conexión a la base de datos
include 'incluir/conexion.php';

// Lógica para agregar, actualizar y eliminar productos del carrito
session_start();
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acciones del carrito
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_producto = (int)$_GET['id'];

    if ($_GET['accion'] == 'agregar') {
        // Agregar producto al carrito
        $encontrado = false;
        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id_producto'] == $id_producto) {
                $item['cantidad']++;
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $_SESSION['carrito'][] = ['id_producto' => $id_producto, 'cantidad' => 1];
        }
    } elseif ($_GET['accion'] == 'eliminar') {
        // Eliminar producto del carrito
        foreach ($_SESSION['carrito'] as $indice => $item) {
            if ($item['id_producto'] == $id_producto) {
                unset($_SESSION['carrito'][$indice]);
                break;
            }
        }
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
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
                    <?php
                    $total = 0;
                    if (!empty($_SESSION['carrito'])) {
                        foreach ($_SESSION['carrito'] as $item) {
                            $consulta = "SELECT * FROM productos WHERE id_producto = " . $item['id_producto'];
                            $resultado = $conexion->query($consulta);
                            if ($resultado->num_rows > 0) {
                                $producto = $resultado->fetch_assoc();
                                $subtotal = $producto['precio'] * $item['cantidad'];
                                $total += $subtotal;
                                echo '
                                <tr>
                                    <td>' . $producto['nombre'] . '</td>
                                    <td>' . $item['cantidad'] . '</td>
                                    <td>$' . number_format($producto['precio'], 2) . '</td>
                                    <td>$' . number_format($subtotal, 2) . '</td>
                                    <td>
                                        <a href="carrito.php?accion=eliminar&id=' . $item['id_producto'] . '" class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                                ';
                            }
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center">El carrito está vacío.</td></tr>';
                    }
                    ?>
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