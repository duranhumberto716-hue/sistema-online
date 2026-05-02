<?php
header('Content-Type: application/json');

$datos_dir = __DIR__ . '/../datos/';

$input = json_decode(file_get_contents('php://input'), true);

$usuario_id = $input['usuario_id'] ?? null;
$tipo = $input['tipo'] ?? 'global';

if (!$usuario_id) {
    echo json_encode(['success' => false, 'mensaje' => 'Usuario no especificado']);
    exit;
}

// Determinar archivo de mensajes según el tipo
if ($tipo === 'global') {
    $mensajes_file = $datos_dir . 'mensajes_global.json';
    $log_tipo = '💬 GLOBAL';
} else if ($tipo === 'privado') {
    // Para mensajes privados
    $usuario1 = $input['usuario1'] ?? null;
    $usuario2 = $input['usuario2'] ?? null;
    
    if (!$usuario1 || !$usuario2) {
        echo json_encode(['success' => false, 'mensaje' => 'Usuarios no especificados']);
        exit;
    }
    
    $mensajes_file = $datos_dir . 'privado_' . $usuario1 . '_' . $usuario2 . '.json';
    $log_tipo = '🔒 PRIVADO';
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Tipo inválido']);
    exit;
}

// Eliminar archivo si existe
if (file_exists($mensajes_file)) {
    unlink($mensajes_file);
}

// Log del servidor
$log_file = $datos_dir . 'servidor.log';
$log = date('Y-m-d H:i:s') . " - [$log_tipo] ✂️ CHAT ELIMINADO\n";
file_put_contents($log_file, $log, FILE_APPEND);

echo json_encode(['success' => true, 'mensaje' => 'Chat eliminado correctamente']);
