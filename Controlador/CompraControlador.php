<?php

require_once __DIR__ . '/../Modelo/CompraModelo.php';

class CompraControlador
{
    private CompraModelo $modelo;

    public function __construct(mysqli $conexion)
    {
        $this->modelo = new CompraModelo($conexion);
    }

    public function asegurarEstructura(): bool
    {
        return $this->modelo->asegurarEstructuraCarrito();
    }

    public function agregarAlCarrito(string $sessionId, int $idProducto, int $cantidad = 1): bool
    {
        return $this->modelo->agregarProducto($sessionId, $idProducto, $cantidad);
    }

    public function eliminarDelCarrito(string $sessionId, int $idProducto): bool
    {
        return $this->modelo->eliminarProducto($sessionId, $idProducto);
    }

    public function actualizarCantidadCarrito(string $sessionId, int $idProducto, int $cantidad): bool
    {
        return $this->modelo->actualizarCantidadProducto($sessionId, $idProducto, $cantidad);
    }

    public function obtenerCarrito(string $sessionId): array
    {
        return $this->modelo->obtenerCarrito($sessionId);
    }

    public function obtenerCarritoDetallado(string $sessionId): array
    {
        return $this->modelo->obtenerCarritoDetallado($sessionId);
    }

    public function vaciarCarrito(string $sessionId): bool
    {
        return $this->modelo->vaciarCarrito($sessionId);
    }

    public function sincronizarSesionDesdeBd(string $sessionId, array &$carritoSesion): void
    {
        $carritoSesion = $this->obtenerCarrito($sessionId);
    }
}
