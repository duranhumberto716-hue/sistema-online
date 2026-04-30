<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: inicio_sesion.php");
    exit();
}

include '../incluir/conexion.php';

if (!function_exists('esc_historial')) {
    function esc_historial(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }
}

$carritoItems = [];
$ventasItems = [];

$consultaCarrito = $conexion->query(
    "SELECT c.session_id, p.nombre, ci.cantidad, p.precio, (ci.cantidad * p.precio) AS subtotal, ci.fecha_actualizacion
     FROM carrito_items ci
     INNER JOIN carritos c ON c.id_carrito = ci.id_carrito
     INNER JOIN productos p ON p.id_producto = ci.id_producto
     ORDER BY ci.fecha_actualizacion DESC, ci.id_item DESC"
);

if ($consultaCarrito instanceof mysqli_result) {
    while ($fila = $consultaCarrito->fetch_assoc()) {
        $carritoItems[] = $fila;
    }
    $consultaCarrito->free();
}

$consultaVentas = $conexion->query(
    "SELECT v.numero_venta, v.fecha_venta, p.nombre, dv.cantidad, dv.precio_unitario, dv.subtotal, fp.nombre AS forma_pago
     FROM detalle_venta dv
     INNER JOIN ventas v ON v.id_venta = dv.id_venta
     INNER JOIN productos p ON p.id_producto = dv.id_producto
     INNER JOIN formas_pago fp ON fp.id_forma_pago = v.id_forma_pago
     ORDER BY v.fecha_venta DESC, dv.id_detalle_venta DESC"
);

if ($consultaVentas instanceof mysqli_result) {
    while ($fila = $consultaVentas->fetch_assoc()) {
        $ventasItems[] = $fila;
    }
    $consultaVentas->free();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Historial de Compras y Carrito</h2>
        <div class="text-right mb-3">
            <a href="panel_control.php" class="btn btn-secondary mr-2">Volver al panel</a>
            <a href="../index.php" class="btn btn-info">Volver al inicio</a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Productos agregados al carrito</div>
            <div class="card-body table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Sesión</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                            <th>Actualizado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($carritoItems)): ?>
                            <?php foreach ($carritoItems as $item): ?>
                                <tr>
                                    <td><?php echo esc_historial((string)$item['session_id']); ?></td>
                                    <td><?php echo esc_historial((string)$item['nombre']); ?></td>
                                    <td><?php echo (int)$item['cantidad']; ?></td>
                                    <td>$<?php echo number_format((float)$item['precio'], 2); ?></td>
                                    <td>$<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                                    <td><?php echo esc_historial((string)$item['fecha_actualizacion']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay productos en carrito guardados en la base de datos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">Productos comprados</div>
            <div class="card-body table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Número de venta</th>
                            <th>Fecha</th>
                            <th>Forma de pago</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ventasItems)): ?>
                            <?php foreach ($ventasItems as $item): ?>
                                <tr>
                                    <td><?php echo esc_historial((string)$item['numero_venta']); ?></td>
                                    <td><?php echo esc_historial((string)$item['fecha_venta']); ?></td>
                                    <td><?php echo esc_historial((string)$item['forma_pago']); ?></td>
                                    <td><?php echo esc_historial((string)$item['nombre']); ?></td>
                                    <td><?php echo (int)$item['cantidad']; ?></td>
                                    <td>$<?php echo number_format((float)$item['precio_unitario'], 2); ?></td>
                                    <td>$<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay ventas registradas en la base de datos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>