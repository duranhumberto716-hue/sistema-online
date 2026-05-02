<?php
session_start();

// Generar ID único para el usuario si no existe
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = uniqid('usuario_', true);
    $_SESSION['nombre_usuario'] = '';
}

$usuario_id = $_SESSION['usuario_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Sistema Online</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            background: white;
        }

        /* Sidebar - Lista de conversaciones */
        .sidebar {
            width: 300px;
            background: #f5f5f5;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 15px;
            background: #0a1f44;
            color: white;
            font-weight: bold;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
        }

        .usuarios-conectados {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }

        .usuario-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: space-between;
        }

        .usuario-item:hover {
            background: #e8e8e8;
        }

        .usuario-item.active {
            background: #3b82f6;
            color: white;
        }

        .usuario-item.global {
            font-weight: bold;
            background: #667eea;
            color: white;
        }

        .usuario-content {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .usuario-actions {
            display: none;
            gap: 5px;
            flex-shrink: 0;
        }

        .usuario-item:not(.global):hover .usuario-actions {
            display: flex;
        }

        .btn-delete-usuario {
            background: #ef4444;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-delete-usuario:hover {
            background: #dc2626;
        }

        .usuario-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .usuario-info {
            flex: 1;
        }

        .usuario-nombre {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .usuario-estado {
            font-size: 12px;
            opacity: 0.7;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background: #10b981;
            border-radius: 50%;
        }

        /* Chat Principal */
        .chat-principal {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 15px 20px;
            background: #0a1f44;
            color: white;
            font-weight: bold;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .chat-header-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mensaje {
            display: flex;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mensaje.propio {
            justify-content: flex-end;
        }

        .mensaje-contenido {
            max-width: 60%;
            padding: 10px 15px;
            border-radius: 10px;
            background: white;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }

        .mensaje.propio .mensaje-contenido {
            background: #3b82f6;
            color: white;
            border: none;
        }

        .mensaje-usuario {
            font-weight: bold;
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }

        .mensaje-timestamp {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }

        .chat-input-area {
            padding: 15px;
            border-top: 1px solid #ddd;
            background: white;
            display: flex;
            gap: 10px;
        }

        .chat-input-area input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
        }

        .chat-input-area button {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .chat-input-area button:hover {
            background: #2563eb;
        }

        /* Modal de entrada de nombre */
        .modal-login {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-login.active {
            display: flex;
        }

        .modal-login-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-login-content h2 {
            margin-bottom: 20px;
            color: #0a1f44;
        }

        .modal-login-content input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .modal-login-content button {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-login-content button:hover {
            background: #2563eb;
        }

        /* Scroll personalizado */
        .usuarios-conectados::-webkit-scrollbar,
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .usuarios-conectados::-webkit-scrollbar-track,
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .usuarios-conectados::-webkit-scrollbar-thumb,
        .chat-messages::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .usuarios-conectados::-webkit-scrollbar-thumb:hover,
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Estado desconectado */
        .estado-desconectado {
            padding: 15px;
            background: #fee;
            color: #c33;
            text-align: center;
            border-bottom: 1px solid #ddd;
            display: none;
        }

        .estado-desconectado.mostrar {
            display: block;
        }

        .btn-eliminar-chat {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-eliminar-chat:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <!-- Modal de entrada de nombre -->
    <div class="modal-login active" id="modalLogin">
        <div class="modal-login-content">
            <h2>👋 Bienvenido al Chat</h2>
            <p style="color: #666; margin-bottom: 20px;">Ingresa tu nombre para empezar</p>
            <input type="text" id="inputNombre" placeholder="Tu nombre" autocomplete="off">
            <button onclick="conectarUsuario()">Conectar</button>
        </div>
    </div>

    <!-- Chat principal -->
    <div class="chat-container" id="chatContainer" style="display: none;">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <span>💬 Chat</span>
                <div style="display: flex; gap: 8px;">
                    <button onclick="volverInicio()" title="Volver al inicio">🏠</button>
                    <button onclick="desconectar()" title="Desconectar">🚪</button>
                </div>
            </div>
            <div class="usuarios-conectados" id="usuariosConectados">
                <div class="usuario-item global" onclick="seleccionarChat('global')">
                    <div class="usuario-avatar">🌍</div>
                    <div class="usuario-info">
                        <div class="usuario-nombre">Chat Global</div>
                        <div class="usuario-estado">Público</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat principal -->
        <div class="chat-principal">
            <div class="estado-desconectado" id="estadoDesconectado">
                ⚠️ Desconectado. Reconectando...
            </div>

            <div class="chat-header">
                <div class="chat-header-info">
                    <span id="chatTitulo">💬 Chat Global</span>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span id="usuariosOnline">👥 0 usuarios</span>
                    <button class="btn-eliminar-chat" onclick="eliminarChat()">🗑️ Eliminar</button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages"></div>

            <div class="chat-input-area">
                <input 
                    type="text" 
                    id="inputMensaje" 
                    placeholder="Escribe un mensaje..." 
                    onkeypress="if(event.key === 'Enter') enviarMensaje()"
                    autocomplete="off"
                >
                <button onclick="enviarMensaje()">➤</button>
            </div>
        </div>
    </div>

    <script>
        const USUARIO_ID = '<?php echo $usuario_id; ?>';
        const API_URL = 'api/';
        let nombreUsuario = '';
        let chatActual = 'global';
        let ultimoMensajeId = 0;
        let conectado = false;
        let tiempoReconexion = 3000;

        // Conectar usuario
        async function conectarUsuario() {
            const nombre = document.getElementById('inputNombre').value.trim();
            
            if (!nombre) {
                alert('Por favor ingresa tu nombre');
                return;
            }

            nombreUsuario = nombre;
            
            try {
                const response = await fetch(API_URL + 'conectar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario_id: USUARIO_ID, nombre: nombre })
                });

                const data = await response.json();
                
                if (data.success) {
                    conectado = true;
                    document.getElementById('modalLogin').classList.remove('active');
                    document.getElementById('chatContainer').style.display = 'flex';
                    document.getElementById('inputMensaje').focus();
                    
                    // Iniciar actualización de mensajes
                    actualizarMensajes();
                    actualizarUsuarios();
                    setInterval(actualizarMensajes, 1000);
                    setInterval(actualizarUsuarios, 2000);
                } else {
                    alert('Error al conectar: ' + data.mensaje);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        }

        // Enviar mensaje
        async function enviarMensaje() {
            const texto = document.getElementById('inputMensaje').value.trim();
            
            if (!texto || !conectado) return;

            try {
                const payload = {
                    usuario_id: USUARIO_ID,
                    nombre_usuario: nombreUsuario,
                    texto: texto
                };

                if (chatActual === 'global') {
                    payload.tipo = 'global';
                    payload.destinatario = null;
                } else {
                    payload.tipo = 'privado';
                    payload.usuario1 = USUARIO_ID;
                    payload.usuario2 = chatActual;
                }

                await fetch(API_URL + 'enviar_mensaje.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                document.getElementById('inputMensaje').value = '';
                document.getElementById('inputMensaje').focus();
            } catch (error) {
                console.error('Error al enviar:', error);
            }
        }

        // Actualizar mensajes
        async function actualizarMensajes() {
            if (!conectado) return;

            try {
                let url = API_URL + 'obtener_mensajes.php?ultimo=' + ultimoMensajeId;
                
                if (chatActual === 'global') {
                    url += '&tipo=global';
                } else {
                    // Para chat privado, enviar los dos IDs ordenados
                    const ids = [USUARIO_ID, chatActual].sort();
                    url += '&tipo=privado&usuario1=' + ids[0] + '&usuario2=' + ids[1];
                }
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success && data.mensajes.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');
                    
                    data.mensajes.forEach(msg => {
                        agregarMensajeUI(msg);
                        ultimoMensajeId = msg.id;
                    });

                    // Scroll al último mensaje
                    setTimeout(() => {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 100);
                }
            } catch (error) {
                console.error('Error al actualizar:', error);
            }
        }

        // Agregar mensaje a la UI
        function agregarMensajeUI(mensaje) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Verificar si el mensaje ya existe
            if (document.getElementById('msg-' + mensaje.id)) return;

            const esPropio = mensaje.usuario_id === USUARIO_ID;
            const div = document.createElement('div');
            div.id = 'msg-' + mensaje.id;
            div.className = 'mensaje ' + (esPropio ? 'propio' : '');
            
            const hora = new Date(mensaje.timestamp).toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            div.innerHTML = `
                <div class="mensaje-contenido">
                    <div class="mensaje-usuario">${mensaje.nombre_usuario}</div>
                    <div>${mensaje.texto}</div>
                    <div class="mensaje-timestamp">${hora}</div>
                </div>
            `;
            
            chatMessages.appendChild(div);
        }

        // Actualizar usuarios conectados
        async function actualizarUsuarios() {
            if (!conectado) return;

            try {
                const response = await fetch(API_URL + 'obtener_usuarios.php');
                const data = await response.json();
                
                if (data.success) {
                    const usuariosDiv = document.getElementById('usuariosConectados');
                    document.getElementById('usuariosOnline').textContent = '👥 ' + data.usuarios.length + ' usuarios';
                    
                    // Remover usuarios antiguos (excepto Chat Global)
                    const usuariosElements = usuariosDiv.querySelectorAll('.usuario-item:not(.global)');
                    usuariosElements.forEach(el => el.remove());
                    
                    // Agregar usuarios nuevos
                    data.usuarios.forEach(usuario => {
                        if (usuario.usuario_id !== USUARIO_ID) {
                            const div = document.createElement('div');
                            div.className = 'usuario-item';
                            if (chatActual === usuario.usuario_id) {
                                div.classList.add('active');
                            }
                            
                            const inicial = usuario.nombre.charAt(0).toUpperCase();
                            div.innerHTML = `
                                <div class="usuario-content" onclick="seleccionarChat('${usuario.usuario_id}', '${usuario.nombre.replace(/'/g, "\\'")}')">
                                    <div class="usuario-avatar">${inicial}</div>
                                    <div class="usuario-info">
                                        <div class="usuario-nombre">${usuario.nombre}</div>
                                        <div class="usuario-estado"><span class="online-indicator"></span> En línea</div>
                                    </div>
                                </div>
                                <div class="usuario-actions">
                                    <button class="btn-delete-usuario" onclick="eliminarUsuarioChat('${usuario.usuario_id}', '${usuario.nombre.replace(/'/g, "\\'")}', event)">✕</button>
                                </div>
                            `;
                            
                            usuariosDiv.appendChild(div);
                        }
                    });
                }
            } catch (error) {
                console.error('Error al actualizar usuarios:', error);
            }
        }

        // Seleccionar chat
        function seleccionarChat(tipo, nombre = null) {
            chatActual = tipo;
            ultimoMensajeId = 0;
            
            // Limpiar todos los activos
            document.querySelectorAll('.usuario-item').forEach(el => el.classList.remove('active'));
            
            // Actualizar UI según el tipo
            if (tipo === 'global') {
                document.getElementById('chatTitulo').textContent = '💬 Chat Global';
                // Marcar como activo el chat global
                document.querySelector('.usuario-item.global').classList.add('active');
            } else {
                document.getElementById('chatTitulo').textContent = '🔒 Chat privado con ' + nombre;
                // Marcar como activo el usuario seleccionado
                document.querySelectorAll('.usuario-item').forEach(el => {
                    const usuarioNombre = el.querySelector('.usuario-nombre');
                    if (usuarioNombre && usuarioNombre.textContent === nombre) {
                        el.classList.add('active');
                    }
                });
            }

            // Limpiar mensajes
            document.getElementById('chatMessages').innerHTML = '';
            
            // Actualizar mensajes
            actualizarMensajes();
        }

        // Eliminar usuario/chat privado
        async function eliminarUsuarioChat(usuarioId, usuarioNombre, event) {
            event.stopPropagation();
            
            if (!confirm(`¿Estás seguro de que quieres eliminar el chat privado con ${usuarioNombre}?`)) {
                return;
            }

            try {
                const ids = [USUARIO_ID, usuarioId].sort();
                
                const response = await fetch(API_URL + 'eliminar_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        usuario_id: USUARIO_ID,
                        tipo: 'privado',
                        usuario1: ids[0],
                        usuario2: ids[1]
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Si es el chat actual, cambiar al chat global
                    if (chatActual === usuarioId) {
                        seleccionarChat('global');
                    }
                    
                    // Actualizar la lista de usuarios
                    actualizarUsuarios();
                    alert(`✓ Chat con ${usuarioNombre} eliminado`);
                } else {
                    alert('Error: ' + data.mensaje);
                }
            } catch (error) {
                console.error('Error al eliminar:', error);
                alert('Error al eliminar el chat');
            }
        }

        // Eliminar chat
        async function eliminarChat() {
            const titulo = chatActual === 'global' ? 'Chat Global' : 'este chat privado';
            
            if (!confirm('¿Estás seguro de que quieres eliminar todos los mensajes de ' + titulo + '?')) {
                return;
            }

            try {
                const payload = { usuario_id: USUARIO_ID };

                if (chatActual === 'global') {
                    payload.tipo = 'global';
                } else {
                    payload.tipo = 'privado';
                    const ids = [USUARIO_ID, chatActual].sort();
                    payload.usuario1 = ids[0];
                    payload.usuario2 = ids[1];
                }

                const response = await fetch(API_URL + 'eliminar_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('✓ Chat eliminado correctamente');
                    ultimoMensajeId = 0;
                    document.getElementById('chatMessages').innerHTML = '';
                    actualizarMensajes();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            } catch (error) {
                console.error('Error al eliminar:', error);
                alert('Error al eliminar el chat');
            }
        }

        // Volver al inicio
        async function volverInicio() {
            try {
                // Desconectar el usuario antes de ir al inicio
                await fetch(API_URL + 'desconectar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario_id: USUARIO_ID })
                });
            } catch (error) {
                console.error('Error:', error);
            }
            
            // Redirigir al inicio
            window.location.href = '../index.php';
        }

        // Desconectar
        async function desconectar() {
            conectado = false;
            
            try {
                await fetch(API_URL + 'desconectar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario_id: USUARIO_ID })
                });
            } catch (error) {
                console.error('Error:', error);
            }

            document.getElementById('chatContainer').style.display = 'none';
            document.getElementById('modalLogin').classList.add('active');
            document.getElementById('inputNombre').value = '';
            document.getElementById('inputNombre').focus();
        }

        // Inicializar
        document.getElementById('inputNombre').focus();
    </script>
</body>
</html>
