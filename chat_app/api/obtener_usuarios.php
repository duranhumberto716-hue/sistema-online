<?php
header('Content-Type: application/json');

$datos_dir = __DIR__ . '/../datos/';
$usuarios_file = $datos_dir . 'usuarios.json';

// Cargar usuarios
$usuarios_array = [];
if (file_exists($usuarios_file)) {
    $usuarios_data = json_decode(file_get_contents($usuarios_file), true) ?: [];
    
    foreach ($usuarios_data as $id => $usuario) {
        if ($usuario['estado'] === 'online') {
            $usuarios_array[] = [
                'usuario_id' => $usuario['usuario_id'],
                'nombre' => $usuario['nombre'],
                'estado' => $usuario['estado']
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'usuarios' => $usuarios_array,
    'cantidad' => count($usuarios_array)
]);
