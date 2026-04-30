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
    <style>
        :root {
            --duran-azul-oscuro: #0a1f44;
            --duran-azul-medio: #1e3a8a;
            --duran-azul-brillante: #3b82f6;
            --duran-gris-claro: #f3f4f6;
            --duran-texto-principal: #111827;
            --duran-texto-secundario: #6b7280;
            --duran-blanco: #ffffff;
            --duran-sombra: 0 20px 50px rgba(10, 31, 68, 0.14);
        }

        .section-title {
            color: var(--duran-texto-principal);
            font-weight: 800;
            margin-bottom: 0.35rem;
        }

        .section-subtitle {
            color: var(--duran-texto-secundario);
            margin-bottom: 1.75rem;
        }

        .encabezado-portada {
            margin-bottom: 1.5rem;
        }

        .titulo-portada {
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 800;
            color: #0f234f;
            margin-bottom: 1.8rem;
            text-align: center;
        }

        .product-card {
            border: 1px solid #e1e7f2;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 14px rgba(15, 35, 79, 0.08);
            transition: box-shadow 0.2s ease;
            background: var(--duran-blanco);
            height: 100%;
        }

        .product-card:hover {
            box-shadow: 0 10px 20px rgba(15, 35, 79, 0.12);
        }

        .product-card .card-body {
            padding: 1.1rem 1.15rem 1.25rem;
        }

        .product-card .card-title {
            color: var(--duran-texto-principal);
            font-weight: 800;
            margin-bottom: 0.35rem;
            font-size: 1.55rem;
        }

        .product-card .card-text {
            color: var(--duran-texto-secundario);
        }

        .product-image-wrap {
            height: 210px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.06), transparent 34%),
                linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            padding: 14px;
        }

        .product-image-wrap img {
            max-width: 86%;
            max-height: 86%;
            object-fit: contain;
            transition: transform 0.25s ease;
        }

        .product-card:hover .product-image-wrap img {
            transform: scale(1.03);
        }

        .product-price {
            color: var(--duran-azul-oscuro);
            font-weight: 800;
            font-size: 1.22rem;
        }

        .btn-product {
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.22);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .btn-product:hover,
        .btn-product:focus {
            background: linear-gradient(90deg, #2f6fed, #275fd1);
        }

    </style>
</head>
<body>
    <?php include 'incluir/encabezado.php'; ?>

    <div class="container mt-5">
        <h1 class="titulo-portada">Bienvenido a nuestra tienda en línea</h1>

        <?php
        $busqueda = trim($_GET['buscar'] ?? '');
        ?>

        <div class="row">
            <?php
            // Consultar productos de la base de datos
            if ($busqueda !== '') {
                $consulta = "SELECT * FROM productos WHERE nombre LIKE ? OR descripcion LIKE ? ORDER BY nombre";
                $sentencia = $conexion->prepare($consulta);
                $busquedaLike = '%' . $busqueda . '%';
                $sentencia->bind_param('ss', $busquedaLike, $busquedaLike);
                $sentencia->execute();
                $resultado = $sentencia->get_result();
            } else {
                $consulta = "SELECT * FROM productos ORDER BY nombre";
                $resultado = $conexion->query($consulta);
            }

            $imagenes_por_producto = [
                'televisor' => 'recursos/imagen1.jpg',
                'mouse' => 'recursos/mouse.jpg',
                'teclado' => 'recursos/teclado.jpg',
                'monitor' => 'recursos/monitor.jpg',
                'audifonos' => 'recursos/audifonos.jpg',
                'disco ssd' => 'recursos/Disco-SSD.jpg',
                'ssd' => 'recursos/Disco-SSD.jpg',
                'webcam' => 'recursos/Webcam.jpg',
                'silla gamer' => 'recursos/SILLA-GAMER.jpg',
                'silla' => 'recursos/SILLA-GAMER.jpg',
            ];

            if ($resultado->num_rows > 0) {
                $indice_producto = 0;
                while ($producto = $resultado->fetch_assoc()) {
                    // Prioridad: usar imagen Base64 de la BD si está disponible
                    $ruta_imagen = (string)($producto['imagen'] ?? '');
                    
                    // Si no hay imagen en BD, buscar archivos de fallback
                    if ($ruta_imagen === '' || (strpos($ruta_imagen, 'data:image') === false)) {
                        $nombre_imagen = basename((string)($producto['imagen'] ?? ''));
                        $rutas_posibles = [];
                        if ($nombre_imagen !== '') {
                            $rutas_posibles[] = 'recursos/imagenes/' . $nombre_imagen;
                            $rutas_posibles[] = 'recursos/' . $nombre_imagen;
                        }

                        $ruta_imagen = '';
                        $nombre_normalizado = strtolower(trim((string)($producto['nombre'] ?? '')));

                        if ($nombre_normalizado !== '' && isset($imagenes_por_producto[$nombre_normalizado])) {
                            $ruta_candidata = $imagenes_por_producto[$nombre_normalizado];
                            if (is_file(__DIR__ . '/' . $ruta_candidata)) {
                                $ruta_imagen = $ruta_candidata;
                            }
                        } elseif ($indice_producto === 0 && is_file(__DIR__ . '/recursos/imagen1.jpg')) {
                            $ruta_imagen = 'recursos/imagen1.jpg';
                        } elseif ($indice_producto === 1 && is_file(__DIR__ . '/recursos/mouse.jpg')) {
                            $ruta_imagen = 'recursos/mouse.jpg';
                        } elseif ($indice_producto === 2 && is_file(__DIR__ . '/recursos/teclado.jpg')) {
                            $ruta_imagen = 'recursos/teclado.jpg';
                        } elseif ($indice_producto === 3 && is_file(__DIR__ . '/recursos/monitor.jpg')) {
                            $ruta_imagen = 'recursos/monitor.jpg';
                        } elseif ($indice_producto === 4 && is_file(__DIR__ . '/recursos/audifonos.jpg')) {
                            $ruta_imagen = 'recursos/audifonos.jpg';
                        } elseif ($indice_producto === 5 && is_file(__DIR__ . '/recursos/Disco-SSD.jpg')) {
                            $ruta_imagen = 'recursos/Disco-SSD.jpg';
                        } elseif ($indice_producto === 6 && is_file(__DIR__ . '/recursos/Webcam.jpg')) {
                            $ruta_imagen = 'recursos/Webcam.jpg';
                        } elseif ($indice_producto === 7 && is_file(__DIR__ . '/recursos/SILLA-GAMER.jpg')) {
                            $ruta_imagen = 'recursos/SILLA-GAMER.jpg';
                        } else {
                            foreach ($rutas_posibles as $ruta) {
                                if (!empty($ruta) && is_file(__DIR__ . '/' . $ruta)) {
                                    $ruta_imagen = $ruta;
                                    break;
                                }
                            }
                        }
                    }

                    $estilo_contenedor_imagen = 'height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #ffffff;';
                    $estilo_imagen = 'max-width: 72%; max-height: 72%; object-fit: contain;';

                    if ($indice_producto === 2) {
                        $estilo_imagen = 'width: 100%; height: 100%; object-fit: cover;';
                    }

                    if ($ruta_imagen !== '') {
                        // Si es Base64, no crear link externo
                        if (strpos($ruta_imagen, 'data:image') === 0) {
                            $bloque_imagen = '<div class="card-img-top product-image-wrap">'
                                . '<img src="' . htmlspecialchars($ruta_imagen, ENT_QUOTES, 'UTF-8') . '" class="img-fluid" alt="' . htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') . '" style="' . $estilo_imagen . '">'
                                . '</div>';
                        } else {
                            // Archivo físico: crear link externo
                            $bloque_imagen = '<a href="' . htmlspecialchars($ruta_imagen, ENT_QUOTES, 'UTF-8') . '" target="_blank">'
                                . '<div class="card-img-top product-image-wrap">'
                                . '<img src="' . htmlspecialchars($ruta_imagen, ENT_QUOTES, 'UTF-8') . '" class="img-fluid" alt="' . htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') . '" style="' . $estilo_imagen . '">'
                                . '</div>'
                                . '</a>';
                        }
                    } else {
                        $bloque_imagen = '<div class="card-img-top product-image-wrap"></div>';
                    }

                    $indice_producto++;

                    echo '
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                            ' . $bloque_imagen . '
                            <div class="card-body">
                                <h5 class="card-title">' . $producto['nombre'] . '</h5>
                                <p class="card-text">' . $producto['descripcion'] . '</p>
                                <p class="card-text product-price">Precio: Bs. ' . number_format($producto['precio'], 2) . '</p>
                                <a href="carrito.php?accion=agregar&id=' . $producto['id_producto'] . '" class="btn btn-primary btn-product">Agregar al carrito</a>
                            </div>
                        </div>
                    </div>
                    ';
                }
            } else {
                if ($busqueda !== '') {
                    echo '<p class="text-center w-100">No se encontraron productos para esa búsqueda.</p>';
                } else {
                    echo '<p class="text-center w-100">No hay productos disponibles en este momento.</p>';
                }
            }

            if (isset($sentencia) && $sentencia instanceof mysqli_stmt) {
                $sentencia->close();
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