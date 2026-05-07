<?php
include 'incluir/conexion.php';
require_once 'Controlador/CompraControlador.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

$compraControlador = new CompraControlador($conexion);
$accion = $_POST['accion'] ?? '';
$id_producto = (int)($_POST['id_producto'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 1);
$sessionId = session_id();

try {
    
    if (!$compraControlador->asegurarEstructura()) {
        throw new Exception('Error al preparar estructura del carrito');
    }
    
    switch ($accion) {
        
        case 'agregar':
            if ($id_producto <= 0 || $cantidad < 1) {
                throw new Exception('Datos inválidos');
            }
            if ($compraControlador->agregarAlCarrito($sessionId, $id_producto, $cantidad)) {
                echo json_encode(['ok' => true, 'mens
                
                
                e' => 'Producto agregado']);
            } else {
                throw new Exception('No se pudo agregar el producto');
            }
            break;
        
        case 'eliminar':
            if ($id_producto <= 0) {
                throw new Exception('ID de producto inválido');
            }
            if ($compraControlador->eliminarDelCarrito($sessionId, $id_producto)) {
                echo json_encode(['ok' => true, 'mensaje' => 'Producto eliminado']);
            } else {
                throw new Exception('No se pudo eliminar el producto');
            }
            break;
        
        case 'actualizar':
            if ($id_producto <= 0 || $cantidad < 0) {
                throw new Exception('Datos inválidos');
            }
            if ($compraControlador->actualizarCantidadCarrito($sessionId, $id_producto, $cantidad)) {
                echo json_encode(['ok' => true, 'mensaje' => 'Cantidad actualizada']);
            } else {
                throw new Exception('No se pudo actualizar la cantidad');
            }
            break;
        
        case 'obtener':
            $carrito = $compraControlador->obtenerCarritoDetallado($sessionId);
            $total = 0.0;
            foreach ($carrito as $item) {
                $total += (float)$item['subtotal'];
            }
            echo json_encode(['ok' => true, 'items' => $carrito, 'total' => $total]);
            break;
        
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
exit();
?>
