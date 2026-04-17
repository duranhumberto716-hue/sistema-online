<?php
// Incluir la conexión a la base de datos
include 'incluir/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Tienda en Línea</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'incluir/encabezado.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Bienvenido a nuestra tienda en línea</h1>
        <div class="row">
            <?php
            // Consultar productos de la base de datos
            $consulta = "SELECT * FROM productos";
            $resultado = $conexion->query($consulta);

            if ($resultado->num_rows > 0) {
                $indice_producto = 0;
                while ($producto = $resultado->fetch_assoc()) {
                    // Mantener imagen fija en el primer producto y usar BD para los demas
                    $nombre_imagen = basename((string)($producto['imagen'] ?? ''));
                    $rutas_posibles = [];
                    if ($nombre_imagen !== '') {
                        $rutas_posibles[] = 'recursos/imagenes/' . $nombre_imagen;
                        $rutas_posibles[] = 'recursos/' . $nombre_imagen;
                    }

                    $ruta_imagen = '';
                    if ($indice_producto === 0 && is_file(__DIR__ . '/recursos/imagen1.jpg')) {
                        $ruta_imagen = 'recursos/imagen1.jpg';
                    } else {
                        foreach ($rutas_posibles as $ruta) {
                            if (!empty($ruta) && is_file(__DIR__ . '/' . $ruta)) {
                                $ruta_imagen = $ruta;
                                break;
                            }
                        }
                    }

                    if ($ruta_imagen !== '') {
                        $bloque_imagen = '<a href="' . $ruta_imagen . '" target="_blank">'
                            . '<div class="card-img-top" style="height: 200px; overflow: hidden;">'
                            . '<img src="' . $ruta_imagen . '" class="img-fluid" alt="' . $producto['nombre'] . '" style="width: 100%; object-fit: cover; height: 100%;">'
                            . '</div>'
                            . '</a>';
                    } else {
                        $bloque_imagen = '<div class="card-img-top" style="height: 200px; background: #f1f3f5;"></div>';
                    }

                    $indice_producto++;

                    echo '
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            ' . $bloque_imagen . '
                            <div class="card-body">
                                <h5 class="card-title">' . $producto['nombre'] . '</h5>
                                <p class="card-text">' . $producto['descripcion'] . '</p>
                                <p class="card-text"><strong>Precio: $' . number_format($producto['precio'], 2) . '</strong></p>
                                <a href="carrito.php?accion=agregar&id=' . $producto['id_producto'] . '" class="btn btn-primary">Agregar al carrito</a>
                            </div>
                        </div>
                    </div>
                    ';
                }
            } else {
                echo '<p class="text-center">No hay productos disponibles en este momento.</p>';
            }
            ?>
        </div>
    </div>

    <?php include 'incluir/pie.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>