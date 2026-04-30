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

    public function crear(string $nombre, string $descripcion, float $precio, string $imagen, int $stock, int $id_marca, int $id_industria, int $id_categoria, ?int $id_proveedor): bool
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

        // Validar IDs
        if ($id_marca <= 0 || $id_industria <= 0 || $id_categoria <= 0) {
            error_log("Error: IDs inválidos. marca: $id_marca, industria: $id_industria, categoria: $id_categoria");
            return false;
        }

        $stmt = $this->conexion->prepare('INSERT INTO productos (nombre, descripcion, precio, imagen, stock, id_marca, id_industria, id_categoria, id_proveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            error_log("Error en prepare: " . $this->conexion->error);
            return false;
        }

        // Usar 'i' para integers y NULL para id_proveedor opcional
        $stmt->bind_param('ssdsiiii', $nombre, $descripcion, $precio, $imagen, $stock, $id_marca, $id_industria, $id_categoria);
        
        // Manejar id_proveedor NULL
        if ($id_proveedor === null) {
            $stmt->bind_param('ssdsiiii', $nombre, $descripcion, $precio, $imagen, $stock, $id_marca, $id_industria, $id_categoria);
        } else {
            $stmt->bind_param('ssdsiiiii', $nombre, $descripcion, $precio, $imagen, $stock, $id_marca, $id_industria, $id_categoria, $id_proveedor);
        }
        
        $ok = $stmt->execute();
        
        if (!$ok) {
            error_log("Error en execute: " . $stmt->error);
        }
        
        $stmt->close();

        return $ok;
    }

    public function actualizar(int $idProducto, string $nombre, string $descripcion, float $precio, string $imagen, int $stock, int $id_marca, int $id_industria, int $id_categoria, ?int $id_proveedor): bool
    {
        $stmt = $this->conexion->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, stock = ?, id_marca = ?, id_industria = ?, id_categoria = ?, id_proveedor = ? WHERE id_producto = ?');
        if (!$stmt) {
            return false;
        }

        if ($id_proveedor === null) {
            $stmt->bind_param('ssdsiiiii', $nombre, $descripcion, $precio, $imagen, $stock, $id_marca, $id_industria, $id_categoria, $idProducto);
        } else {
            $stmt->bind_param('ssdsiiiiii', $nombre, $descripcion, $precio, $imagen, $stock, $id_marca, $id_industria, $id_categoria, $id_proveedor, $idProducto);
        }
        
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
