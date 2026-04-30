<?php

if (!function_exists('columna_existe_en_tabla')) {
    function columna_existe_en_tabla(mysqli $conexion, string $tabla, string $columna): bool
    {
        $stmt = $conexion->prepare(
            'SELECT COUNT(*) AS total
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?'
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ss', $tabla, $columna);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        return (int)($fila['total'] ?? 0) > 0;
    }
}

function asegurar_estructura_ventas_pago(mysqli $conexion, ?string &$detalleError = null): bool
{
    $detalleError = null;

    $ajustesVentas = [
        "ALTER TABLE ventas ADD COLUMN numero_venta VARCHAR(40) NULL AFTER id_venta",
        "ALTER TABLE ventas ADD COLUMN fecha_venta DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER fecha",
        "ALTER TABLE ventas ADD COLUMN subtotal DECIMAL(10,2) NULL DEFAULT 0.00 AFTER total",
        "ALTER TABLE ventas ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'PAGADA' AFTER subtotal",
        "ALTER TABLE ventas ADD COLUMN id_forma_pago INT NULL AFTER estado",
    ];

    foreach ($ajustesVentas as $sqlAjuste) {
        if (stripos($sqlAjuste, 'ADD COLUMN numero_venta') !== false && columna_existe_en_tabla($conexion, 'ventas', 'numero_venta')) {
            continue;
        }

        if (stripos($sqlAjuste, 'ADD COLUMN fecha_venta') !== false && columna_existe_en_tabla($conexion, 'ventas', 'fecha_venta')) {
            continue;
        }

        if (stripos($sqlAjuste, 'ADD COLUMN subtotal') !== false && columna_existe_en_tabla($conexion, 'ventas', 'subtotal')) {
            continue;
        }

        if (stripos($sqlAjuste, 'ADD COLUMN estado') !== false && columna_existe_en_tabla($conexion, 'ventas', 'estado')) {
            continue;
        }

        if (stripos($sqlAjuste, 'ADD COLUMN id_forma_pago') !== false && columna_existe_en_tabla($conexion, 'ventas', 'id_forma_pago')) {
            continue;
        }

        if (!$conexion->query($sqlAjuste)) {
            $detalleError = $conexion->error;
            return false;
        }
    }

    if (columna_existe_en_tabla($conexion, 'ventas', 'id_usuario')) {
        if (!$conexion->query('ALTER TABLE ventas MODIFY id_usuario INT NULL')) {
            $detalleError = $conexion->error;
            return false;
        }
    }

    $sentencias = [
        "CREATE TABLE IF NOT EXISTS formas_pago (
            id_forma_pago INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(30) NOT NULL UNIQUE,
            nombre VARCHAR(80) NOT NULL,
            descripcion VARCHAR(180) NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

        "CREATE TABLE IF NOT EXISTS ventas (
            id_venta INT AUTO_INCREMENT PRIMARY KEY,
            numero_venta VARCHAR(40) NOT NULL UNIQUE,
            fecha_venta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            subtotal DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            estado VARCHAR(20) NOT NULL DEFAULT 'PAGADA',
            id_forma_pago INT NOT NULL,
            FOREIGN KEY (id_forma_pago) REFERENCES formas_pago(id_forma_pago)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

        "CREATE TABLE IF NOT EXISTS detalle_venta (
            id_detalle_venta INT AUTO_INCREMENT PRIMARY KEY,
            id_venta INT NOT NULL,
            id_producto INT NULL,
            cantidad INT NOT NULL,
            precio_unitario DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
            FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

        "CREATE TABLE IF NOT EXISTS sucursales (
            id_sucursal INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(80) NOT NULL,
            direccion VARCHAR(180) NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

        "CREATE TABLE IF NOT EXISTS detalle_sucursal_producto (
            id_sucursal INT NOT NULL,
            id_producto INT NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            PRIMARY KEY (id_sucursal, id_producto),
            FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE CASCADE,
            FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

        "INSERT INTO sucursales (id_sucursal, nombre, direccion, activo)
         SELECT 1, 'Sucursal Principal', 'Central', 1
         WHERE NOT EXISTS (SELECT 1 FROM sucursales WHERE id_sucursal = 1)",

        "INSERT INTO detalle_sucursal_producto (id_sucursal, id_producto, stock)
         SELECT 1, p.id_producto, p.stock
         FROM productos p
         WHERE NOT EXISTS (
             SELECT 1
             FROM detalle_sucursal_producto dsp
             WHERE dsp.id_sucursal = 1
               AND dsp.id_producto = p.id_producto
         )",

        "CREATE TABLE IF NOT EXISTS pagos_venta (
            id_pago INT AUTO_INCREMENT PRIMARY KEY,
            id_venta INT NOT NULL,
            id_forma_pago INT NOT NULL,
            monto DECIMAL(10,2) NOT NULL,
            estado VARCHAR(20) NOT NULL DEFAULT 'PAGADO',
            fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
            FOREIGN KEY (id_forma_pago) REFERENCES formas_pago(id_forma_pago)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

        "INSERT INTO formas_pago (codigo, nombre, descripcion)
         SELECT 'efectivo', 'Efectivo', 'Pago en efectivo'
         WHERE NOT EXISTS (SELECT 1 FROM formas_pago WHERE codigo = 'efectivo')",

        "INSERT INTO formas_pago (codigo, nombre, descripcion)
         SELECT 'tarjeta', 'Tarjeta', 'Pago con tarjeta de credito/debito'
         WHERE NOT EXISTS (SELECT 1 FROM formas_pago WHERE codigo = 'tarjeta')",

        "INSERT INTO formas_pago (codigo, nombre, descripcion)
         SELECT 'transferencia', 'Transferencia', 'Transferencia bancaria'
         WHERE NOT EXISTS (SELECT 1 FROM formas_pago WHERE codigo = 'transferencia')",

        "INSERT INTO formas_pago (codigo, nombre, descripcion)
         SELECT 'qr', 'QR', 'Pago mediante codigo QR'
         WHERE NOT EXISTS (SELECT 1 FROM formas_pago WHERE codigo = 'qr')",

        "DROP FUNCTION IF EXISTS fn_calcular_total_venta",
        "DROP PROCEDURE IF EXISTS sp_registrar_venta",
        "DROP PROCEDURE IF EXISTS sp_registrar_detalle_venta",
        "DROP PROCEDURE IF EXISTS sp_registrar_pago_venta",

        "CREATE FUNCTION fn_calcular_total_venta(p_id_venta INT)
         RETURNS DECIMAL(10,2)
         DETERMINISTIC
         BEGIN
            DECLARE v_total DECIMAL(10,2);

            SELECT IFNULL(SUM(subtotal), 0.00)
              INTO v_total
              FROM detalle_venta
             WHERE id_venta = p_id_venta;

            RETURN v_total;
         END",

        "CREATE PROCEDURE sp_registrar_venta(
            IN p_subtotal DECIMAL(10,2),
            IN p_id_forma_pago INT,
            OUT p_id_venta INT,
            OUT p_numero_venta VARCHAR(40)
        )
        BEGIN
            SET p_numero_venta = CONCAT('VTA-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'), '-', LPAD(FLOOR(RAND() * 9999), 4, '0'));

            INSERT INTO ventas (id_cliente, id_usuario, numero_venta, fecha_venta, subtotal, total, estado, id_forma_pago)
            VALUES (NULL, NULL, p_numero_venta, NOW(), p_subtotal, p_subtotal, 'PENDIENTE', p_id_forma_pago);

            SET p_id_venta = LAST_INSERT_ID();
        END",

        "CREATE PROCEDURE sp_registrar_detalle_venta(
            IN p_id_venta INT,
            IN p_id_producto INT,
            IN p_cantidad INT,
            IN p_precio_unitario DECIMAL(10,2)
        )
        BEGIN
            DECLARE v_subtotal DECIMAL(10,2);
                        DECLARE v_filas_afectadas INT;

            SET v_subtotal = p_cantidad * p_precio_unitario;

                        UPDATE detalle_sucursal_producto
                             SET stock = stock - p_cantidad
                         WHERE id_producto = p_id_producto
                             AND id_sucursal = 1
                             AND stock >= p_cantidad;

                        SET v_filas_afectadas = ROW_COUNT();

                        IF v_filas_afectadas = 0 THEN
                                SIGNAL SQLSTATE '45000'
                                        SET MESSAGE_TEXT = 'Stock insuficiente en Detalle Sucursal Producto.';
                        END IF;

                        UPDATE productos p
                        INNER JOIN detalle_sucursal_producto dsp
                                        ON dsp.id_producto = p.id_producto
                                     AND dsp.id_sucursal = 1
                             SET p.stock = dsp.stock
                         WHERE p.id_producto = p_id_producto;

            INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
            VALUES (p_id_venta, p_id_producto, p_cantidad, p_precio_unitario, v_subtotal);
        END",

        "CREATE PROCEDURE sp_registrar_pago_venta(
            IN p_id_venta INT,
            IN p_id_forma_pago INT,
            IN p_monto DECIMAL(10,2),
            OUT p_id_pago INT
        )
        BEGIN
            INSERT INTO pagos_venta (id_venta, id_forma_pago, monto, estado)
            VALUES (p_id_venta, p_id_forma_pago, p_monto, 'PAGADO');

            SET p_id_pago = LAST_INSERT_ID();

            UPDATE ventas
               SET total = fn_calcular_total_venta(p_id_venta),
                   estado = 'PAGADA'
             WHERE id_venta = p_id_venta;
        END",
    ];

    foreach ($sentencias as $sql) {
        if (!$conexion->query($sql)) {
            $detalleError = $conexion->error;
            return false;
        }
    }

    return true;
}

function limpiar_resultados_call(mysqli $conexion): void
{
    while ($conexion->more_results()) {
        $conexion->next_result();
        $resultado = $conexion->store_result();
        if ($resultado instanceof mysqli_result) {
            $resultado->free();
        }
    }
}

function obtener_formas_pago_disponibles(): array
{
    return [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
        'qr' => 'QR',
    ];
}

function registrar_venta_con_pago(mysqli $conexion, array $items, string $codigoFormaPago, ?string &$detalleError = null): ?array
{
    $detalleError = null;

    if (empty($items)) {
        $detalleError = 'No hay items en la venta.';
        return null;
    }

    $consultaFormaPago = $conexion->prepare(
        "SELECT id_forma_pago, nombre FROM formas_pago WHERE codigo = ? AND activo = 1 LIMIT 1"
    );

    if (!$consultaFormaPago) {
        $detalleError = $conexion->error;
        return null;
    }

    $consultaFormaPago->bind_param("s", $codigoFormaPago);
    $consultaFormaPago->execute();
    $resultadoFormaPago = $consultaFormaPago->get_result();

    if (!$resultadoFormaPago || $resultadoFormaPago->num_rows !== 1) {
        $consultaFormaPago->close();
        $detalleError = 'Forma de pago no encontrada o inactiva.';
        return null;
    }

    $formaPago = $resultadoFormaPago->fetch_assoc();
    $consultaFormaPago->close();

    $idFormaPago = (int)$formaPago['id_forma_pago'];
    $nombreFormaPago = (string)$formaPago['nombre'];

    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += (float)$item['subtotal'];
    }

    $conexion->begin_transaction();

    try {
        $spVenta = $conexion->prepare("CALL sp_registrar_venta(?, ?, @p_id_venta, @p_numero_venta)");
        if (!$spVenta) {
            throw new Exception('No se pudo preparar sp_registrar_venta.');
        }

        $spVenta->bind_param("di", $subtotal, $idFormaPago);
        if (!$spVenta->execute()) {
            throw new Exception('No se pudo ejecutar sp_registrar_venta.');
        }
        $spVenta->close();
        limpiar_resultados_call($conexion);

        $resVenta = $conexion->query("SELECT @p_id_venta AS id_venta, @p_numero_venta AS numero_venta");
        if (!$resVenta || $resVenta->num_rows !== 1) {
            throw new Exception('No se pudieron recuperar datos de la venta.');
        }

        $datosVenta = $resVenta->fetch_assoc();
        $idVenta = (int)$datosVenta['id_venta'];
        $numeroVenta = (string)$datosVenta['numero_venta'];

        if ($idVenta <= 0 || $numeroVenta === '') {
            throw new Exception('Datos de venta incompletos.');
        }

        $spDetalle = $conexion->prepare("CALL sp_registrar_detalle_venta(?, ?, ?, ?)");
        if (!$spDetalle) {
            throw new Exception('No se pudo preparar sp_registrar_detalle_venta.');
        }

        foreach ($items as $item) {
            $idProducto = (int)$item['id_producto'];
            $cantidad = (int)$item['cantidad'];
            $precioUnitario = (float)$item['precio_unitario'];

            $spDetalle->bind_param("iiid", $idVenta, $idProducto, $cantidad, $precioUnitario);
            if (!$spDetalle->execute()) {
                throw new Exception('No se pudo ejecutar sp_registrar_detalle_venta.');
            }
            limpiar_resultados_call($conexion);
        }

        $spDetalle->close();

        $spPago = $conexion->prepare("CALL sp_registrar_pago_venta(?, ?, ?, @p_id_pago)");
        if (!$spPago) {
            throw new Exception('No se pudo preparar sp_registrar_pago_venta.');
        }

        $spPago->bind_param("iid", $idVenta, $idFormaPago, $subtotal);
        if (!$spPago->execute()) {
            throw new Exception('No se pudo ejecutar sp_registrar_pago_venta.');
        }

        $spPago->close();
        limpiar_resultados_call($conexion);

        $resPago = $conexion->query("SELECT @p_id_pago AS id_pago");
        if (!$resPago || $resPago->num_rows !== 1) {
            throw new Exception('No se pudo recuperar el pago registrado.');
        }

        $datosPago = $resPago->fetch_assoc();
        $idPago = (int)$datosPago['id_pago'];

        $resTotal = $conexion->query("SELECT fn_calcular_total_venta(" . $idVenta . ") AS total_calculado");
        if (!$resTotal || $resTotal->num_rows !== 1) {
            throw new Exception('No se pudo calcular el total de la venta.');
        }

        $datosTotal = $resTotal->fetch_assoc();
        $totalCalculado = (float)$datosTotal['total_calculado'];

        $fechaVenta = '';
        $consultaFechaVenta = $conexion->prepare(
            "SELECT DATE_FORMAT(COALESCE(fecha_venta, fecha), '%Y-%m-%d %H:%i:%s') AS fecha_venta
             FROM ventas
             WHERE id_venta = ?
             LIMIT 1"
        );

        if ($consultaFechaVenta) {
            $consultaFechaVenta->bind_param("i", $idVenta);
            if ($consultaFechaVenta->execute()) {
                $resultadoFechaVenta = $consultaFechaVenta->get_result();
                if ($resultadoFechaVenta && $resultadoFechaVenta->num_rows === 1) {
                    $datosFechaVenta = $resultadoFechaVenta->fetch_assoc();
                    $fechaVenta = (string)($datosFechaVenta['fecha_venta'] ?? '');
                }
            }
            $consultaFechaVenta->close();
        }

        $conexion->commit();

        return [
            'id_venta' => $idVenta,
            'numero_venta' => $numeroVenta,
            'id_pago' => $idPago,
            'forma_pago' => $nombreFormaPago,
            'fecha_venta' => $fechaVenta,
            'total' => $totalCalculado,
        ];
    } catch (Throwable $e) {
        $conexion->rollback();
        $detalleError = $e->getMessage();
        return null;
    }
}
