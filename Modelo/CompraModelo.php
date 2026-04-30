<?php

class CompraModelo
{
    private mysqli $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    public function asegurarEstructuraCarrito(): bool
    {
        $sqlCarritos = "CREATE TABLE IF NOT EXISTS carritos (
            id_carrito INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(128) NOT NULL UNIQUE,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $sqlItems = "CREATE TABLE IF NOT EXISTS carrito_items (
            id_item INT AUTO_INCREMENT PRIMARY KEY,
            id_carrito INT NOT NULL,
            id_producto INT NOT NULL,
            cantidad INT NOT NULL DEFAULT 1,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_carrito_producto (id_carrito, id_producto),
            FOREIGN KEY (id_carrito) REFERENCES carritos(id_carrito) ON DELETE CASCADE,
            FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        return $this->conexion->query($sqlCarritos) && $this->conexion->query($sqlItems);
    }

    public function agregarProducto(string $sessionId, int $idProducto, int $cantidad = 1): bool
    {
        $idCarrito = $this->obtenerOCrearIdCarrito($sessionId);
        if ($idCarrito === null) {
            return false;
        }

        $stmt = $this->conexion->prepare(
            'INSERT INTO carrito_items (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad), fecha_actualizacion = CURRENT_TIMESTAMP'
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iii', $idCarrito, $idProducto, $cantidad);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function eliminarProducto(string $sessionId, int $idProducto): bool
    {
        $idCarrito = $this->obtenerIdCarrito($sessionId);
        if ($idCarrito === null) {
            return true;
        }

        $stmt = $this->conexion->prepare('DELETE FROM carrito_items WHERE id_carrito = ? AND id_producto = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $idCarrito, $idProducto);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function actualizarCantidadProducto(string $sessionId, int $idProducto, int $cantidad): bool
    {
        $idCarrito = $this->obtenerIdCarrito($sessionId);
        if ($idCarrito === null) {
            return false;
        }

        if ($cantidad <= 0) {
            return $this->eliminarProducto($sessionId, $idProducto);
        }

        $stmt = $this->conexion->prepare('UPDATE carrito_items SET cantidad = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_carrito = ? AND id_producto = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iii', $cantidad, $idCarrito, $idProducto);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function obtenerCarrito(string $sessionId): array
    {
        $idCarrito = $this->obtenerIdCarrito($sessionId);
        if ($idCarrito === null) {
            return [];
        }

        $stmt = $this->conexion->prepare('SELECT id_producto, cantidad FROM carrito_items WHERE id_carrito = ?');
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $idCarrito);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $items = [];
        if ($resultado instanceof mysqli_result) {
            while ($fila = $resultado->fetch_assoc()) {
                $items[] = [
                    'id_producto' => (int)$fila['id_producto'],
                    'cantidad' => (int)$fila['cantidad'],
                ];
            }
        }

        $stmt->close();

        return $items;
    }

    public function obtenerCarritoDetallado(string $sessionId): array
    {
        $idCarrito = $this->obtenerIdCarrito($sessionId);
        if ($idCarrito === null) {
            return [];
        }

        $stmt = $this->conexion->prepare(
            'SELECT p.id_producto, p.nombre, p.precio, ci.cantidad
             FROM carrito_items ci
             INNER JOIN productos p ON p.id_producto = ci.id_producto
             WHERE ci.id_carrito = ?
             ORDER BY ci.id_item ASC'
        );

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $idCarrito);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $items = [];
        if ($resultado instanceof mysqli_result) {
            while ($fila = $resultado->fetch_assoc()) {
                $cantidad = (int)$fila['cantidad'];
                $precio = (float)$fila['precio'];
                $items[] = [
                    'id_producto' => (int)$fila['id_producto'],
                    'nombre' => (string)$fila['nombre'],
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'subtotal' => $precio * $cantidad,
                ];
            }
        }

        $stmt->close();

        return $items;
    }

    public function vaciarCarrito(string $sessionId): bool
    {
        $idCarrito = $this->obtenerIdCarrito($sessionId);
        if ($idCarrito === null) {
            return true;
        }

        $stmt = $this->conexion->prepare('DELETE FROM carrito_items WHERE id_carrito = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $idCarrito);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    private function obtenerOCrearIdCarrito(string $sessionId): ?int
    {
        $id = $this->obtenerIdCarrito($sessionId);
        if ($id !== null) {
            return $id;
        }

        $stmt = $this->conexion->prepare('INSERT INTO carritos (session_id) VALUES (?)');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $sessionId);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return null;
        }

        return $this->obtenerIdCarrito($sessionId);
    }

    private function obtenerIdCarrito(string $sessionId): ?int
    {
        $stmt = $this->conexion->prepare('SELECT id_carrito FROM carritos WHERE session_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        if (!$fila) {
            return null;
        }

        return (int)$fila['id_carrito'];
    }
}
