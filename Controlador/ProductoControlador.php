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
        $id_marca = (int)($datos['id_marca'] ?? 0);
        $id_industria = (int)($datos['id_industria'] ?? 0);
        $id_categoria = (int)($datos['id_categoria'] ?? 0);
        $id_proveedor = (int)($datos['id_proveedor'] ?? 0);

        if ($nombre === '' || $descripcion === '') {
            return ['ok' => false, 'error' => 'Completa los campos requeridos.'];
        }

        if ($precio <= 0) {
            return ['ok' => false, 'error' => 'El precio debe ser mayor a 0.'];
        }

        if ($stock < 0) {
            return ['ok' => false, 'error' => 'El stock no puede ser negativo.'];
        }

        if ($id_marca <= 0 || $id_industria <= 0 || $id_categoria <= 0) {
            return ['ok' => false, 'error' => 'Debes seleccionar marca, industria y categoría.'];
        }

        $imagen = $this->procesarImagen($archivoImagen, true);
        if (!$imagen['ok']) {
            return $imagen;
        }

        $ok = $this->modelo->crear($nombre, $descripcion, $precio, $imagen['imagen_base64'], $stock, $id_marca, $id_industria, $id_categoria, $id_proveedor > 0 ? $id_proveedor : null);
        if (!$ok) {
            return ['ok' => false, 'error' => 'Error al guardar el producto en la base de datos. Verifica el error en los logs del servidor.'];
        }

        return ['ok' => true];
    }

    public function actualizarProducto(int $idProducto, array $datos, array $archivoImagen, string $imagenActual): array
    {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? ''));
        $precio = (float)($datos['precio'] ?? 0);
        $stock = (int)($datos['stock'] ?? 0);
        $id_marca = (int)($datos['id_marca'] ?? 0);
        $id_industria = (int)($datos['id_industria'] ?? 0);
        $id_categoria = (int)($datos['id_categoria'] ?? 0);
        $id_proveedor = (int)($datos['id_proveedor'] ?? 0);
        $imagenBase64 = $imagenActual;

        if ($nombre === '' || $descripcion === '') {
            return ['ok' => false, 'error' => 'Completa los campos requeridos.'];
        }

        if ($precio <= 0) {
            return ['ok' => false, 'error' => 'El precio debe ser mayor a 0.'];
        }

        if ($stock < 0) {
            return ['ok' => false, 'error' => 'El stock no puede ser negativo.'];
        }

        if ($id_marca <= 0 || $id_industria <= 0 || $id_categoria <= 0) {
            return ['ok' => false, 'error' => 'Debes seleccionar marca, industria y categoría.'];
        }

        if (isset($archivoImagen['error']) && (int)$archivoImagen['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagen = $this->procesarImagen($archivoImagen, false);
            if (!$imagen['ok']) {
                return $imagen;
            }
            $imagenBase64 = $imagen['imagen_base64'];
        }

        $ok = $this->modelo->actualizar($idProducto, $nombre, $descripcion, $precio, $imagenBase64, $stock, $id_marca, $id_industria, $id_categoria, $id_proveedor > 0 ? $id_proveedor : null);
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
                : ['ok' => true, 'imagen_base64' => ''];
        }

        $errorArchivo = (int)$archivo['error'];

        if ($errorArchivo === UPLOAD_ERR_NO_FILE) {
            return $obligatoria
                ? ['ok' => false, 'error' => 'No se ha seleccionado una imagen.']
                : ['ok' => true, 'imagen_base64' => ''];
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

        // Leer el archivo y convertir a Base64
        $archivoTemp = (string)($archivo['tmp_name'] ?? '');
        if (!file_exists($archivoTemp)) {
            return ['ok' => false, 'error' => 'No se pudo procesar la imagen.'];
        }

        $contenidoImagen = file_get_contents($archivoTemp);
        if ($contenidoImagen === false) {
            return ['ok' => false, 'error' => 'No se pudo leer la imagen.'];
        }

        // Convertir a Base64
        $imagenBase64 = 'data:image/' . $extension . ';base64,' . base64_encode($contenidoImagen);

        // Validar tamaño (máximo 5MB en Base64 ~ 3.75MB original)
        if (strlen($imagenBase64) > 5242880) {
            return ['ok' => false, 'error' => 'La imagen es demasiado grande (máximo 5MB).'];
        }

        return ['ok' => true, 'imagen_base64' => $imagenBase64];
    }
}
