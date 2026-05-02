<?php
header('Content-Type: application/json');

$datos_dir = __DIR__ . '/../datos/';
if (!is_dir($datos_dir)) {
    mkdir($datos_dir, 0777, true);
}

$usuarios_file = $datos_dir . 'usuarios.json';

$input = json_decode(file_get_contents('php://input'), true);

$usuario_id = $input['usuario_id'] ?? null;
$nombre = trim($input['nombre'] ?? '');

if (!$usuario_id || !$nombre) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

// Cargar usuarios
$usuarios = file_exists($usuarios_file) ? json_decode(file_get_contents($usuarios_file), true) : [];

// Agregar usuario
$usuarios[$usuario_id] = [
    'usuario_id' => $usuario_id,
    'nombre' => $nombre,
    'timestamp' => date('Y-m-d H:i:s'),
    'estado' => 'online'
];

// Guardar
file_put_contents($usuarios_file, json_encode($usuarios, JSON_PRETTY_PRINT));

// Log del servidor
$log_file = $datos_dir . 'servidor.log';
$log = date('Y-m-d H:i:s') . " - [✓ CONECTADO] $nombre\n";
file_put_contents($log_file, $log, FILE_APPEND);

echo json_encode(['success' => true, 'mensaje' => 'Conectado']);
