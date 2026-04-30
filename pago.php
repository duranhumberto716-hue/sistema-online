<?php
// Incluir la conexión a la base de datos
include 'incluir/conexion.php';
include 'incluir/facturacion.php';
include 'incluir/ventas.php';
require_once 'Controlador/CompraControlador.php';

// Iniciar sesión para acceder al carrito
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

// Verificar si el carrito tiene productos
if (empty($carritoPersistido)) {
    header("Location: carrito.php");
    exit();
}

$error = '';
$formasPago = obtener_formas_pago_disponibles();

// Lógica para validar los productos en el carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCliente = trim($_POST['nombre_cliente'] ?? '');
    $correoCliente = trim($_POST['correo_cliente'] ?? '');
    $telefonoCliente = trim($_POST['telefono_cliente'] ?? '');
    $direccionCliente = trim($_POST['direccion_cliente'] ?? '');
    $metodoPago = trim($_POST['metodo_pago'] ?? '');

    $correoValido = filter_var($correoCliente, FILTER_VALIDATE_EMAIL) !== false;
    $correoFactura = $correoValido ? $correoCliente : 'sin-correo@local';

    if ($error === '' && !array_key_exists($metodoPago, $formasPago)) {
        $error = 'Selecciona una forma de pago valida.';
    }

    $detalleVentas = null;

    if ($error === '' && !asegurar_estructura_ventas_pago($conexion, $detalleVentas)) {
        $error = 'No se pudo preparar la estructura de ventas/pagos: ' . $detalleVentas;
    }

    if ($error === '' && !asegurar_tablas_facturacion($conexion)) {
        $error = 'No se pudieron preparar las tablas de facturacion.';
    }

    if ($error === '') {
        [$itemsFactura, $subtotal] = obtener_detalle_carrito($conexion, $carritoPersistido);

        if (empty($itemsFactura)) {
            $error = 'No se encontraron productos validos en el carrito.';
        }

        if ($error === '') {
            $impuesto = 0.00;
            $total = $subtotal + $impuesto;

            $venta = registrar_venta_con_pago($conexion, $itemsFactura, $metodoPago, $detalleVentas);

            if ($venta === null) {
                $error = 'No se pudo registrar la venta/pago: ' . ($detalleVentas ?? 'Error desconocido');
            }

            if ($error !== '') {
                // Evita continuar con facturacion si falla la venta.
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
                    $error = 'La venta se registro, pero no se pudo generar la factura.';
                } else {
                    $htmlFactura = construir_html_factura($factura);
                    if ($correoValido) {
                        $correoEnviado = enviar_factura_correo($correoCliente, $factura['numero_factura'], $htmlFactura);
                    } else {
                        $correoEnviado = false;
                        establecer_error_correo('No se ingreso correo en el proceso de pago.');
                    }

                    $_SESSION['ultima_factura'] = $factura;
                    $_SESSION['ultima_factura']['correo_enviado'] = $correoEnviado;
                    $_SESSION['ultima_factura']['correo_error'] = obtener_error_correo();
                    $_SESSION['ultima_factura']['venta'] = $venta;
                    $_SESSION['ultima_factura']['metodo_pago_codigo'] = $metodoPago;

                    // Vaciar carrito luego de finalizar la compra.
                    $compraControlador->vaciarCarrito($sessionId);
                    $_SESSION['carrito'] = [];

                    // Redirigir a una página de confirmación o mostrar un mensaje de éxito
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago</title>
    <link rel="stylesheet" href="recursos/css/estilos.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'incluir/encabezado.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Proceso de Pago</h1>
        <form method="POST" action="">
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="alert alert-info text-center">
                <p>Este es un proceso de pago simulado. Completa tus datos para generar tu factura.</p>
            </div>

            <div class="form-group">
                <label for="nombre_cliente">Nombre del cliente (opcional)</label>
                <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" placeholder="Nombre para la factura" value="<?php echo htmlspecialchars($_POST['nombre_cliente'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="correo_cliente">Correo electronico</label>
                <input type="email" class="form-control" id="correo_cliente" name="correo_cliente" placeholder="cliente@correo.com" value="<?php echo htmlspecialchars($_POST['correo_cliente'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="telefono_cliente">Numero de telefono</label>
                <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente" placeholder="Ej. 70000000" value="<?php echo htmlspecialchars($_POST['telefono_cliente'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="direccion_cliente">Direccion</label>
                <input type="text" class="form-control" id="direccion_cliente" name="direccion_cliente" placeholder="Direccion del cliente" value="<?php echo htmlspecialchars($_POST['direccion_cliente'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="metodo_pago">Forma de pago</label>
                <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                    <option value="">Selecciona una opcion</option>
                    <?php foreach ($formasPago as $codigo => $nombre): ?>
                        <option value="<?php echo htmlspecialchars($codigo); ?>" <?php echo (($_POST['metodo_pago'] ?? '') === $codigo) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-success">Completar Compra</button>
            </div>
        </form>
    </div>

    <?php include 'incluir/pie.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>