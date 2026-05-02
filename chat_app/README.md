# 💬 Chat Online - Sistema de Mensajería tipo WhatsApp

## 🚀 Características

✅ **Chat Global** - Mensajes visibles para todos
✅ **Chats Privados** - Mensajes directos entre usuarios
✅ **Usuarios en Línea** - Lista actualizada en tiempo real
✅ **Interfaz Moderna** - Diseño responsive inspirado en WhatsApp
✅ **Almacenamiento Temporal** - Mensajes guardados en JSON
✅ **Log del Servidor** - Registro de todas las acciones
✅ **Sin congelamiento** - Actualizaciones asincrónicas con AJAX

---

## 📁 Estructura del Proyecto

```
chat_app/
├── chat.php                    # Página principal del chat
├── api/
│   ├── conectar.php           # Conectar un usuario
│   ├── desconectar.php        # Desconectar un usuario
│   ├── enviar_mensaje.php     # Enviar mensajes
│   ├── obtener_mensajes.php   # Obtener mensajes (global o privados)
│   └── obtener_usuarios.php   # Obtener lista de usuarios conectados
└── datos/
    ├── usuarios.json          # Usuarios conectados
    ├── mensajes_global.json   # Mensajes del chat global
    ├── privado_xxx_yyy.json   # Chats privados
    └── servidor.log           # Log de operaciones
```

---

## 🔧 Instalación y Uso

### Requisitos
- PHP 7.0+
- XAMPP/WAMP/LAMP ejecutándose
- Navegador moderno (Chrome, Firefox, Edge)

### Pasos para usar

1. **Abrir el chat:**
   ```
   http://localhost/sistema-online/chat_app/chat.php
   ```

2. **Ingresar nombre de usuario:**
   - Se abrirá un modal pidiendo tu nombre
   - Ingresa tu nombre y haz clic en "Conectar"

3. **Chat Global:**
   - Haz clic en "Chat Global" en la lista
   - Escribe tu mensaje y presiona Enter o haz clic en el botón enviar

4. **Chats Privados:**
   - Haz clic en el nombre de un usuario conectado
   - Escribe el mensaje privado
   - Solo tú y ese usuario verán el mensaje

5. **Desconectar:**
   - Haz clic en el botón de puerta (🚪) en la esquina superior derecha

---

## 📊 Funcionamiento

### Frontend (chat.php)
- Interfaz HTML + CSS Moderno
- JavaScript para manejo de eventos
- AJAX para actualizaciones sin recarga

### Backend (API)
- **conectar.php**: Agrega usuario a usuarios.json
- **enviar_mensaje.php**: Guarda mensaje en JSON y en servidor.log
- **obtener_mensajes.php**: Devuelve mensajes nuevos desde el último ID
- **obtener_usuarios.php**: Lista todos los usuarios en línea
- **desconectar.php**: Remueve usuario de usuarios.json

### Almacenamiento
- **usuarios.json**: {usuario_id, nombre, timestamp, estado}
- **mensajes_global.json**: Array de mensajes públicos
- **privado_xxx_yyy.json**: Chats privados (nombres ordenados)
- **servidor.log**: Log de todas las acciones

---

## 🎨 Características de la Interfaz

### Diseño Responsive
- Adapta a dispositivos móviles y escritorio
- Colores modernos (azul y morado)
- Animaciones suaves

### Navegación
- Sidebar con lista de usuarios
- Chat principal con historial
- Input de mensajes en la parte inferior

### Indicadores
- 🌍 Chat Global
- 🔒 Chats Privados
- 👥 Contador de usuarios conectados
- 🟢 Indicador de conexión

---

## ⚙️ Configuración Avanzada

### Cambiar puerto o host
En `chat.php`, busca:
```javascript
const API_URL = 'api/';
```

### Cambiar intervalo de actualización
En `chat.php`, líneas de setInterval:
```javascript
setInterval(actualizarMensajes, 1000);   // Cada 1 segundo
setInterval(actualizarUsuarios, 2000);   // Cada 2 segundos
```

---

## 📝 Notas

- Los mensajes se almacenan en `/datos/` en JSON
- El log del servidor está en `/datos/servidor.log`
- Los datos se limpian cuando se reinicia el servidor (JSON temporal)
- Los chats privados se crean automáticamente
- Las sesiones están manejadas por PHP sessions

---

## 🐛 Solución de Problemas

### No se conecta
- Verifica que XAMPP esté ejecutándose
- Asegúrate que la carpeta `datos/` tenga permisos de escritura
- Comprueba la consola del navegador (F12)

### No se ven otros usuarios
- Abre `chat.php` en otra pestaña/navegador
- Ingresa con otro nombre de usuario
- Espera a que se actualice la lista (2 segundos)

### Los mensajes no se guardan
- Verifica que `/datos/` sea escribible: `chmod 777 datos/`
- Comprueba los permisos en la carpeta del proyecto

---

## 📞 Soporte

Si tienes problemas, revisa:
1. `datos/servidor.log` - Ver las acciones registradas
2. Consola del navegador (F12) - Ver errores JavaScript
3. Consola PHP - Ver errores del servidor

---

**Versión:** 1.0
**Última actualización:** 2026-05-02
