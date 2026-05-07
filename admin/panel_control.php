<?php
session_start();

// Verificar si el administrador ha iniciado sesión
if (!isset($_SESSION['admin'])) {
    header("Location: inicio_sesion.php");
    exit();
}

// Incluir la conexión a la base de datos
include '../incluir/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Administración</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 42%),
                linear-gradient(180deg, var(--duran-gris-claro) 0%, #e9eef7 100%);
            color: var(--duran-texto-principal);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .panel-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .panel-header {
            margin-bottom: 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .panel-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--duran-azul-oscuro);
            margin: 0;
            text-shadow: 0 2px 4px rgba(10, 31, 68, 0.08);
        }

        .btn-group-header {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-header {
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .btn-inicio {
            background: linear-gradient(90deg, var(--duran-azul-medio), var(--duran-azul-oscuro));
            color: white;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .btn-inicio:hover {
            background: linear-gradient(90deg, var(--duran-azul-oscuro), #051a38);
            color: white;
            text-decoration: none;
        }

        .btn-logout {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-logout:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            color: white;
            text-decoration: none;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .panel-card {
            background: var(--duran-blanco);
            border: 1px solid rgba(30, 58, 138, 0.08);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--duran-sombra);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .panel-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(10, 31, 68, 0.2);
            border-color: var(--duran-azul-brillante);
        }

        .card-header-panel {
            background: linear-gradient(135deg, var(--duran-azul-oscuro), var(--duran-azul-medio));
            color: white;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .card-header-text h3 {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0;
            color: white;
        }

        .card-body-panel {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-description {
            color: var(--duran-texto-secundario);
            font-size: 1rem;
            margin-bottom: 25px;
            line-height: 1.6;
            flex: 1;
        }

        .btn-card {
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 24px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.22);
        }

        .btn-card:hover {
            background: linear-gradient(90deg, #2f6fed, #275fd1);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.35);
        }

        @media (max-width: 768px) {
            .panel-title {
                font-size: 1.8rem;
            }

            .panel-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-group-header {
                width: 100%;
            }

            .btn-header {
                flex: 1;
                justify-content: center;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body>
    
    <div class="panel-container">
        <div class="panel-header">
            <h1 class="panel-title"><i class="fas fa-tachometer-alt"></i> Panel de Control</h1>
            <div class="btn-group-header">
                <a href="../index.php" class="btn-header btn-inicio"><i class="fas fa-home"></i> Volver al inicio</a>
            </div>
        </div>

        <div class="cards-grid">
            <div class="panel-card">
                <div class="card-header-panel">
                    <div class="card-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="card-header-text">
                        <h3>Gestionar Productos</h3>
                    </div>
                </div>
                <div class="card-body-panel">
                    <p class="card-description">Agregar, editar y eliminar productos de la tienda. Gestiona tu inventario de forma fácil y rápida.</p>
                    <a href="gestion_productos.php" class="btn-card">
                        <i class="fas fa-arrow-right"></i> Ir a Gestión
                    </a>
                </div>
            </div>

            <div class="panel-card">
                <div class="card-header-panel">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-header-text">
                        <h3>Compras y Carrito</h3>
                    </div>
                </div>
                <div class="card-body-panel">
                    <p class="card-description">Ver los productos agregados al carrito y todas las compras registradas en la base de datos.</p>
                    <a href="historial_compras.php" class="btn-card">
                        <i class="fas fa-history"></i> Ver Historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>