<?php
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Incluir la conexión a la base de datos
include '../incluir/conexion.php';
require_once '../Controlador/ProductoControlador.php';

header('Content-Type: application/json');

$productoControlador = new ProductoControlador($conexion);

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resultado = $productoControlador->crearProducto($_POST, $_FILES['imagen'] ?? []);

    if (!empty($resultado['ok'])) {
        echo json_encode(['ok' => true, 'mensaje' => 'Producto agregado con éxito']);
    } else {
        echo json_encode(['error' => (string)($resultado['error'] ?? 'No se pudo agregar el producto.')]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
exit();
?>
