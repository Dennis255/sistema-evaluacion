<?php
// setup_users.php
require 'config.php';
$pdo = getDBConnection(); // ¡ESTA ES LA LÍNEA QUE FALTABA!

try {
    // 1. Crear el usuario Profesor (Tú)
    $password_profesor = password_hash('Profe', PASSWORD_DEFAULT);

    $stmt1 = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt1->execute(['Denis Calle', 'denis.profesor@email.com', $password_profesor, 'profesor']);

    // 2. Crear un usuario Estudiante
    $password_estudiante = password_hash('Alumno123!', PASSWORD_DEFAULT);

    $stmt2 = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt2->execute(['Juan Perez', 'juan.alumno@email.com', $password_estudiante, 'estudiante']);

    echo "¡Usuarios creados con éxito! Ya puedes probar el login.";
} catch (PDOException $e) {
    echo "Error al crear usuarios: " . $e->getMessage();
}
?>