<?php

class ProductoModelo
{
    private mysqli $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $productos = [];
        $resultado = $this->conexion->query('SELECT * FROM productos ORDER BY id_producto DESC');

        if ($resultado instanceof mysqli_result) {
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
            $resultado->free();
        }

        return $productos;
    }

    public function obtenerPorId(int $idProducto): ?array
    {
        $stmt = $this->conexion->prepare('SELECT * FROM productos WHERE id_producto = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $idProducto);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $producto = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        return $producto ?: null;
    }

    public function crear(string $nombre, string $descripcion, float $precio, string $imagen, int $stock): bool
    {
        // Validar que el precio sea positivo
        if ($precio <= 0) {
            error_log("Error: El precio debe ser mayor a 0. Precio recibido: " . $precio);
            return false;
        }

        // Validar que el stock sea no negativo
        if ($stock < 0) {
            error_log("Error: El stock no puede ser negativo. Stock recibido: " . $stock);
            return false;
        }

        $stmt = $this->conexion->prepare('INSERT INTO productos (nombre, descripcion, precio, imagen, stock) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            error_log("Error en prepare: " . $this->conexion->error);
            return false;
        }

        $stmt->bind_param('ssdsi', $nombre, $descripcion, $precio, $imagen, $stock);
        $ok = $stmt->execute();
        
        if (!$ok) {
            error_log("Error en execute: " . $stmt->error);
        }
        
        $stmt->close();

        return $ok;
    }

    public function actualizar(int $idProducto, string $nombre, string $descripcion, float $precio, string $imagen, int $stock): bool
    {
        $stmt = $this->conexion->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, stock = ? WHERE id_producto = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssdsii', $nombre, $descripcion, $precio, $imagen, $stock, $idProducto);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function eliminar(int $idProducto): bool
    {
        $stmt = $this->conexion->prepare('DELETE FROM productos WHERE id_producto = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $idProducto);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
