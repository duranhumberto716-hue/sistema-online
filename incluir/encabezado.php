<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda en Línea</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($basePath === '') {
        $basePath = '/';
    }

    $basePublica = $basePath;
    if ($basePublica !== '/' && substr($basePublica, -6) === '/admin') {
        $basePublica = substr($basePublica, 0, -6);
        if ($basePublica === '') {
            $basePublica = '/';
        }
    }

    $cantidadCarrito = 0;
    $sessionIdVista = '';

    if (session_status() === PHP_SESSION_ACTIVE) {
        $sessionIdVista = session_id();
    } elseif (isset($_COOKIE[session_name()])) {
        $sessionIdVista = (string)$_COOKIE[session_name()];
    }

    if ($sessionIdVista !== '') {
        include_once __DIR__ . '/conexion.php';

        if (isset($conexion) && $conexion instanceof mysqli) {
            $consultaCantidad = $conexion->prepare(
                'SELECT COALESCE(SUM(ci.cantidad), 0) AS total_items
                 FROM carritos c
                 INNER JOIN carrito_items ci ON ci.id_carrito = c.id_carrito
                 WHERE c.session_id = ?'
            );

            if ($consultaCantidad) {
                $consultaCantidad->bind_param('s', $sessionIdVista);
                if ($consultaCantidad->execute()) {
                    $resultadoCantidad = $consultaCantidad->get_result();
                    $filaCantidad = $resultadoCantidad ? $resultadoCantidad->fetch_assoc() : null;
                    $cantidadCarrito = (int)($filaCantidad['total_items'] ?? 0);
                }
                $consultaCantidad->close();
            }
        }
    }
    ?>

    <style>
        :root {
            --duran-azul-oscuro: #0a1f44;
            --duran-azul-medio: #1e3a8a;
            --duran-azul-brillante: #3b82f6;
            --duran-gris-claro: #f3f4f6;
            --duran-texto-principal: #111827;
            --duran-texto-secundario: #6b7280;
            --duran-blanco: #ffffff;
            --duran-sombra: 0 14px 40px rgba(10, 31, 68, 0.12);
        }

        body {
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 42%),
                linear-gradient(180deg, var(--duran-gris-claro) 0%, #e9eef7 100%);
            color: var(--duran-texto-principal);
        }

        .navbar-duran {
            background: linear-gradient(90deg, var(--duran-azul-oscuro) 0%, #102c63 100%);
            box-shadow: 0 8px 28px rgba(10, 31, 68, 0.22);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }

        .navbar-duran .navbar-brand,
        .navbar-duran .nav-link,
        .navbar-duran .navbar-toggler {
            color: var(--duran-gris-claro) !important;
        }

        .navbar-duran .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.03em;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-right: 1rem;
        }

        .navbar-brand-texto {
            white-space: nowrap;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }

        .navbar-buscador {
            flex: 1 1 520px;
            max-width: 680px;
            margin-right: 1.2rem;
        }

        .navbar-buscador .input-group {
            flex-wrap: nowrap;
        }

        .navbar-buscador .form-control {
            height: 52px;
            border: none;
            border-radius: 999px 0 0 999px;
            box-shadow: none;
            padding-left: 1.25rem;
            font-size: 1.05rem;
            background: var(--duran-azul-oscuro);
            color: #ffffff;
        }

        .navbar-buscador .form-control::placeholder {
            color: rgba(255, 255, 255, 0.85);
        }

        .navbar-buscador .form-control:focus {
            background: var(--duran-azul-oscuro);
            color: #ffffff;
            border: none;
            box-shadow: 0 0 0 0.2rem rgba(10, 31, 68, 0.28);
        }

        .navbar-buscador .btn {
            border-radius: 0 999px 999px 0;
            border: none;
            background: var(--duran-azul-oscuro);
            font-weight: 700;
            min-width: 126px;
            font-size: 1.05rem;
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }

        .navbar-buscador .btn:hover,
        .navbar-buscador .btn:focus {
            background: #0b2656;
        }

        .navbar-duran .nav-link {
            opacity: 0.92;
            transition: color 0.2s ease, opacity 0.2s ease;
        }

        .navbar-duran .nav-link:hover,
        .navbar-duran .nav-link:focus {
            color: var(--duran-blanco) !important;
            opacity: 1;
        }

        .navbar-duran .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.22);
        }

        .navbar-duran .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }

        .card,
        .factura-card {
            border: 1px solid rgba(30, 58, 138, 0.12);
            border-radius: 18px;
            box-shadow: var(--duran-sombra);
            overflow: hidden;
        }

        .card-header,
        .factura-header {
            background: linear-gradient(90deg, var(--duran-azul-oscuro), var(--duran-azul-medio));
            color: var(--duran-gris-claro);
            border-bottom: 0;
        }

        .card-title,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .page-title {
            color: var(--duran-texto-principal);
        }

        .card-text,
        .text-muted,
        small,
        .section-description {
            color: var(--duran-texto-secundario) !important;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            border-color: var(--duran-azul-brillante);
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.22);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(90deg, #2f6fed, #275fd1);
            border-color: #275fd1;
        }

        .btn-secondary {
            background: linear-gradient(90deg, var(--duran-azul-medio), #274caa);
            border-color: var(--duran-azul-medio);
        }

        .btn-outline-primary {
            color: var(--duran-azul-oscuro);
            border-color: var(--duran-azul-oscuro);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            background: var(--duran-azul-oscuro);
            border-color: var(--duran-azul-oscuro);
            color: var(--duran-blanco);
        }

        .badge-carrito {
            background-color: var(--duran-blanco);
            color: var(--duran-azul-oscuro);
            border-color: rgba(255, 255, 255, 0.35);
        }

        .container .row > [class*="col-"] .card {
            background: var(--duran-blanco);
        }

        .section-title,
        .text-primary {
            color: var(--duran-azul-oscuro) !important;
        }

        .text-secondary,
        .page-subtitle {
            color: var(--duran-texto-secundario) !important;
        }

        .badge-carrito {
            display: inline-flex;
            min-width: 15px;
            height: 15px;
            padding: 0 3px;
            border-radius: 999px;
            font-size: 0.58rem;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            color: var(--duran-azul-oscuro);
            background-color: var(--duran-blanco);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .nav-link-carrito {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .indicador-carrito {
            position: absolute;
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: 3px;
            line-height: 1;
        }

        .icono-carrito-mini {
            width: 20px;
            height: 20px;
            fill: var(--duran-gris-claro);
            stroke: var(--duran-gris-claro);
            stroke-width: 0.7;
        }

        .navbar-brand img {
            box-shadow: 0 6px 18px rgba(255, 255, 255, 0.08);
            border-radius: 12px !important;
        }

        @media (max-width: 1199px) {
            .navbar-brand-texto {
                font-size: 1.8rem;
            }

            .navbar-buscador {
                max-width: 520px;
            }

            .navbar-buscador .form-control {
                height: 46px;
                font-size: 0.98rem;
            }

            .navbar-buscador .btn {
                min-width: 108px;
                font-size: 0.98rem;
            }
        }

    </style>

    <nav class="navbar navbar-expand-lg navbar-duran">
        <a class="navbar-brand" href="<?php echo $basePath; ?>/index.php">
            <img src="<?php echo rtrim($basePublica, '/'); ?>/recursos/logo.svg" width="58" height="58" alt="Logo Duran" class="d-inline-block align-top rounded mr-2" style="object-fit: cover;">
            <span class="navbar-brand-texto">Duran</span>
        </a>
        <form class="navbar-buscador d-none d-lg-block" method="GET" action="<?php echo $basePath; ?>/index.php">
            <div class="input-group">
                <input type="text" class="form-control" name="buscar" placeholder="Buscar productos" value="<?php echo htmlspecialchars(trim($_GET['buscar'] ?? '')); ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </div>
        </form>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>/index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-carrito" href="<?php echo $basePath; ?>/carrito.php" aria-label="Carrito de compras">
                        <span class="indicador-carrito" aria-hidden="true">
                            <svg class="icono-carrito-mini" viewBox="0 0 24 24" focusable="false">
                                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2zM7.17 14h9.92c.75 0 1.41-.41 1.75-1.03L22 6.5A1 1 0 0 0 21.12 5H6.21l-.45-2.02A1 1 0 0 0 4.78 2H2a1 1 0 1 0 0 2h1.98l2.23 10.01A2 2 0 0 0 8.17 16H19a1 1 0 1 0 0-2H8.17z"></path>
                            </svg>
                            <span class="badge-carrito"><?php echo (int)$cantidadCarrito; ?></span>
                        </span>
                        <span>Carrito</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>/pago.php">Pagar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>/admin/inicio_sesion.php">Iniciar sesión</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>/registro.php">Registrar</a>
                </li>
            </ul>
        </div>
    </nav>
