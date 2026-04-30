<?php
include 'incluir/conexion.php';
include 'incluir/facturacion.php';

$token = trim($_GET['token'] ?? '');
$factura = null;
$error = '';

if ($token === '') {
    $error = 'Token de factura no proporcionado.';
} else {
    if (!asegurar_tablas_facturacion($conexion)) {
        $error = 'No se pudo preparar la estructura de facturacion.';
    } else {
        $factura = obtener_factura_por_token($conexion, $token);
        if ($factura === null) {
            $error = 'No se encontro una factura valida con ese QR.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura del Cliente</title>
    <link rel="stylesheet" href="recursos/css/estilos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .factura-fondo {
            background:
                radial-gradient(circle at 10% 20%, rgba(255, 209, 102, 0.18), transparent 35%),
                radial-gradient(circle at 90% 10%, rgba(6, 214, 160, 0.12), transparent 35%),
                linear-gradient(135deg, #f8fafc 0%, #eef3f8 45%, #e8f0ff 100%);
            min-height: 100vh;
        }

        .factura-card {
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 16px;
            box-shadow: 0 14px 34px rgba(17, 24, 39, 0.14);
            backdrop-filter: blur(3px);
            overflow: hidden;
        }

        .factura-header {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            letter-spacing: .2px;
        }
    </style>
</head>
<body class="factura-fondo">
    <?php include 'incluir/encabezado.php'; ?>

    <div class="container mt-5 mb-5">
        <h2 class="text-center">Consulta de Factura</h2>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger text-center mt-4"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($factura): ?>
            <div class="card factura-card mt-4">
                <div class="card-header factura-header">
                    <div class="d-flex align-items-center">
                        <img src="recursos/logo.svg" alt="Logo de la empresa" style="width:56px;height:56px;object-fit:cover;border-radius:10px;margin-right:12px;">
                        <div>
                            <strong>Factura:</strong> <?php echo htmlspecialchars($factura['numero_factura']); ?><br>
                            <small>Tienda en Línea</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Fecha:</strong> <?php echo htmlspecialchars($factura['fecha']); ?></p>
                    <p class="mb-3"><strong>Cliente:</strong> <?php echo htmlspecialchars($factura['nombre_cliente'] !== '' ? $factura['nombre_cliente'] : 'Cliente'); ?></p>
                    <p class="mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($factura['correo_cliente'] ?? ''); ?></p>
                    <p class="mb-1"><strong>Telefono:</strong> <?php echo htmlspecialchars($factura['telefono_cliente'] ?? ''); ?></p>
                    <p class="mb-2"><strong>Direccion:</strong> <?php echo htmlspecialchars($factura['direccion_cliente'] ?? ''); ?></p>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($factura['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre_producto']); ?></td>
                                        <td><?php echo (int)$item['cantidad']; ?></td>
                                        <td>$<?php echo number_format((float)$item['precio_unitario'], 2); ?></td>
                                        <td>$<?php echo number_format((float)$item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right">
                        <p class="mb-1"><strong>Subtotal:</strong> $<?php echo number_format((float)$factura['subtotal'], 2); ?></p>
                        <p class="mb-1"><strong>Impuesto:</strong> $<?php echo number_format((float)$factura['impuesto'], 2); ?></p>
                        <h5><strong>Total:</strong> $<?php echo number_format((float)$factura['total'], 2); ?></h5>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'incluir/pie.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
