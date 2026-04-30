    <style>
        .footer-duran {
            background: linear-gradient(90deg, #0a1f44 0%, #1e3a8a 100%);
            box-shadow: 0 -10px 28px rgba(10, 31, 68, 0.18);
            color: #f3f4f6;
        }

        .footer-duran a {
            color: #e8eefb;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-duran a:hover {
            color: #3b82f6 !important;
        }

        .footer-duran p,
        .footer-duran small {
            color: #f3f4f6;
        }

        .footer-titulo {
            font-weight: 700;
            margin-bottom: 0.7rem;
            font-size: 1.2rem;
        }

        .footer-lista {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-lista li {
            margin-bottom: 0.35rem;
        }

        .footer-logo {
            width: 180px;
            max-width: 100%;
            margin-bottom: 0.6rem;
        }

        .footer-redes {
            display: flex;
            gap: 0.9rem;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .footer-redes i {
            font-size: 1.6rem;
        }
    </style>

    <?php
    $basePathFooter = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($basePathFooter === '') {
        $basePathFooter = '/';
    }
    if ($basePathFooter !== '/' && substr($basePathFooter, -6) === '/admin') {
        $basePathFooter = substr($basePathFooter, 0, -6);
        if ($basePathFooter === '') {
            $basePathFooter = '/';
        }
    }
    ?>

    <footer class="footer-duran py-4 mt-5">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-md-3 mb-3 mb-md-0">
                    <img src="<?php echo rtrim($basePathFooter, '/'); ?>/recursos/logo.svg" alt="Logo Duran" class="footer-logo">
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="footer-titulo">Enlaces</div>
                    <ul class="footer-lista">
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="index.php">Productos</a></li>
                        <li><a href="#">Sobre nosotros</a></li>
                        <li><a href="#">Contacto</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="footer-titulo">Ayuda</div>
                    <ul class="footer-lista">
                        <li><a href="#">Preguntas frecuentes</a></li>
                        <li><a href="#">Envíos y devoluciones</a></li>
                        <li><a href="#">Política de privacidad</a></li>
                        <li><a href="#">Términos y condiciones</a></li>
                    </ul>
                </div>

                <div class="col-md-3 text-md-left">
                    <div class="footer-titulo">Síguenos</div>
                    <div class="footer-redes">
                        <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.twitter.com" target="_blank" rel="noopener noreferrer"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer"><i class="bi bi-linkedin"></i></a>
                    </div>
                    <small>&copy; <?php echo date("Y"); ?> Duran. Todos los derechos reservados.</small>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

<!-- Agregar enlace a Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">