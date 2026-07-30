<?php
// gestionar_usuarios.php
session_start();
require 'config.php';
$pdo = getDBConnection();

// Validar que solo un profesor pueda acceder a este panel de administración
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$mensaje = "";
$tipo_alerta = "";

// 1. Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol']; // 'profesor' o 'estudiante'

    if (!empty($nombre) && !empty($email) && !empty($password) && in_array($rol, ['profesor', 'estudiante'])) {
        try {
            // Verificar si el correo ya existe
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtCheck->execute([$email]);
            
            if ($stmtCheck->rowCount() > 0) {
                $mensaje = "El correo electrónico ya está registrado en el sistema.";
                $tipo_alerta = "danger";
            } else {
                // Encriptar contraseña de manera segura
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insertar usuario
                $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$nombre, $email, $password_hash, $rol]);

                $mensaje = "¡Usuario '$nombre' registrado exitosamente como $rol!";
                $tipo_alerta = "success";
            }
        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . $e->getMessage();
            $tipo_alerta = "danger";
        }
    } else {
        $mensaje = "Por favor, completa todos los campos correctamente.";
        $tipo_alerta = "warning";
    }
}

// 2. Obtener la lista de todos los usuarios registrados para mostrarlos en la tabla
try {
    $stmtUsuarios = $pdo->query("SELECT id, nombre, email, rol FROM usuarios ORDER BY id DESC");
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Panel Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="profesor_dashboard.php">👨‍🏫 Panel Docente</a>
        <div>
            <a href="profesor_dashboard.php" class="btn btn-outline-light btn-sm me-2">📊 Ver Pruebas</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row justify-content-center">
        
        <!-- Formulario de Registro Rápido -->
        <div class="col-md-5 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-primary fw-bold">➕ Nuevo Usuario</h4>
                    <p class="text-muted small mb-4">Crea cuentas de profesor o estudiante al instante.</p>

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_alerta ?> text-center small" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form action="gestionar_usuarios.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Kathe">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required placeholder="kathe.profesor@mail.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Rol en el Sistema</label>
                            <select name="rol" class="form-select" required>
                                <option value="estudiante">Estudiante</option>
                                <option value="profesor">Profesor / Administrador</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Usuarios Registrados -->
        <div class="col-md-7">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-secondary fw-bold">👥 Usuarios en el Sistema</h4>
                    <p class="text-muted small mb-3">Lista general de cuentas creadas.</p>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usr): ?>
                                <tr>
                                    <td><?= $usr['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($usr['nombre']) ?></td>
                                    <td><small><?= htmlspecialchars($usr['email']) ?></small></td>
                                    <td>
                                        <?php if ($usr['rol'] === 'profesor'): ?>
                                            <span class="badge bg-danger">Profesor</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Estudiante</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if (count($usuarios) === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No hay usuarios registrados.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>