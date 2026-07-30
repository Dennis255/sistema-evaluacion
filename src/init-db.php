<?php
// Configuración de la base de datos (Credenciales de Render)
$host     = 'dpg-d9lrn8710e5c73e949ug-a.oregon-postgres.render.com';
$port     = '5432';
$dbname   = 'sistema_pruebas';
$user     = 'sistema_pruebas_user';
$password = 'KCSN7wvyzU7zJJtqBiu4ZQJHQbgz67sz';

// Construcción del DSN (Data Source Name) exigiendo SSL para Render
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

$sql = "
-- 1. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) CHECK (rol IN ('profesor', 'estudiante')) NOT NULL
);

-- 2. Tabla de Pruebas (Tests)
CREATE TABLE IF NOT EXISTS pruebas (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tiempo_minutos INT NOT NULL,
    estado VARCHAR(20) CHECK (estado IN ('borrador', 'activa', 'cerrada')) DEFAULT 'borrador'
);

-- 3. Tabla de Preguntas con Retroalimentación
CREATE TABLE IF NOT EXISTS preguntas (
    id SERIAL PRIMARY KEY,
    prueba_id INT NOT NULL,
    texto_pregunta TEXT NOT NULL,
    retroalimentacion TEXT,
    FOREIGN KEY (prueba_id) REFERENCES pruebas(id) ON DELETE CASCADE
);

-- 4. Tabla de Opciones
CREATE TABLE IF NOT EXISTS opciones (
    id SERIAL PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto_opcion VARCHAR(255) NOT NULL,
    es_correcta BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
);

-- 5. Tabla de Resultados
CREATE TABLE IF NOT EXISTS resultados (
    id SERIAL PRIMARY KEY,
    prueba_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    calificacion DECIMAL(5,2) NOT NULL,
    fecha_rendicion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prueba_id) REFERENCES pruebas(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
";

try {
    echo "Conectando a PostgreSQL en Render...\n";
    
    // Crear la conexión PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Conexión exitosa. Creando tablas...\n";
    
    // Ejecutar las sentencias SQL
    $pdo->exec($sql);
    
    echo "¡Tablas creadas correctamente!\n";

} catch (PDOException $e) {
    echo "Error al conectar o ejecutar el script:\n" . $e->getMessage() . "\n";
}