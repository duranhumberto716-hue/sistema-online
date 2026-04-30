<?php
session_start();
include '../incluir/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Verificar credenciales con consulta preparada para evitar inyeccion SQL.
    $consulta = "SELECT password FROM usuarios WHERE usuario = ? LIMIT 1";
    $sentencia = $conexion->prepare($consulta);

    if ($sentencia) {
        $sentencia->bind_param("s", $usuario);
        $sentencia->execute();
        $sentencia->store_result();

        if ($sentencia->num_rows === 1) {
            $sentencia->bind_result($passwordGuardada);
            $sentencia->fetch();

            $passwordValida = password_verify($password, $passwordGuardada) || hash_equals((string)$passwordGuardada, (string)$password);

            if ($passwordValida) {
                $_SESSION['admin'] = $usuario;
                header("Location: panel_control.php");
                exit();
            }
        }

        $sentencia->close();
    }

    $error = "Usuario o contraseña incorrectos.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Administración</title>
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
            --duran-sombra: 0 24px 60px rgba(10, 31, 68, 0.28);
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.28), transparent 30%),
                radial-gradient(circle at bottom right, rgba(30, 58, 138, 0.24), transparent 32%),
                linear-gradient(160deg, #07142e 0%, #0a1f44 38%, #102c63 100%);
            color: var(--duran-gris-claro);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .login-shell {
            width: 100%;
            max-width: 1040px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 0;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: var(--duran-sombra);
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .login-brand-panel {
            padding: 42px;
            background:
                linear-gradient(180deg, rgba(10, 31, 68, 0.86), rgba(16, 44, 99, 0.92)),
                url('../recursos/logo.svg');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .login-brand-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(10, 31, 68, 0.88), rgba(16, 44, 99, 0.52));
        }

        .login-brand-panel > * {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 92px;
            height: 92px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 22px;
            padding: 10px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
        }

        .brand-kicker {
            text-transform: uppercase;
            letter-spacing: 0.28em;
            font-size: 0.72rem;
            color: rgba(245, 244, 246, 0.78);
            margin-bottom: 14px;
        }

        .brand-title {
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.05;
            font-weight: 800;
            margin-bottom: 14px;
            color: var(--duran-blanco);
        }

        .brand-copy {
            max-width: 440px;
            color: rgba(243, 244, 246, 0.82);
            font-size: 1rem;
            line-height: 1.65;
        }

        .login-form-panel {
            background: var(--duran-gris-claro);
            color: var(--duran-texto-principal);
            padding: 42px;
            display: flex;
            align-items: center;
        }

        .login-card {
            width: 100%;
        }

        .login-title {
            font-weight: 800;
            color: var(--duran-texto-principal);
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: var(--duran-texto-secundario);
            margin-bottom: 28px;
        }

        .form-control {
            height: 48px;
            border-radius: 14px;
            border: 1px solid rgba(30, 58, 138, 0.16);
            background: var(--duran-blanco);
        }

        .form-control:focus {
            border-color: var(--duran-azul-brillante);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.18);
        }

        .password-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            color: var(--duran-azul-oscuro);
            background: rgba(59, 130, 246, 0.08);
            outline: none;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        .password-toggle .icon-hide {
            display: none;
        }

        .password-toggle.is-visible .icon-show {
            display: none;
        }

        .password-toggle.is-visible .icon-hide {
            display: block;
        }

        .password-input {
            padding-right: 58px;
        }

        .btn-login {
            height: 50px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            box-shadow: 0 14px 26px rgba(59, 130, 246, 0.28);
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .btn-login:hover,
        .btn-login:focus {
            background: linear-gradient(90deg, #2f6fed, #275fd1);
        }

        .alert-danger {
            border: none;
            border-radius: 14px;
            background: rgba(220, 53, 69, 0.1);
            color: #a61d2f;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.12);
            color: var(--duran-azul-oscuro);
            font-weight: 700;
            font-size: 0.88rem;
            margin-bottom: 18px;
        }

        @media (max-width: 992px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-brand-panel {
                min-height: auto;
            }

        }

        @media (max-width: 576px) {
            .login-brand-panel,
            .login-form-panel {
                padding: 28px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <section class="login-brand-panel">
            <div>
                <img src="../recursos/logo.svg" alt="Logo DURAN" class="brand-logo">
                <div class="brand-kicker mt-4">Acceso administrativo</div>
                <div class="brand-title">Gestiona tu tienda con una interfaz más limpia y moderna.</div>
                <p class="brand-copy">Usa el panel de administración para controlar productos, ventas e historial con una identidad visual alineada a la marca DURAN.</p>
            </div>
        </section>

        <section class="login-form-panel">
            <div class="login-card">
                <div class="login-badge">Panel de administración</div>
                <h2 class="login-title">Inicio de sesión</h2>
                <p class="login-subtitle">Ingresa tus credenciales para acceder al sistema.</p>

                <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
                    <div class="alert alert-success">Cuenta creada exitosamente. Ya puedes iniciar sesión.</div>
                <?php endif; ?>

                <?php if (isset($error)) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="usuario">Usuario o correo electrónico</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Tu usuario o correo" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="password-group">
                            <input type="password" class="form-control password-input" id="password" name="password" required>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.16 20.16 0 0 1 5.17-5.94"></path>
                                    <path d="M1 1l22 22"></path>
                                    <path d="M9.53 9.53a3 3 0 0 0 4.24 4.24"></path>
                                    <path d="M10.73 5.08A10.56 10.56 0 0 1 12 5c7 0 11 7 11 7a19.92 19.92 0 0 1-2.06 2.94"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-login btn-block">Iniciar Sesión</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            if (!toggle || !password) {
                return;
            }

            toggle.addEventListener('click', function () {
                const isHidden = password.type === 'password';
                password.type = isHidden ? 'text' : 'password';
                toggle.classList.toggle('is-visible', isHidden);
                toggle.setAttribute('aria-pressed', String(isHidden));
                toggle.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        })();
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>