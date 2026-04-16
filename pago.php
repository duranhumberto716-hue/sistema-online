<?php
// Incluir la conexión a la base de datos
include 'incluir/conexion.php';

// Iniciar sesión para acceder al carrito
session_start();

// Verificar si el carrito tiene productos
if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}

// Lógica para validar los productos en el carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $consulta = "SELECT * FROM productos WHERE id_producto = " . $item['id_producto'];
        $resultado = $conexion->query($consulta);
        if ($resultado->num_rows > 0) {
            $producto = $resultado->fetch_assoc();
            $total += $producto['precio'] * $item['cantidad'];
        }
    }

    // Aquí solo se muestra el total calculado sin escribir en otras tablas.
    // Puedes agregar otras acciones si es necesario.

    // Redirigir a una página de confirmación o mostrar un mensaje de éxito
    header("Location: pago_exitoso.php");
    exit();
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
            <div class="alert alert-info text-center">
                <p>Este es un proceso de pago simulado. Haz clic en "Completar Compra" para finalizar tu compra.</p>
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