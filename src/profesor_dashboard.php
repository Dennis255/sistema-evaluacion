<?php
// dashboard_profesor.php (o el nombre que tenga tu panel docente)
session_start();

// Validar que el usuario esté logueado y sea profesor
// (Ajusta 'rol' y 'profesor' si usaste otros nombres en tu sistema de login)
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

require 'config.php';
$pdo = getDBConnection();

// Consultar todas las pruebas creadas
try {
    $stmt = $pdo->query("SELECT * FROM pruebas ORDER BY id ASC");
    $pruebas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar las pruebas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente - Sistema de Inglés</title>
    <!-- Usando Bootstrap para que se vea profesional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">👨‍🏫 Panel Docente</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            <!-- Agrega este botón donde prefieras en tu panel de profesor -->
            <a href="registrar_estudiante.php" class="btn btn-outline-success btn-sm">➕ Registrar Nuevo Estudiante</a>
            <a href="gestionar_usuarios.php" class="btn btn-outline-warning btn-sm">⚙️ Gestionar Usuarios</a>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mis Evaluaciones</h2>
            <a href="crear_prueba.php" class="btn btn-success">+ Nueva Prueba</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#ID</th>
                                <th>Título del Tema</th>
                                <th>Descripción</th>
                                <th>Tiempo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pruebas as $prueba): ?>
                                <tr>
                                    <td><?= $prueba['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($prueba['titulo']) ?></td>
                                    <td><?= htmlspecialchars($prueba['descripcion']) ?></td>
                                    <td><?= $prueba['tiempo_minutos'] ?> min</td>
                                    <td>
                                        <!-- Colores dinámicos según el estado -->
                                        <?php if ($prueba['estado'] === 'activa'): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php elseif ($prueba['estado'] === 'cerrada'): ?>
                                            <span class="badge bg-danger">Cerrada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Borrador</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Estos enlaces los programaremos después -->
                                        <a href="editar_prueba.php?id=<?= $prueba['id'] ?>"
                                            class="btn btn-primary btn-sm">✏️ Editar</a>
                                        <a href="estado_prueba.php?id=<?= $prueba['id'] ?>"
                                            class="btn btn-warning btn-sm">🔄 Estado</a>
                                        <a href="ver_estadisticas.php?id=<?= $prueba['id'] ?>"
                                            class="btn btn-info btn-sm mb-1 text-white">📊 Estadísticas</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (count($pruebas) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay pruebas creadas aún.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>