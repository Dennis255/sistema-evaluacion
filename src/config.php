<?php
// db.php - Conexión y migración automática para Render / GitHub

function getDBConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        // Obtenemos las credenciales desde variables de entorno (recomendado) 
        // o usamos los valores por defecto si no están definidas.
        $host = getenv('DB_HOST') ?: 'dpg-d9lrn8710e5c73e949ug-a.oregon-postgres.render.com';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'sistema_pruebas';
        $user = getenv('DB_USER') ?: 'sistema_pruebas_user';
        $password = getenv('DB_PASS') ?: 'KCSN7wvyzU7zJJtqBiu4ZQJHQbgz67sz';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

        try {
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Ejecutar la creación inicial de tablas de forma segura
            inicializarEstructuraBD($pdo);

        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    return $pdo;
}

function inicializarEstructuraBD(PDO $pdo)
{
    // El uso de IF NOT EXISTS garantiza que si la tabla ya existe con datos, NO SE BORRA NI MODIFICA.
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

    -- 3. Tabla de Preguntas
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
    CREATE TABLE IF NOT EXISTS detalle_resultados (
    id SERIAL PRIMARY KEY,
    resultado_id INT REFERENCES resultados(id) ON DELETE CASCADE,
    pregunta_id INT REFERENCES preguntas(id) ON DELETE CASCADE,
    opcion_elegida_id INT REFERENCES opciones(id) ON DELETE CASCADE,
    es_acierto BOOLEAN NOT NULL
);
    ";

    $pdo->exec($sql);
}