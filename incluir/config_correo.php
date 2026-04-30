<?php

return [
    // SMTP activo por defecto para evitar dependencia de mail() en XAMPP.
    'usar_smtp' => true,

    // Ejemplo Gmail: host smtp.gmail.com, port 587, encryption tls.
    // Para Outlook: host smtp.office365.com, port 587, encryption tls.
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls', // tls o ssl

    // Credenciales del correo emisor.
    // En Gmail usa una clave de aplicacion, no tu clave normal.
    'username' => 'duranhumberto716@gmail.com',
    'password' => 'tu_clave_de_aplicacion',

    // Remitente visible.
    'from_email' => 'duranhumberto716@gmail.com',
    'from_name' => 'Tienda en Linea',
];
