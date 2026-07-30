<?php
// registrar_estudiante.php
session_start();
require 'config.php';

// Validar que solo el profesor pueda acceder
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$pdo = getDBConnection();
$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = 'estudiante'; // Rol fijo para asegurar que se registre como alumno

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        try {
            // Verificar si el correo ya existe en la base de datos
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtCheck->execute([$email]);
            
            if ($stmtCheck->rowCount() > 0) {
                $mensaje = "El correo electrónico ya está registrado en el sistema.";
                $tipo_alerta = "danger";
            } else {
                // Encriptar la contraseña por seguridad
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insertar el nuevo estudiante
                $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$nombre, $email, $password_hash, $rol]);

                $mensaje = "¡Estudiante registrado con éxito! Ya puede iniciar sesión.";
                $tipo_alerta = "success";
            }
        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . $e->getMessage();
            $tipo_alerta = "danger";
        }
    } else {
        $mensaje = "Por favor, completa todos los campos.";
        $tipo_alerta = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante - Panel Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="profesor_dashboard.php">👨‍🏫 Panel Docente</a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="mb-3 text-center">Registrar Nuevo Estudiante</h3>
                    <p class="text-muted text-center mb-4">Crea las credenciales de acceso para tus alumnos.</p>

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_alerta ?> text-center" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form action="registrar_estudiante.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo del Estudiante</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico (Para Login)</label>
                            <input type="email" name="email" class="form-control" required placeholder="estudiante@email.com">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña Temporal</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Guardar Estudiante</button>
                            <a href="profesor_dashboard.php" class="btn btn-outline-secondary">Volver al Panel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>