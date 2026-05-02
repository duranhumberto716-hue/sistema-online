<?php
header('Content-Type: application/json');

$datos_dir = __DIR__ . '/../datos/';
$usuarios_file = $datos_dir . 'usuarios.json';

$input = json_decode(file_get_contents('php://input'), true);

$usuario_id = $input['usuario_id'] ?? null;

if (!$usuario_id) {
    echo json_encode(['success' => false]);
    exit;
}

// Cargar usuarios
$usuarios = file_exists($usuarios_file) ? json_decode(file_get_contents($usuarios_file), true) : [];

$nombre = $usuarios[$usuario_id]['nombre'] ?? 'Usuario';

// Remover usuario
unset($usuarios[$usuario_id]);
file_put_contents($usuarios_file, json_encode($usuarios, JSON_PRETTY_PRINT));

// Log
$log_file = $datos_dir . 'servidor.log';
$log = date('Y-m-d H:i:s') . " - [✗ DESCONECTADO] $nombre\n";
file_put_contents($log_file, $log, FILE_APPEND);

echo json_encode(['success' => true]);
