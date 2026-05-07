<?php
// Incluir dependencias primero (antes de session_start)
include 'incluir/conexion.php';
include 'incluir/facturacion.php';
include 'incluir/ventas.php';
require_once 'Controlador/CompraControlador.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

// Manejar solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Verificar acción
$accion = $_POST['accion'] ?? '';

try {
    
    switch ($accion) {
        
        case 'procesar_compra':
            // FLUJO COMPLETO: VALIDAR → REGISTRAR VENTA → GENERAR FACTURA
            
            // 1. Obtener carrito
            $compraControlador = new CompraControlador($conexion);
            if (!$compraControlador->asegurarEstructura()) {
                throw new Exception('No se pudo preparar la estructura del carrito');
            }
            
            $sessionId = session_id();
            $carritoPersistido = $compraControlador->obtenerCarrito($sessionId);
            
            if (empty($carritoPersistido)) {
                throw new Exception('El carrito está vacío');
            }
            
            // 2. Obtener datos del formulario
            $nombreCliente = trim($_POST['nombre_cliente'] ?? '');
            $correoCliente = trim($_POST['correo_cliente'] ?? '');
            $telefonoCliente = trim($_POST['telefono_cliente'] ?? '');
            $direccionCliente = trim($_POST['direccion_cliente'] ?? '');
            $metodoPago = trim($_POST['metodo_pago'] ?? '');
            
            // Validar correo
            $correoValido = filter_var($correoCliente, FILTER_VALIDATE_EMAIL) !== false;
            $correoFactura = $correoValido ? $correoCliente : 'sin-correo@local';
            
            // 3. Obtener formas de pago disponibles
            $formasPago = obtener_formas_pago_disponibles();
            if (!array_key_exists($metodoPago, $formasPago)) {
                throw new Exception('Selecciona una forma de pago válida');
            }
            
            // 4. Preparar estructura de ventas/pagos
            $detalleVentas = null;
            if (!asegurar_estructura_ventas_pago($conexion, $detalleVentas)) {
                throw new Exception('No se pudo preparar la estructura de ventas/pagos: ' . $detalleVentas);
            }
            
            // 5. Preparar tablas de facturación
            if (!asegurar_tablas_facturacion($conexion)) {
                throw new Exception('No se pudieron preparar las tablas de facturación');
            }
            
            // 6. Obtener detalle del carrito
            [$itemsFactura, $subtotal] = obtener_detalle_carrito($conexion, $carritoPersistido);
            
            if (empty($itemsFactura)) {
                throw new Exception('No se encontraron productos válidos en el carrito');
            }
            
            // 7. Calcular totales
            $impuesto = 0.00;
            $total = $subtotal + $impuesto;
            
            // 8. Registrar venta y pago
            $venta = registrar_venta_con_pago($conexion, $itemsFactura, $metodoPago, $detalleVentas);
            
            if ($venta === null) {
                throw new Exception('No se pudo registrar la venta/pago: ' . $detalleVentas);
            }
            
            // 9. Registrar factura
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
                throw new Exception('La venta se registró, pero no se pudo generar la factura');
            }
            
            // 10. Enviar correo si es válido
            $htmlFactura = construir_html_factura($factura);
            if ($correoValido) {
                $correoEnviado = enviar_factura_correo($correoCliente, $factura['numero_factura'], $htmlFactura);
            } else {
                $correoEnviado = false;
            }
            
            // 11. Guardar en sesión y vaciar carrito
            $_SESSION['ultima_factura'] = $factura;
            $_SESSION['ultima_factura']['correo_enviado'] = $correoEnviado;
            $_SESSION['ultima_factura']['venta'] = $venta;
            
            $compraControlador->vaciarCarrito($sessionId);
            $_SESSION['carrito'] = [];
            
            // 12. Respuesta exitosa
            echo json_encode([
                'ok' => true,
                'mensaje' => 'Compra procesada exitosamente',
                'factura_numero' => $factura['numero_factura'],
                'redirect' => 'pago_exitoso.php'
            ]);
            break;
        
        case 'validar_pago':
            // Validar datos de pago antes de procesar
            $metodo = $_POST['metodo_pago'] ?? '';
            
            if (empty($metodo)) {
                throw new Exception('Seleccione un método de pago');
            }
            
            // Validaciones específicas por método
            if ($metodo === 'tarjeta') {
                $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
                if (strlen($card_number) !== 16 || !is_numeric($card_number)) {
                    throw new Exception('Número de tarjeta inválido');
                }
                $cvv = $_POST['cvv'] ?? '';
                if (strlen($cvv) !== 3 || !is_numeric($cvv)) {
                    throw new Exception('CVV inválido');
                }
            }
            
            echo json_encode(['ok' => true, 'mensaje' => 'Validación exitosa']);
            break;
        
        case 'obtener_formas_pago':
            // Obtener formas de pago disponibles
            $formas = obtener_formas_pago_disponibles();
            echo json_encode(['ok' => true, 'formas_pago' => $formas]);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

exit();
?>
