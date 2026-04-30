<?php

$GLOBALS['correo_error_detalle'] = '';

function establecer_error_correo(string $mensaje): void
{
    $GLOBALS['correo_error_detalle'] = $mensaje;
}

function obtener_error_correo(): string
{
    return (string)($GLOBALS['correo_error_detalle'] ?? '');
}

function cargar_config_correo(): array
{
    $configPorDefecto = [
        'usar_smtp' => false,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => '',
        'password' => '',
        'from_email' => 'no-reply@tu-tienda.local',
        'from_name' => 'Tienda en Linea',
    ];

    $rutaConfig = __DIR__ . '/config_correo.php';
    if (!is_file($rutaConfig)) {
        return $configPorDefecto;
    }

    $config = include $rutaConfig;
    if (!is_array($config)) {
        return $configPorDefecto;
    }

    return array_merge($configPorDefecto, $config);
}

function leer_respuesta_smtp($socket): string
{
    $respuesta = '';

    while (!feof($socket)) {
        $linea = fgets($socket, 515);
        if ($linea === false) {
            break;
        }

        $respuesta .= $linea;

        if (strlen($linea) >= 4 && $linea[3] === ' ') {
            break;
        }
    }

    return $respuesta;
}

function validar_codigo_smtp(string $respuesta, array $codigosEsperados): bool
{
    if ($respuesta === '') {
        return false;
    }

    $codigo = (int)substr($respuesta, 0, 3);
    return in_array($codigo, $codigosEsperados, true);
}

function enviar_comando_smtp($socket, string $comando, array $codigosEsperados, ?string &$detalleError): bool
{
    if (fwrite($socket, $comando . "\r\n") === false) {
        $detalleError = 'No se pudo enviar comando SMTP: ' . $comando;
        return false;
    }

    $respuesta = leer_respuesta_smtp($socket);
    if (!validar_codigo_smtp($respuesta, $codigosEsperados)) {
        $detalleError = 'Respuesta SMTP invalida para [' . $comando . ']: ' . trim($respuesta);
        return false;
    }

    return true;
}

function enviar_factura_correo_smtp(array $config, string $correoDestino, string $numeroFactura, string $htmlFactura, ?string &$detalleError): bool
{
    $detalleError = null;

    $host = (string)$config['host'];
    $port = (int)$config['port'];
    $encryption = strtolower((string)$config['encryption']);
    $username = (string)$config['username'];
    $password = (string)$config['password'];
    $fromEmail = (string)$config['from_email'];
    $fromName = (string)$config['from_name'];

    if ($host === '' || $port <= 0 || $username === '' || $password === '' || $fromEmail === '') {
        $detalleError = 'Configuracion SMTP incompleta en incluir/config_correo.php';
        return false;
    }

    $prefijo = $encryption === 'ssl' ? 'ssl://' : '';
    $contexto = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ]);

    $socket = @stream_socket_client(
        $prefijo . $host . ':' . $port,
        $errno,
        $errstr,
        20,
        STREAM_CLIENT_CONNECT,
        $contexto
    );

    if (!$socket) {
        $detalleError = 'No se pudo conectar al servidor SMTP: ' . $errstr . ' (' . $errno . ')';
        return false;
    }

    stream_set_timeout($socket, 20);

    $respuestaInicial = leer_respuesta_smtp($socket);
    if (!validar_codigo_smtp($respuestaInicial, [220])) {
        fclose($socket);
        $detalleError = 'Servidor SMTP no disponible: ' . trim($respuestaInicial);
        return false;
    }

    if (!enviar_comando_smtp($socket, 'EHLO localhost', [250], $detalleError)) {
        fclose($socket);
        return false;
    }

    if ($encryption === 'tls') {
        if (!enviar_comando_smtp($socket, 'STARTTLS', [220], $detalleError)) {
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            $detalleError = 'No se pudo habilitar TLS en SMTP.';
            return false;
        }

        if (!enviar_comando_smtp($socket, 'EHLO localhost', [250], $detalleError)) {
            fclose($socket);
            return false;
        }
    }

    if (!enviar_comando_smtp($socket, 'AUTH LOGIN', [334], $detalleError)) {
        fclose($socket);
        return false;
    }

    if (!enviar_comando_smtp($socket, base64_encode($username), [334], $detalleError)) {
        fclose($socket);
        return false;
    }

    if (!enviar_comando_smtp($socket, base64_encode($password), [235], $detalleError)) {
        fclose($socket);
        return false;
    }

    if (!enviar_comando_smtp($socket, 'MAIL FROM:<' . $fromEmail . '>', [250], $detalleError)) {
        fclose($socket);
        return false;
    }

    if (!enviar_comando_smtp($socket, 'RCPT TO:<' . $correoDestino . '>', [250, 251], $detalleError)) {
        fclose($socket);
        return false;
    }

    if (!enviar_comando_smtp($socket, 'DATA', [354], $detalleError)) {
        fclose($socket);
        return false;
    }

    $asunto = 'Tu factura de compra #' . $numeroFactura;
    $encabezados = 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n";
    $encabezados .= 'To: <' . $correoDestino . ">\r\n";
    $encabezados .= 'Subject: =?UTF-8?B?' . base64_encode($asunto) . "?=\r\n";
    $encabezados .= "MIME-Version: 1.0\r\n";
    $encabezados .= "Content-Type: text/html; charset=UTF-8\r\n";
    $encabezados .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

    $contenido = str_replace("\n.", "\n..", $htmlFactura);
    $mensaje = $encabezados . $contenido . "\r\n.";

    if (fwrite($socket, $mensaje . "\r\n") === false) {
        fclose($socket);
        $detalleError = 'No se pudo enviar el cuerpo del correo SMTP.';
        return false;
    }

    $respuestaData = leer_respuesta_smtp($socket);
    if (!validar_codigo_smtp($respuestaData, [250])) {
        fclose($socket);
        $detalleError = 'Error al enviar DATA SMTP: ' . trim($respuestaData);
        return false;
    }

    enviar_comando_smtp($socket, 'QUIT', [221], $detalleError);
    fclose($socket);
    return true;
}

function asegurar_tablas_facturacion(mysqli $conexion): bool
{
    $sqlFacturas = "CREATE TABLE IF NOT EXISTS facturas (
        id_factura INT AUTO_INCREMENT PRIMARY KEY,
        numero_factura VARCHAR(40) NOT NULL UNIQUE,
        token_acceso VARCHAR(64) NULL UNIQUE,
        correo_cliente VARCHAR(120) NOT NULL,
        nombre_cliente VARCHAR(120) NULL,
        telefono_cliente VARCHAR(30) NULL,
        direccion_cliente VARCHAR(180) NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        impuesto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    $sqlDetalle = "CREATE TABLE IF NOT EXISTS detalle_factura (
        id_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_factura INT NOT NULL,
        id_producto INT NULL,
        nombre_producto VARCHAR(150) NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (id_factura) REFERENCES facturas(id_factura) ON DELETE CASCADE,
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    if (!$conexion->query($sqlFacturas) || !$conexion->query($sqlDetalle)) {
        return false;
    }

    $columnaToken = $conexion->query("SHOW COLUMNS FROM facturas LIKE 'token_acceso'");
    if (!$columnaToken) {
        return false;
    }

    $existeToken = $columnaToken->num_rows > 0;
    $columnaToken->free();

    if (!$existeToken) {
        $sqlToken = "ALTER TABLE facturas
                     ADD COLUMN token_acceso VARCHAR(64) NULL UNIQUE AFTER numero_factura";
        if (!$conexion->query($sqlToken)) {
            return false;
        }
    }

    $columnasNuevas = [
        'telefono_cliente' => "ALTER TABLE facturas ADD COLUMN telefono_cliente VARCHAR(30) NULL AFTER nombre_cliente",
        'direccion_cliente' => "ALTER TABLE facturas ADD COLUMN direccion_cliente VARCHAR(180) NULL AFTER telefono_cliente",
    ];

    foreach ($columnasNuevas as $nombreColumna => $sqlAlter) {
        $columna = $conexion->query("SHOW COLUMNS FROM facturas LIKE '" . $conexion->real_escape_string($nombreColumna) . "'");
        if (!$columna) {
            return false;
        }

        $existe = $columna->num_rows > 0;
        $columna->free();

        if (!$existe && !$conexion->query($sqlAlter)) {
            return false;
        }
    }

    return true;
}

function generar_numero_factura(): string
{
    return 'FAC-' . date('Ymd-His') . '-' . random_int(1000, 9999);
}

function generar_token_factura(): string
{
    return bin2hex(random_bytes(16));
}

function obtener_base_publica_factura(): string
{
    $rutaConfig = __DIR__ . '/config_factura.php';
    $config = [];
    if (is_file($rutaConfig)) {
        $cargado = include $rutaConfig;
        if (is_array($cargado)) {
            $config = $cargado;
        }
    }

    $baseConfig = trim((string)($config['base_publica'] ?? ''));
    $baseEntorno = trim((string)getenv('FACTURA_TUNEL_URL'));

    if ($baseEntorno !== '') {
        return rtrim($baseEntorno, '/');
    }

    if ($baseConfig !== '') {
        return rtrim($baseConfig, '/');
    }

    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $esHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $protocolo = $esHttps ? 'https' : 'http';
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $directorio = str_replace('\\', '/', dirname($script));

    if ($directorio === '/' || $directorio === '.') {
        $directorio = '';
    }

    return $protocolo . '://' . $host . rtrim($directorio, '/');
}

function construir_url_publica_factura(string $tokenFactura): string
{
    $base = obtener_base_publica_factura();
    return rtrim($base, '/') . '/factura_cliente.php?token=' . urlencode($tokenFactura);
}

function construir_url_qr_factura(string $urlFactura): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($urlFactura);
}

function obtener_url_logo_empresa(): string
{
    return rtrim(obtener_base_publica_factura(), '/') . '/recursos/logo.svg';
}

function obtener_fecha_factura_guardada(mysqli $conexion, int $idFactura): string
{
    $fecha = '';

    $consultaFecha = $conexion->prepare(
        "SELECT DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha
         FROM facturas
         WHERE id_factura = ?
         LIMIT 1"
    );

    if ($consultaFecha) {
        $consultaFecha->bind_param("i", $idFactura);
        if ($consultaFecha->execute()) {
            $resultadoFecha = $consultaFecha->get_result();
            if ($resultadoFecha && $resultadoFecha->num_rows === 1) {
                $datosFecha = $resultadoFecha->fetch_assoc();
                $fecha = (string)($datosFecha['fecha'] ?? '');
            }
        }
        $consultaFecha->close();
    }

    if ($fecha === '') {
        $fecha = date('Y-m-d H:i:s');
    }

    return $fecha;
}

function obtener_detalle_carrito(mysqli $conexion, array $carrito): array
{
    $items = [];
    $subtotalGeneral = 0.0;

    $consulta = $conexion->prepare("SELECT id_producto, nombre, precio FROM productos WHERE id_producto = ? LIMIT 1");
    if (!$consulta) {
        return [[], 0.0];
    }

    foreach ($carrito as $item) {
        $idProducto = (int)($item['id_producto'] ?? 0);
        $cantidad = (int)($item['cantidad'] ?? 0);

        if ($idProducto <= 0 || $cantidad <= 0) {
            continue;
        }

        $consulta->bind_param("i", $idProducto);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado && $resultado->num_rows > 0) {
            $producto = $resultado->fetch_assoc();
            $precioUnitario = (float)$producto['precio'];
            $subtotal = $precioUnitario * $cantidad;

            $items[] = [
                'id_producto' => (int)$producto['id_producto'],
                'nombre_producto' => $producto['nombre'],
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotal,
            ];

            $subtotalGeneral += $subtotal;
        }
    }

    $consulta->close();

    return [$items, $subtotalGeneral];
}

function registrar_factura(
    mysqli $conexion,
    string $correoCliente,
    string $nombreCliente,
    string $telefonoCliente,
    string $direccionCliente,
    array $items,
    float $subtotal,
    float $impuesto,
    float $total
): ?array {
    if (empty($items)) {
        return null;
    }

    $numeroFactura = generar_numero_factura();
    $tokenFactura = generar_token_factura();

    $conexion->begin_transaction();

    try {
        $insertFactura = $conexion->prepare(
            "INSERT INTO facturas (numero_factura, token_acceso, correo_cliente, nombre_cliente, telefono_cliente, direccion_cliente, subtotal, impuesto, total)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$insertFactura) {
            throw new Exception('No se pudo preparar la insercion de factura.');
        }

        $insertFactura->bind_param(
            "ssssssddd",
            $numeroFactura,
            $tokenFactura,
            $correoCliente,
            $nombreCliente,
            $telefonoCliente,
            $direccionCliente,
            $subtotal,
            $impuesto,
            $total
        );

        if (!$insertFactura->execute()) {
            throw new Exception('No se pudo guardar la factura.');
        }

        $idFactura = (int)$conexion->insert_id;
        $insertFactura->close();

        $insertDetalle = $conexion->prepare(
            "INSERT INTO detalle_factura (id_factura, id_producto, nombre_producto, cantidad, precio_unitario, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$insertDetalle) {
            throw new Exception('No se pudo preparar el detalle de factura.');
        }

        foreach ($items as $item) {
            $idProducto = (int)$item['id_producto'];
            $nombreProducto = $item['nombre_producto'];
            $cantidad = (int)$item['cantidad'];
            $precioUnitario = (float)$item['precio_unitario'];
            $subtotalItem = (float)$item['subtotal'];

            $insertDetalle->bind_param(
                "iisidd",
                $idFactura,
                $idProducto,
                $nombreProducto,
                $cantidad,
                $precioUnitario,
                $subtotalItem
            );

            if (!$insertDetalle->execute()) {
                throw new Exception('No se pudo guardar un item del detalle de factura.');
            }
        }

        $insertDetalle->close();

        $fechaFactura = obtener_fecha_factura_guardada($conexion, $idFactura);
        $enlacePublico = construir_url_publica_factura($tokenFactura);
        $qrUrl = construir_url_qr_factura($enlacePublico);

        $conexion->commit();

        return [
            'id_factura' => $idFactura,
            'numero_factura' => $numeroFactura,
            'correo_cliente' => $correoCliente,
            'nombre_cliente' => $nombreCliente,
            'telefono_cliente' => $telefonoCliente,
            'direccion_cliente' => $direccionCliente,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
            'fecha' => $fechaFactura,
            'token_acceso' => $tokenFactura,
            'enlace_publico' => $enlacePublico,
            'qr_url' => $qrUrl,
            'items' => $items,
        ];
    } catch (Throwable $e) {
        $conexion->rollback();
        return null;
    }
}

function obtener_factura_por_token(mysqli $conexion, string $tokenFactura): ?array
{
    $tokenFactura = trim($tokenFactura);
    if ($tokenFactura === '') {
        return null;
    }

    $consultaFactura = $conexion->prepare(
        "SELECT id_factura, numero_factura, correo_cliente, nombre_cliente, telefono_cliente, direccion_cliente,
                subtotal, impuesto, total,
                DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i:%s') AS fecha
         FROM facturas
         WHERE token_acceso = ?
         LIMIT 1"
    );

    if (!$consultaFactura) {
        return null;
    }

    $consultaFactura->bind_param("s", $tokenFactura);
    if (!$consultaFactura->execute()) {
        $consultaFactura->close();
        return null;
    }

    $resultadoFactura = $consultaFactura->get_result();
    if (!$resultadoFactura || $resultadoFactura->num_rows !== 1) {
        $consultaFactura->close();
        return null;
    }

    $factura = $resultadoFactura->fetch_assoc();
    $consultaFactura->close();

    $idFactura = (int)$factura['id_factura'];
    $consultaDetalle = $conexion->prepare(
        "SELECT nombre_producto, cantidad, precio_unitario, subtotal
         FROM detalle_factura
         WHERE id_factura = ?
         ORDER BY id_detalle ASC"
    );

    if (!$consultaDetalle) {
        return null;
    }

    $consultaDetalle->bind_param("i", $idFactura);
    if (!$consultaDetalle->execute()) {
        $consultaDetalle->close();
        return null;
    }

    $resultadoDetalle = $consultaDetalle->get_result();
    $items = [];
    while ($fila = $resultadoDetalle->fetch_assoc()) {
        $items[] = [
            'nombre_producto' => (string)$fila['nombre_producto'],
            'cantidad' => (int)$fila['cantidad'],
            'precio_unitario' => (float)$fila['precio_unitario'],
            'subtotal' => (float)$fila['subtotal'],
        ];
    }
    $consultaDetalle->close();

    $enlacePublico = construir_url_publica_factura($tokenFactura);

    return [
        'id_factura' => $idFactura,
        'numero_factura' => (string)$factura['numero_factura'],
        'correo_cliente' => (string)$factura['correo_cliente'],
        'nombre_cliente' => (string)$factura['nombre_cliente'],
        'telefono_cliente' => (string)($factura['telefono_cliente'] ?? ''),
        'direccion_cliente' => (string)($factura['direccion_cliente'] ?? ''),
        'subtotal' => (float)$factura['subtotal'],
        'impuesto' => (float)$factura['impuesto'],
        'total' => (float)$factura['total'],
        'fecha' => (string)$factura['fecha'],
        'token_acceso' => $tokenFactura,
        'enlace_publico' => $enlacePublico,
        'qr_url' => construir_url_qr_factura($enlacePublico),
        'items' => $items,
    ];
}

function construir_html_factura(array $factura): string
{
    $filas = '';

    foreach ($factura['items'] as $item) {
        $filas .= '<tr>'
            . '<td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars($item['nombre_producto']) . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:center;">' . (int)$item['cantidad'] . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:right;">$' . number_format((float)$item['precio_unitario'], 2) . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:right;">$' . number_format((float)$item['subtotal'], 2) . '</td>'
            . '</tr>';
    }

    $cliente = trim((string)$factura['nombre_cliente']) === '' ? 'Cliente' : htmlspecialchars((string)$factura['nombre_cliente']);
    $correoCliente = trim((string)($factura['correo_cliente'] ?? ''));
    $telefonoCliente = trim((string)($factura['telefono_cliente'] ?? ''));
    $direccionCliente = trim((string)($factura['direccion_cliente'] ?? ''));
    $enlacePublico = trim((string)($factura['enlace_publico'] ?? ''));
    $qrUrl = trim((string)($factura['qr_url'] ?? ''));
    $logoEmpresa = obtener_url_logo_empresa();
    $bloqueQr = '';
    $bloqueDatos = '';

    if ($correoCliente !== '') {
        $bloqueDatos .= '<p><strong>Correo:</strong> ' . htmlspecialchars($correoCliente) . '</p>';
    }

    if ($telefonoCliente !== '') {
        $bloqueDatos .= '<p><strong>Telefono:</strong> ' . htmlspecialchars($telefonoCliente) . '</p>';
    }

    if ($direccionCliente !== '') {
        $bloqueDatos .= '<p><strong>Direccion:</strong> ' . htmlspecialchars($direccionCliente) . '</p>';
    }

    if ($enlacePublico !== '') {
        $bloqueQr .= '<p><strong>Enlace de Factura:</strong> <a href="' . htmlspecialchars($enlacePublico) . '">' . htmlspecialchars($enlacePublico) . '</a></p>';
    }

    if ($qrUrl !== '') {
        $bloqueQr .= '<p><img src="' . htmlspecialchars($qrUrl) . '" alt="QR Factura" width="180" height="180" style="border:1px solid #ddd;padding:6px;"></p>';
    }

    return '<html><body style="font-family:Arial, sans-serif;color:#222;background:#f7f8fb;padding:24px;">'
        . '<div style="max-width:860px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 12px 28px rgba(15,23,42,.10);">'
        . '<div style="padding:18px 24px;background:linear-gradient(90deg,#f8fafc 0%,#ffffff 100%);border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:16px;">'
        . '<img src="' . htmlspecialchars($logoEmpresa) . '" alt="Logo de la empresa" style="width:72px;height:72px;object-fit:cover;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.08);">'
        . '<div>'
        . '<h2 style="margin:0 0 4px 0;">Factura de Compra - ' . htmlspecialchars((string)$factura['numero_factura']) . '</h2>'
        . '<div style="color:#6b7280;font-size:14px;">Tienda en Línea</div>'
        . '</div>'
        . '</div>'
        . '<div style="padding:24px;">'
        . '<p><strong>Fecha:</strong> ' . htmlspecialchars((string)$factura['fecha']) . '</p>'
        . '<p><strong>Cliente:</strong> ' . $cliente . '</p>'
        . $bloqueDatos
        . $bloqueQr
        . '<table style="width:100%;border-collapse:collapse;">'
        . '<thead><tr>'
        . '<th style="padding:8px;border:1px solid #ddd;text-align:left;">Producto</th>'
        . '<th style="padding:8px;border:1px solid #ddd;text-align:center;">Cantidad</th>'
        . '<th style="padding:8px;border:1px solid #ddd;text-align:right;">Precio</th>'
        . '<th style="padding:8px;border:1px solid #ddd;text-align:right;">Subtotal</th>'
        . '</tr></thead>'
        . '<tbody>' . $filas . '</tbody>'
        . '</table>'
        . '<p style="margin-top:16px;"><strong>Subtotal:</strong> $' . number_format((float)$factura['subtotal'], 2) . '</p>'
        . '<p><strong>Impuesto:</strong> $' . number_format((float)$factura['impuesto'], 2) . '</p>'
        . '<p><strong>Total:</strong> $' . number_format((float)$factura['total'], 2) . '</p>'
        . '</div></div></body></html>';
}

function enviar_factura_correo(string $correoDestino, string $numeroFactura, string $htmlFactura): bool
{
    establecer_error_correo('');

    $config = cargar_config_correo();
    if (!empty($config['usar_smtp'])) {
        $detalleError = null;
        $enviado = enviar_factura_correo_smtp($config, $correoDestino, $numeroFactura, $htmlFactura, $detalleError);
        if ($enviado) {
            return true;
        }

        establecer_error_correo((string)$detalleError);
        return false;
    }

    $asunto = 'Tu factura de compra #' . $numeroFactura;

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@tu-tienda.local\r\n";

    $enviado = @mail($correoDestino, $asunto, $htmlFactura, $headers);
    if (!$enviado) {
        establecer_error_correo('La funcion mail() fallo. Activa SMTP en incluir/config_correo.php o configura php.ini.');
    }

    return $enviado;
}
