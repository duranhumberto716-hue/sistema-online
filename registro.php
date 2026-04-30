<?php
include 'incluir/conexion.php';

$registroError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmarPassword = trim($_POST['confirmar_password'] ?? '');

    if ($nombre === '' || $correo === '' || $password === '' || $confirmarPassword === '') {
        $registroError = 'Completa todos los campos para crear la cuenta.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $registroError = 'Ingresa un correo electrónico válido.';
    } elseif ($password !== $confirmarPassword) {
        $registroError = 'Las contraseñas no coinciden.';
    } else {
        $usuario = $correo;
        $consultaUsuario = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE usuario = ? LIMIT 1');

        if ($consultaUsuario) {
            $consultaUsuario->bind_param('s', $usuario);
            $consultaUsuario->execute();
            $consultaUsuario->store_result();

            if ($consultaUsuario->num_rows > 0) {
                $registroError = 'Ya existe una cuenta registrada con ese correo electrónico.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insertarUsuario = $conexion->prepare('INSERT INTO usuarios (usuario, password) VALUES (?, ?)');

                if ($insertarUsuario) {
                    $insertarUsuario->bind_param('ss', $usuario, $passwordHash);

                    if ($insertarUsuario->execute()) {
                        header('Location: admin/inicio_sesion.php?registro=ok');
                        exit();
                    }

                    $registroError = 'No se pudo crear la cuenta. Intenta nuevamente.';
                    $insertarUsuario->close();
                } else {
                    $registroError = 'No se pudo preparar el registro. Intenta nuevamente.';
                }
            }

            $consultaUsuario->close();
        } else {
            $registroError = 'No se pudo verificar si el correo ya existe.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tienda en Línea</title>
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

        * {
            box-sizing: border-box;
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

        .registro-shell {
            width: 100%;
            max-width: 1160px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: var(--duran-sombra);
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .registro-brand-panel {
            padding: 48px 52px;
            background:
                linear-gradient(180deg, rgba(10, 31, 68, 0.86), rgba(16, 44, 99, 0.92)),
                url('recursos/logo.svg');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .registro-brand-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(10, 31, 68, 0.88), rgba(16, 44, 99, 0.52));
        }

        .registro-brand-panel > * {
            position: relative;
            z-index: 1;
        }

        .brand-top {
            display: flex;
            justify-content: center;
            width: 100%;
            margin-bottom: 18px;
        }

        .brand-logo {
            width: 195px;
            height: 195px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 30px;
            padding: 18px;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.05) inset,
                0 18px 40px rgba(0, 0, 0, 0.18),
                0 0 30px rgba(59, 130, 246, 0.24);
        }

        .brand-kicker {
            text-transform: uppercase;
            letter-spacing: 0.28em;
            font-size: 0.72rem;
            color: rgba(245, 244, 246, 0.78);
            margin-bottom: 12px;
            text-align: center;
        }

        .brand-title {
            font-size: clamp(1.8rem, 2.8vw, 2.8rem);
            line-height: 1.05;
            font-weight: 800;
            margin-top: 8px;
            margin-bottom: 14px;
            color: var(--duran-blanco);
            text-align: center;
        }

        .brand-copy {
            max-width: 440px;
            color: rgba(243, 244, 246, 0.82);
            font-size: 1rem;
            line-height: 1.65;
            text-align: center;
        }

        .registro-form-panel {
            background: var(--duran-gris-claro);
            color: var(--duran-texto-principal);
            padding: 40px 42px;
            display: flex;
            align-items: center;
        }

        .registro-card {
            width: 100%;
        }

        .registro-card h1 {
            font-size: 2.05rem;
            font-weight: 800;
            color: var(--duran-texto-principal);
            margin-bottom: 10px;
            text-align: center;
        }

        .registro-card p.subtitulo {
            color: var(--duran-texto-secundario);
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 1.15rem;
            text-align: center;
        }

        .alert-registro {
            border: none;
            border-radius: 14px;
            background: rgba(59, 130, 246, 0.10);
            color: var(--duran-azul-oscuro);
        }

        .form-group label {
            font-weight: 700;
            margin-bottom: 8px;
            color: #283448;
        }

        .form-control {
            height: 48px;
            border-radius: 14px;
            border: 1px solid #d4dae7;
            padding: 0 14px;
            font-size: 1rem;
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

        .hint {
            margin-top: -6px;
            margin-bottom: 14px;
            color: var(--duran-texto-secundario);
            font-size: 0.92rem;
        }

        .btn-registro {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 14px;
            color: var(--duran-blanco);
            font-weight: 700;
            font-size: 1.05rem;
            background: linear-gradient(90deg, var(--duran-azul-brillante), #2f6fed);
            box-shadow: 0 10px 22px rgba(59, 130, 246, 0.26);
        }

        .btn-registro:hover,
        .btn-registro:focus {
            background: linear-gradient(90deg, #2f6fed, #275fd1);
        }

        @media (max-width: 992px) {
            .registro-shell {
                grid-template-columns: 1fr;
            }

            .registro-brand-panel {
                min-height: auto;
                padding: 30px;
            }

            .registro-form-panel {
                padding: 32px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 14px;
            }

            .registro-brand-panel,
            .registro-form-panel {
                padding: 24px 20px;
            }

            .brand-logo {
                width: 160px;
                height: 160px;
            }

            .registro-card h1 {
                font-size: 1.7rem;
            }

            .registro-card p.subtitulo {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="registro-shell">
        <section class="registro-brand-panel">
            <div class="brand-top">
                <img src="recursos/logo.svg" alt="Logo DURAN" class="brand-logo">
            </div>
            <div>
                <div class="brand-kicker">Acceso a clientes</div>
                <div class="brand-title">Tecnología de vanguardia<br>para tu éxito digital</div>
            </div>
        </section>

        <section class="registro-form-panel">
            <div class="registro-card">
                <h1>Regístrate</h1>
                <p class="subtitulo">Crea tu cuenta en segundos</p>

                <?php if ($registroError !== ''): ?>
                    <div class="alert alert-registro mb-3"><?php echo htmlspecialchars($registroError); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nombre">Nombre y apellido</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Tu nombre completo" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="password-group">
                            <input type="password" class="form-control password-input" id="password" name="password" placeholder="********" required>
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
                    <p class="hint">Mínimo 8 caracteres</p>

                    <div class="form-group">
                        <label for="confirmar_password">Confirma tu contraseña</label>
                        <div class="password-group">
                            <input type="password" class="form-control password-input" id="confirmar_password" name="confirmar_password" placeholder="********" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Mostrar contraseña" aria-pressed="false">
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

                    <button type="submit" class="btn-registro">Crear cuenta</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const bindToggle = function (toggleId, inputId) {
                const toggle = document.getElementById(toggleId);
                const input = document.getElementById(inputId);

                if (!toggle || !input) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    toggle.classList.toggle('is-visible', isHidden);
                    toggle.setAttribute('aria-pressed', String(isHidden));
                    toggle.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
                });
            };

            bindToggle('togglePassword', 'password');
            bindToggle('toggleConfirmPassword', 'confirmar_password');
        })();
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
