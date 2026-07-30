<?php
// config.php

function getDBConnection() {
    // Reemplaza estos valores con las credenciales de tu base de datos PostgreSQL en Render
    $host = 'dpg-xxxxxxxxxxxx-a.oregon-postgres.render.com'; // Tu Internal/External Database URL o Host
    $dbname = 'nombre_de_tu_bd';                              // Database Name
    $user = 'usuario_de_tu_bd';                               // Username
    $password = 'tu_contraseña_de_render';                    // Password
    $port = '5432';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}
?>