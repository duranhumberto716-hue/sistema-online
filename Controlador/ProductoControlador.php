<?php

require_once __DIR__ . '/../Modelo/ProductoModelo.php';

class ProductoControlador
{
    private ProductoModelo $modelo;

    public function __construct(mysqli $conexion)
    {
        $this->modelo = new ProductoModelo($conexion);
    }

    public function listarProductos(): array
    {
        return $this->modelo->obtenerTodos();
    }

    public function obtenerProducto(int $idProducto): ?array
    {
        return $this->modelo->obtenerPorId($idProducto);
    }

    public function eliminarProducto(int $idProducto): bool
    {
        return $this->modelo->eliminar($idProducto);
    }

    public function crearProducto(array $datos, array $archivoImagen): array
    {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? ''));
        $precio = (float)($datos['precio'] ?? 0);
        $stock = (int)($datos['stock'] ?? 0);

        if ($nombre === '' || $descripcion === '') {
            return ['ok' => false, 'error' => 'Completa los campos requeridos.'];
        }

        $imagen = $this->procesarImagen($archivoImagen, true);
        if (!$imagen['ok']) {
            return $imagen;
        }

        $ok = $this->modelo->crear($nombre, $descripcion, $precio, $imagen['nombre'], $stock);
        if (!$ok) {
            return ['ok' => false, 'error' => 'Error al guardar el producto en la base de datos.'];
        }

        return ['ok' => true];
    }

    public function actualizarProducto(int $idProducto, array $datos, array $archivoImagen, string $imagenActual): array
    {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? ''));
        $precio = (float)($datos['precio'] ?? 0);
        $stock = (int)($datos['stock'] ?? 0);
        $nombreImagen = $imagenActual;

        if ($nombre === '' || $descripcion === '') {
            return ['ok' => false, 'error' => 'Completa los campos requeridos.'];
        }

        if (isset($archivoImagen['error']) && (int)$archivoImagen['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagen = $this->procesarImagen($archivoImagen, false);
            if (!$imagen['ok']) {
                return $imagen;
            }
            $nombreImagen = $imagen['nombre'];
        }

        $ok = $this->modelo->actualizar($idProducto, $nombre, $descripcion, $precio, $nombreImagen, $stock);
        if (!$ok) {
            return ['ok' => false, 'error' => 'Error al actualizar el producto en la base de datos.'];
        }

        return ['ok' => true];
    }

    private function procesarImagen(array $archivo, bool $obligatoria): array
    {
        if (!isset($archivo['error'])) {
            return $obligatoria
                ? ['ok' => false, 'error' => 'No se ha seleccionado una imagen.']
                : ['ok' => true, 'nombre' => ''];
        }

        $errorArchivo = (int)$archivo['error'];

        if ($errorArchivo === UPLOAD_ERR_NO_FILE) {
            return $obligatoria
                ? ['ok' => false, 'error' => 'No se ha seleccionado una imagen.']
                : ['ok' => true, 'nombre' => ''];
        }

        if ($errorArchivo !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Hubo un error al subir la imagen.'];
        }

        $nombreOriginal = basename((string)($archivo['name'] ?? ''));
        if ($nombreOriginal === '') {
            return ['ok' => false, 'error' => 'El nombre de la imagen no es valido.'];
        }

        $extension = strtolower((string)pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $permitidas, true)) {
            return ['ok' => false, 'error' => 'Formato de imagen no permitido.'];
        }

        $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)pathinfo($nombreOriginal, PATHINFO_FILENAME));
        if ($base === null || $base === '') {
            $base = 'imagen';
        }

        $nombreFinal = $base . '_' . time() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../recursos/' . $nombreFinal;

        if (!move_uploaded_file((string)$archivo['tmp_name'], $rutaDestino)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen en recursos.'];
        }

        return ['ok' => true, 'nombre' => $nombreFinal];
    }
}
