<?php
session_start();

$factura = $_SESSION['ultima_factura'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Exitosa - Duran</title>
    <link rel="stylesheet" href="recursos/css/estilos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --success-primary: #10b981;
            --success-light: #ecfdf5;
            --dark: #1e293b;
            --light: #f1f5f9;
            --border: #e2e8f0;
        }

        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f1f5f9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .success-wrapper {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        /* ENCABEZADO DE ÉXITO */
        .success-header {
            text-align: center;
            margin-bottom: 50px;
            animation: slideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .header-top {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            position: relative;
        }

        .btn-print-header {
            position: absolute;
            right: 0;
            top: 0;
            padding: 8px 16px;
            font-size: 0.8rem;
            border-radius: 6px;
            background: white;
            color: var(--success-primary);
            border: 2px solid var(--success-primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print-header:hover {
            background: var(--success-light);
            transform: translateY(-2px);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--success-primary) 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 3.5rem;
            color: white;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
            animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-header h1 {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .success-header p {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .factura-number {
            background: var(--success-light);
            border: 2px solid var(--success-primary);
            color: var(--success-primary);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
            margin-top: 15px;
        }

        /* INFORMACIÓN COMPACTA */
        .customer-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 0.95rem;
            color: var(--dark);
            font-weight: 600;
        }

        /* RESUMEN DE COMPRA */
        .purchase-summary {
            background: white;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--success-primary);
        }

        .summary-title i {
            color: var(--success-primary);
            font-size: 1.8rem;
        }

        .items-table {
            width: 100%;
            margin-bottom: 25px;
        }

        .items-table thead {
            background: var(--light);
            border-bottom: 2px solid var(--border);
        }

        .items-table th {
            font-weight: 800;
            text-transform: uppercase;
            color: var(--dark);
            padding: 15px;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border: none;
        }

        .items-table td {
            padding: 18px 15px;
            border-bottom: 1px solid var(--border);
            color: var(--dark);
        }

        .items-table tbody tr:hover {
            background: var(--light);
            transition: all 0.2s ease;
        }

        .item-name {
            font-weight: 700;
            font-size: 1rem;
        }

        .item-price {
            font-weight: 800;
            color: var(--success-primary);
            font-size: 1.05rem;
        }

        /* TOTALES */
        .totals-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 20px;
            background: var(--light);
            border-radius: 8px;
            margin-top: 25px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: var(--dark);
        }

        .total-row.grand-total {
            background: linear-gradient(135deg, var(--success-primary) 0%, #059669 100%);
            color: white;
            padding: 18px 20px;
            border-radius: 8px;
            font-size: 1.3rem;
            margin-top: 10px;
        }

        /* BOTONES DE ACCIÓN */
        .action-buttons {
            display: none;
        }

        /* RESPONSIVIDAD */
        @media(max-width: 768px) {
            .success-header h1 {
                font-size: 2rem;
            }

            .customer-info {
                grid-template-columns: 1fr;
            }

            .purchase-summary {
                padding: 20px;
            }

            .items-table th,
            .items-table td {
                padding: 12px 8px;
                font-size: 0.9rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }

        /* IMPRESIÓN */
        @media print {
            body {
                background: white;
            }

            .action-buttons,
            .success-icon {
                display: none;
            }

            .purchase-summary {
                box-shadow: none;
                border: 1px solid var(--border);
            }
        }
    </style>
</head>

<body>

<?php include 'incluir/encabezado.php'; ?>

<div class="success-wrapper">

    <!-- ENCABEZADO DE ÉXITO -->
    <div class="success-header">
        <div class="header-top">
            <div></div>
            <button class="btn-print-header" onclick="window.print();">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>¡Compra Exitosa!</h1>
        <?php if ($factura): ?>
            <div class="factura-number">
                Factura: <?= htmlspecialchars($factura['numero_factura']) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($factura): ?>

        <div class="purchase-summary">
            <!-- INFORMACIÓN DEL CLIENTE -->
            <div class="customer-info">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-calendar"></i> Fecha de Compra</span>
                    <span class="info-value"><?= htmlspecialchars($factura['venta']['fecha_venta'] ?? $factura['fecha'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-user"></i> Cliente</span>
                    <span class="info-value"><?= htmlspecialchars($factura['nombre_cliente'] !== '' ? $factura['nombre_cliente'] : 'Cliente') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-credit-card"></i> Método de Pago</span>
                    <span class="info-value"><?= htmlspecialchars($factura['venta']['forma_pago'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-envelope"></i> Correo</span>
                    <span class="info-value"><?= htmlspecialchars($factura['correo_cliente'] ?? 'No proporcionado') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-phone"></i> Teléfono</span>
                    <span class="info-value"><?= htmlspecialchars($factura['telefono_cliente'] ?? 'No proporcionado') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-map-marker-alt"></i> Dirección</span>
                    <span class="info-value"><?= htmlspecialchars($factura['direccion_cliente'] ?? 'No proporcionada') ?></span>
                </div>
            </div>

            <div class="summary-title">
                <i class="fas fa-shopping-bag"></i>
                Detalle de Productos
                <span style="margin-left: auto; font-size: 0.9rem; color: var(--success-primary); font-weight: 600;">Factura: <?= htmlspecialchars($factura['numero_factura']) ?></span>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th style="text-align: center;">Cantidad</th>
                        <th style="text-align: right;">Precio Unitario</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factura['items'] as $item): ?>
                        <tr>
                            <td class="item-name"><?= htmlspecialchars($item['nombre_producto']) ?></td>
                            <td style="text-align: center;"><?= (int)$item['cantidad'] ?></td>
                            <td style="text-align: right;">Bs. <?= number_format((float)$item['precio_unitario'], 2) ?></td>
                            <td style="text-align: right;" class="item-price">Bs. <?= number_format((float)$item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>Bs. <?= number_format((float)$factura['subtotal'], 2) ?></span>
                </div>
                <div class="total-row">
                    <span>Impuesto:</span>
                    <span>Bs. <?= number_format((float)$factura['impuesto'], 2) ?></span>
                </div>
                <div class="total-row grand-total">
                    <span><i class="fas fa-coins"></i> TOTAL A PAGAR:</span>
                    <span>Bs. <?= number_format((float)$factura['total'], 2) ?></span>
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
