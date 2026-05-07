<?php
include 'incluir/conexion.php';
include 'incluir/facturacion.php';
include 'incluir/ventas.php';
require_once 'Controlador/CompraControlador.php';

session_start();

$compraControlador = new CompraControlador($conexion);
if (!$compraControlador->asegurarEstructura()) {
    die('No se pudo preparar la estructura del carrito en la base de datos.');
}

$sessionId = session_id();
$carritoPersistido = $compraControlador->obtenerCarrito($sessionId);
$carritoSesion = $_SESSION['carrito'] ?? [];

if (!is_array($carritoSesion)) {
    $carritoSesion = [];
}

$compraControlador->sincronizarSesionDesdeBd($sessionId, $carritoSesion);

if (empty($carritoPersistido)) {
    header("Location: carrito.php");
    exit();
}

$error = '';
$formasPago = obtener_formas_pago_disponibles();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreCliente = trim($_POST['nombre_cliente'] ?? '');
    $correoCliente = trim($_POST['correo_cliente'] ?? '');
    $telefonoCliente = trim($_POST['telefono_cliente'] ?? '');
    $direccionCliente = trim($_POST['direccion_cliente'] ?? '');
    $metodoPago = trim($_POST['metodo_pago'] ?? '');

    $correoValido = filter_var($correoCliente, FILTER_VALIDATE_EMAIL) !== false;
    $correoFactura = $correoValido ? $correoCliente : 'sin-correo@local';

    if ($error === '' && !array_key_exists($metodoPago, $formasPago)) {
        $error = 'Selecciona una forma de pago válida.';
    }

    $detalleVentas = null;

    if ($error === '' && !asegurar_estructura_ventas_pago($conexion, $detalleVentas)) {
        $error = 'No se pudo preparar la estructura de ventas/pagos: ' . $detalleVentas;
    }

    if ($error === '' && !asegurar_tablas_facturacion($conexion)) {
        $error = 'No se pudieron preparar las tablas de facturación.';
    }

    if ($error === '') {

        [$itemsFactura, $subtotal] = obtener_detalle_carrito($conexion, $carritoPersistido);

        if (empty($itemsFactura)) {
            $error = 'No se encontraron productos válidos en el carrito.';
        }

        if ($error === '') {

            $impuesto = 0.00;
            $total = $subtotal + $impuesto;

            $venta = registrar_venta_con_pago($conexion, $itemsFactura, $metodoPago, $detalleVentas);

            if ($venta === null) {
                $error = 'No se pudo registrar la venta/pago.';
            } else {

                $factura = registrar_factura(
                    $conexion,
                    $correoFactura,
                    $nombreCliente,
                    $telefonoCliente,
                    $direccionCliente,
                    $itemsFactura,
                    $subtotal,
                    $impuesto,
                    $total
                );

                if ($factura === null) {
                    $error = 'La venta se registró, pero no se pudo generar la factura.';
                } else {

                    $htmlFactura = construir_html_factura($factura);

                    if ($correoValido) {
                        $correoEnviado = enviar_factura_correo($correoCliente, $factura['numero_factura'], $htmlFactura);
                    } else {
                        $correoEnviado = false;
                        establecer_error_correo('No se ingresó correo.');
                    }

                    $_SESSION['ultima_factura'] = $factura;
                    $_SESSION['ultima_factura']['correo_enviado'] = $correoEnviado;
                    $_SESSION['ultima_factura']['venta'] = $venta;

                    $compraControlador->vaciarCarrito($sessionId);
                    $_SESSION['carrito'] = [];

                    header("Location: pago_exitoso.php");
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Finalizar Compra - Duran</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
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
        --duran-primary: #1e40af;
        --duran-secondary: #dc2626;
        --duran-accent: #16a34a;
        --duran-light: #f8fafc;
        --duran-border: #e2e8f0;
        --duran-text: #1e293b;
        --duran-text-muted: #64748b;
    }

    body {
        background-color: #f1f5f9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--duran-text);
        min-height: 100vh;
    }

    .checkout-wrapper {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* ENCABEZADO */
    .checkout-header {
        background: linear-gradient(135deg, var(--duran-primary) 0%, #1e3a8a 100%);
        color: white;
        padding: 40px 30px;
        border-radius: 12px;
        margin-bottom: 40px;
        box-shadow: 0 4px 15px rgba(30, 64, 175, 0.2);
    }

    .checkout-header h1 {
        font-size: 2.5rem;
        font-weight: 900;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .checkout-header p {
        margin-top: 10px;
        font-size: 1.05rem;
        opacity: 0.95;
    }

    /* CONTENEDOR PRINCIPAL */
    .checkout-main {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
        align-items: start;
    }

    /* SECCIÓN IZQUIERDA - FORMULARIO */
    .checkout-form-section {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    /* TARJETAS DE INFORMACIÓN */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 5px solid var(--duran-primary);
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .info-card.error-card {
        border-left-color: var(--duran-secondary);
        background: #fef2f2;
        border: 2px solid #fecaca;
    }

    .info-card h3 {
        font-size: 1.3rem;
        font-weight: 800;
        margin-bottom: 20px;
        color: var(--duran-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-card h3 i {
        font-size: 1.5rem;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    label {
        display: block;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--duran-text);
        font-size: 0.95rem;
    }

    label i {
        color: var(--duran-secondary);
        margin-right: 5px;
    }

    input, select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--duran-border);
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
        background: white;
    }

    input:focus, select:focus {
        outline: none;
        border-color: var(--duran-primary);
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        background: white;
    }

    input:valid {
        border-color: var(--duran-accent);
    }

    select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%231e40af' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 35px;
        cursor: pointer;
    }

    .error-message {
        background: #fee2e2;
        border: 2px solid #fca5a5;
        color: #991b1b;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .error-message i {
        font-size: 1.2rem;
    }

    /* BOTONES */
    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-checkout {
        flex: 1;
        padding: 14px 24px;
        border-radius: 8px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-checkout-primary {
        background: linear-gradient(135deg, var(--duran-primary) 0%, #1e3a8a 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);
    }

    .btn-checkout-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(30, 64, 175, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-checkout-secondary {
        background: white;
        color: var(--duran-primary);
        border: 2px solid var(--duran-primary);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .btn-checkout-secondary:hover {
        transform: translateY(-2px);
        background: var(--duran-light);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
        text-decoration: none;
        color: var(--duran-primary);
    }

    /* SECCIÓN DERECHA - RESUMEN */
    .checkout-summary {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 20px;
    }

    .summary-header {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--duran-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--duran-border);
    }

    .summary-header i {
        font-size: 1.5rem;
    }

    .summary-items {
        margin-bottom: 20px;
        max-height: 300px;
        overflow-y: auto;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid var(--duran-border);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-weight: 700;
        color: var(--duran-text);
        margin-bottom: 3px;
    }

    .item-qty {
        font-size: 0.85rem;
        color: var(--duran-text-muted);
    }

    .item-price {
        font-weight: 800;
        color: var(--duran-primary);
        font-size: 1.05rem;
        white-space: nowrap;
        margin-left: 10px;
    }

    .summary-divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--duran-border), transparent);
        margin: 20px 0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        font-size: 1rem;
    }

    .summary-row.total {
        background: linear-gradient(135deg, var(--duran-primary) 0%, #1e3a8a 100%);
        color: white;
        padding: 16px;
        border-radius: 8px;
        font-weight: 900;
        font-size: 1.2rem;
        margin-top: 15px;
    }

    .summary-row label {
        font-weight: 700;
        color: var(--duran-text);
        margin: 0;
    }

    .summary-row.total label {
        color: white;
    }

    .summary-value {
        font-weight: 700;
        color: var(--duran-text);
    }

    .summary-row.total .summary-value {
        color: white;
    }

    /* TARJETA DE SEGURIDAD */
    .security-info {
        background: #f0fdf4;
        border: 2px solid var(--duran-accent);
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #15803d;
    }

    .security-info i {
        font-size: 1.3rem;
    }

    /* RESPONSIVIDAD */
    @media(max-width: 1024px) {
        .checkout-main {
            grid-template-columns: 1fr;
        }

        .checkout-summary {
            position: static;
        }

        .checkout-header h1 {
            font-size: 2rem;
        }
    }

    @media(max-width: 768px) {
        .checkout-wrapper {
            margin: 20px auto;
        }

        .checkout-header {
            padding: 25px 20px;
            margin-bottom: 30px;
        }

        .checkout-header h1 {
            font-size: 1.6rem;
        }

        .info-card {
            padding: 20px;
        }

        .info-card h3 {
            font-size: 1.1rem;
        }

        .button-group {
            flex-direction: column;
            gap: 10px;
        }

        .btn-checkout {
            width: 100%;
        }
    }

    /* SCROLL PERSONALIZADO */
    .summary-items::-webkit-scrollbar {
        width: 6px;
    }

    .summary-items::-webkit-scrollbar-track {
        background: var(--duran-light);
        border-radius: 3px;
    }

    .summary-items::-webkit-scrollbar-thumb {
        background: var(--duran-border);
        border-radius: 3px;
    }

    .summary-items::-webkit-scrollbar-thumb:hover {
        background: var(--duran-text-muted);
    }

    /* TARJETA DE CRÉDITO */
    .payment-method-section {
        display: none;
    }

    .payment-method-section.active {
        display: block;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* TARJETA SIMULADA */
    .card-preview {
        background: linear-gradient(135deg, var(--duran-primary) 0%, #1e3a8a 100%);
        border-radius: 12px;
        padding: 25px;
        color: white;
        margin-bottom: 20px;
        height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(30, 64, 175, 0.3);
    }

    .card-preview::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .card-logo {
        font-size: 2rem;
        font-weight: 900;
        opacity: 0.3;
        position: absolute;
        top: 10px;
        right: 15px;
    }

    .card-number {
        font-size: 1.5rem;
        letter-spacing: 2px;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        margin-top: 20px;
        position: relative;
        z-index: 1;
    }

    .card-details {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        position: relative;
        z-index: 1;
        margin-top: 15px;
    }

    .card-holder {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .card-expiry {
        text-align: center;
        font-size: 0.85rem;
    }

    .card-expiry label {
        display: block;
        font-size: 0.75rem;
        opacity: 0.7;
        margin-bottom: 3px;
    }

    .card-cvv {
        background: rgba(0, 0, 0, 0.2);
        padding: 3px 8px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }

    /* FORMULARIO DE TARJETA */
    .card-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    /* QR SECTION */
    .qr-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        padding: 30px;
        background: #f0f9ff;
        border-radius: 12px;
        border: 2px dashed var(--duran-primary);
    }

    .qr-code {
        width: 200px;
        height: 200px;
        background: white;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .qr-code img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .qr-instructions {
        text-align: center;
        color: var(--duran-text-muted);
        font-size: 0.95rem;
    }

    .qr-instructions strong {
        color: var(--duran-primary);
        display: block;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
</style>
</head>

<body>

<?php include 'incluir/encabezado.php'; ?>

<div class="checkout-wrapper">

    <!-- ENCABEZADO -->
    <div class="checkout-header">
        <h1><i class="fas fa-lock"></i> Finalizar Compra</h1>
        <p>Completa tus datos para confirmar tu pedido de manera segura</p>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="checkout-main">

        <!-- COLUMNA IZQUIERDA: FORMULARIO -->
        <div class="checkout-form-section">

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- DATOS PERSONALES -->
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Datos Personales</h3>
                
                <form method="POST" id="checkoutForm">
                    <div class="form-group">
                        <label><i class="fas fa-user-circle"></i> Nombre Completo</label>
                        <input type="text" name="nombre_cliente" placeholder="Juan Carlos Pérez" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                        <input type="email" name="correo_cliente" placeholder="tu@correo.com" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="tel" name="telefono_cliente" placeholder="+591 70000000" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Dirección de Entrega</label>
                        <input type="text" name="direccion_cliente" placeholder="Calle, número, ciudad" required>
                    </div>

                    <!-- MÉTODO DE PAGO -->
                    <div style="margin-top: 30px; padding-top: 25px; border-top: 2px solid var(--duran-border);">
                        <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--duran-primary); margin-bottom: 20px;">
                            <i class="fas fa-credit-card"></i> Método de Pago
                        </h4>

                        <div class="form-group">
                            <label><i class="fas fa-money-bill-wave"></i> Selecciona tu forma de pago</label>
                            <select name="metodo_pago" id="metodoPago" required>
                                <option value="">-- Selecciona una opción --</option>
                                <?php foreach ($formasPago as $k=>$v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- TARJETA DE CRÉDITO -->
                        <div class="payment-method-section" id="tarjetaSection">
                            <div class="card-preview" id="cardPreview">
                                <div class="card-logo"><i class="fas fa-credit-card"></i></div>
                                <div class="card-number" id="cardNumber">•••• •••• •••• ••••</div>
                                <div class="card-details">
                                    <div class="card-holder" id="cardHolder">NOMBRE TITULAR</div>
                                    <div class="card-expiry">
                                        <label>VÁLIDA HASTA</label>
                                        <span id="cardExpiry">MM/YY</span>
                                    </div>
                                    <div class="card-cvv" id="cardCVV">•••</div>
                                </div>
                            </div>

                            <div class="card-form">
                                <div class="form-group full">
                                    <label><i class="fas fa-hashtag"></i> Número de Tarjeta</label>
                                    <input type="text" name="card_number" id="cardNumberInput" placeholder="1234 5678 9012 3456" 
                                           maxlength="19" data-type="cc-number">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Nombre del Titular</label>
                                    <input type="text" name="card_holder" id="cardHolderInput" placeholder="Juan Pérez" 
                                           data-type="cc-name">
                                </div>

                                <div class="form-group">
                                    <label>Vencimiento</label>
                                    <input type="text" name="card_expiry" id="cardExpiryInput" placeholder="MM/YY" 
                                           maxlength="5" data-type="cc-exp">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-lock"></i> CVV</label>
                                    <input type="text" name="card_cvv" id="cardCVVInput" placeholder="123" 
                                           maxlength="4" data-type="cc-csc">
                                </div>
                            </div>
                        </div>

                        <!-- CÓDIGO QR -->
                        <div class="payment-method-section" id="qrSection">
                            <div class="qr-container">
                                <div class="qr-code">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=DURAN-PAYMENT-QR-001" 
                                         alt="Código QR de pago">
                                </div>
                                <div class="qr-instructions">
                                    <strong>Escanea este código QR</strong>
                                    Abre tu app bancaria o billetera digital y escanea el código para completar tu pago.
                                </div>
                            </div>
                        </div>

                        <div class="button-group">
                            <button class="btn-checkout btn-checkout-primary" type="submit">
                                <i class="fas fa-check-circle"></i> Confirmar Compra
                            </button>
                            <a href="carrito.php" class="btn-checkout btn-checkout-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </form>
            </div>

        </div>

        <!-- COLUMNA DERECHA: RESUMEN -->
        <div class="checkout-summary">
            <div class="summary-header">
                <i class="fas fa-shopping-cart"></i> Resumen de Compra
            </div>

            <div class="summary-items">
                <?php
                [$itemsFactura, $subtotal] = obtener_detalle_carrito($conexion, $carritoPersistido);
                $total = $subtotal;
                ?>

                <?php foreach ($itemsFactura as $item): ?>
                    <div class="summary-item">
                        <div class="item-info">
                            <div class="item-name"><?= htmlspecialchars($item['nombre_producto']) ?></div>
                            <div class="item-qty"><i class="fas fa-box"></i> x<?= (int)$item['cantidad'] ?></div>
                        </div>
                        <div class="item-price">Bs. <?= number_format($item['subtotal'],2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row">
                <label>Subtotal:</label>
                <span class="summary-value">Bs. <?= number_format($subtotal,2) ?></span>
            </div>

            <div class="summary-row">
                <label>Envío:</label>
                <span class="summary-value" style="color: var(--duran-accent);">Gratis</span>
            </div>

            <div class="summary-row total">
                <label><i class="fas fa-coins"></i> TOTAL A PAGAR:</label>
                <span class="summary-value">Bs. <?= number_format($total,2) ?></span>
            </div>

            <div class="security-info">
                <i class="fas fa-shield-alt"></i>
                <span>Tu compra está 100% segura</span>
            </div>
        </div>

    </div>

</div>

<?php include 'incluir/pie.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#checkoutForm');
        if (!form) return;

        const inputs = form.querySelectorAll('input, select');
        const metodoPago = document.getElementById('metodoPago');
        const tarjetaSection = document.getElementById('tarjetaSection');
        const qrSection = document.getElementById('qrSection');

        // EVENTOS DE MÉTODO DE PAGO
        metodoPago.addEventListener('change', function() {
            const metodo = this.value;
            
            // Ocultar todas las secciones
            tarjetaSection.classList.remove('active');
            qrSection.classList.remove('active');

            // Mostrar la sección correspondiente
            if (metodo === 'tarjeta') {
                tarjetaSection.classList.add('active');
            } else if (metodo === 'qr') {
                qrSection.classList.add('active');
            }
        });

        // ACTUALIZAR TARJETA EN TIEMPO REAL
        const cardNumberInput = document.getElementById('cardNumberInput');
        const cardHolderInput = document.getElementById('cardHolderInput');
        const cardExpiryInput = document.getElementById('cardExpiryInput');
        const cardCVVInput = document.getElementById('cardCVVInput');

        const cardNumber = document.getElementById('cardNumber');
        const cardHolder = document.getElementById('cardHolder');
        const cardExpiry = document.getElementById('cardExpiry');
        const cardCVV = document.getElementById('cardCVV');

        // Formatear número de tarjeta
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            if (value.length > 16) value = value.slice(0, 16);
            
            e.target.value = value.replace(/(\d{4})/g, '$1 ').trim();
            
            // Actualizar vista
            if (value) {
                let display = value.slice(0, 4).padEnd(16, '•');
                display = display.replace(/(\d{4})/g, '$1 ').trim();
                cardNumber.textContent = display;
            } else {
                cardNumber.textContent = '•••• •••• •••• ••••';
            }
        });

        // Actualizar nombre del titular
        cardHolderInput.addEventListener('input', function(e) {
            cardHolder.textContent = e.target.value.toUpperCase() || 'NOMBRE TITULAR';
        });

        // Formatear fecha de vencimiento
        cardExpiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
            cardExpiry.textContent = value || 'MM/YY';
        });

        // Actualizar CVV
        cardCVVInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').slice(0, 4);
            e.target.value = value;
            cardCVV.textContent = value ? '•'.repeat(value.length) : '•••';
        });

        // Validación en tiempo real
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value.trim() !== '') {
                    this.style.borderColor = 'var(--duran-accent)';
                    this.style.borderWidth = '2px';
                }
            });
        });

        // Envío del formulario
        form.addEventListener('submit', function(e) {
            const emailInput = form.querySelector('input[name="correo_cliente"]');
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);

            if (!validEmail) {
                e.preventDefault();
                emailInput.focus();
                emailInput.style.borderColor = 'var(--duran-secondary)';
                return;
            }

            // Validar tarjeta si es el método seleccionado
            const metodo = metodoPago.value;
            if (metodo === 'tarjeta') {
                if (!cardNumberInput.value.replace(/\s/g, '') || 
                    !cardHolderInput.value || 
                    !cardExpiryInput.value || 
                    !cardCVVInput.value) {
                    e.preventDefault();
                    alert('Por favor completa todos los datos de la tarjeta');
                    return;
                }
            }

            const submitBtn = form.querySelector('.btn-checkout-primary');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        });
    });
</script>

</body>
</html>
