<?php
header('Content-Type: application/json');

$datos_dir = __DIR__ . '/../datos/';
if (!is_dir($datos_dir)) {
    mkdir($datos_dir, 0777, true);
}

$input = json_decode(file_get_contents('php://input'), true);

$usuario_id = $input['usuario_id'] ?? null;
$nombre_usuario = trim($input['nombre_usuario'] ?? '');
$texto = trim($input['texto'] ?? '');
$tipo = $input['tipo'] ?? 'global';

if (!$usuario_id || !$nombre_usuario || !$texto) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

// Determinar archivo de mensajes según el tipo
if ($tipo === 'global') {
    $mensajes_file = $datos_dir . 'mensajes_global.json';
    $log_tipo = '💬 GLOBAL';
} else if ($tipo === 'privado') {
    // Para mensajes privados, usar usuario1 y usuario2 ordenados
    $usuario1 = $input['usuario1'] ?? null;
    $usuario2 = $input['usuario2'] ?? null;
    
    if (!$usuario1 || !$usuario2) {
        echo json_encode(['success' => false, 'mensaje' => 'Usuarios no especificados']);
        exit;
    }
    
    $usuarios = [$usuario1, $usuario2];
    sort($usuarios);
    $mensajes_file = $datos_dir . 'privado_' . $usuarios[0] . '_' . $usuarios[1] . '.json';
    $log_tipo = '🔒 PRIVADO';
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Tipo inválido']);
    exit;
}

// Cargar mensajes
$mensajes = file_exists($mensajes_file) ? json_decode(file_get_contents($mensajes_file), true) : [];

// Obtener siguiente ID
$siguiente_id = count($mensajes) + 1;

// Crear mensaje
$mensaje = [
    'id' => $siguiente_id,
    'usuario_id' => $usuario_id,
    'nombre_usuario' => $nombre_usuario,
    'texto' => $texto,
    'tipo' => $tipo,
    'timestamp' => date('Y-m-d H:i:s')
];

$mensajes[] = $mensaje;

// Guardar
file_put_contents($mensajes_file, json_encode($mensajes, JSON_PRETTY_PRINT));

// Log del servidor
$log_file = $datos_dir . 'servidor.log';
if ($tipo === 'global') {
    $log = date('Y-m-d H:i:s') . " - [$log_tipo] $nombre_usuario: " . substr($texto, 0, 50) . "\n";
} else {
    $log = date('Y-m-d H:i:s') . " - [$log_tipo] $nombre_usuario → privado: " . substr($texto, 0, 50) . "\n";
}
file_put_contents($log_file, $log, FILE_APPEND);

echo json_encode(['success' => true, 'id' => $siguiente_id]);
