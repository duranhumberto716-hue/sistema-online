<?php
header('Content-Type: application/json');

$datos_dir = __DIR__ . '/../datos/';

$tipo = $_GET['tipo'] ?? 'global';
$ultimo = (int)($_GET['ultimo'] ?? 0);

// Determinar archivo de mensajes
if ($tipo === 'global') {
    $mensajes_file = $datos_dir . 'mensajes_global.json';
} else if ($tipo === 'privado') {
    // Para chat privado, usar usuario1 y usuario2
    $usuario1 = $_GET['usuario1'] ?? '';
    $usuario2 = $_GET['usuario2'] ?? '';
    
    if (!$usuario1 || !$usuario2) {
        echo json_encode(['success' => false, 'mensajes' => []]);
        exit;
    }
    
    // Los usuarios ya vienen ordenados desde el cliente
    $mensajes_file = $datos_dir . 'privado_' . $usuario1 . '_' . $usuario2 . '.json';
} else {
    echo json_encode(['success' => false, 'mensajes' => []]);
    exit;
}

// Cargar mensajes
$mensajes = [];
if (file_exists($mensajes_file)) {
    $todos = json_decode(file_get_contents($mensajes_file), true) ?: [];
    
    // Filtrar mensajes posteriores a 'ultimo'
    foreach ($todos as $msg) {
        if ($msg['id'] > $ultimo) {
            $mensajes[] = $msg;
        }
    }
}

echo json_encode(['success' => true, 'mensajes' => $mensajes]);
