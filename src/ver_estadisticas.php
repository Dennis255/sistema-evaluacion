<?php
session_start();
require 'config.php';
$pdo = getDBConnection();

// Validar acceso del profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$prueba_id = $_GET['id'] ?? null;

if (!$prueba_id) {
    header("Location: profesor_dashboard.php");
    exit;
}

try {
    // 1. Obtener datos de la prueba
    $stmtPrueba = $pdo->prepare("SELECT * FROM pruebas WHERE id = ?");
    $stmtPrueba->execute([$prueba_id]);
    $prueba = $stmtPrueba->fetch(PDO::FETCH_ASSOC);

    if (!$prueba) {
        die("La prueba no existe.");
    }

    // 2. Obtener estadísticas generales de resultados (Alumnos que rindieron, nota máx, mín y promedio)
    $stmtStats = $pdo->prepare("
        SELECT 
            COUNT(r.id) AS total_alumnos,
            MAX(r.calificacion) AS nota_maxima,
            MIN(r.calificacion) AS nota_minima,
            AVG(r.calificacion) AS nota_promedio
        FROM resultados r
        WHERE r.prueba_id = ?
    ");
    $stmtStats->execute([$prueba_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // 3. Obtener el listado de los alumnos que rindieron la prueba incluyendo su ID (estudiante_id)
    $stmtAlumnos = $pdo->prepare("
        SELECT u.id AS estudiante_id, u.nombre, u.email, r.calificacion, r.fecha_rendicion
        FROM resultados r
        JOIN usuarios u ON r.estudiante_id = u.id
        WHERE r.prueba_id = ?
        ORDER BY r.calificacion DESC
    ");
    $stmtAlumnos->execute([$prueba_id]);
    $alumnos_resultados = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas: <?= htmlspecialchars($prueba['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="profesor_dashboard.php">👨‍🏫 Panel Docente</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>📊 Analítica de la Evaluación</h2>
                <h4 class="text-primary"><?= htmlspecialchars($prueba['titulo']) ?></h4>
            </div>
            <a href="profesor_dashboard.php" class="btn btn-outline-secondary">⬅ Volver al Panel</a>
        </div>

        <!-- Tarjetas de Resumen Estadístico (Insights para el Docente) -->
        <div class="row text-center mb-5">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-primary text-white">
                    <div class="card-body">
                        <h6>Alumnos Evaluados</h6>
                        <h2 class="fw-bold"><?= $stats['total_alumnos'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-success text-white">
                    <div class="card-body">
                        <h6>Nota Máxima</h6>
                        <h2 class="fw-bold"><?= $stats['nota_maxima'] !== null ? $stats['nota_maxima'] : 'N/A' ?> / 10</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-warning text-dark">
                    <div class="card-body">
                        <h6>Nota Promedio</h6>
                        <h2 class="fw-bold">
                            <?= $stats['nota_promedio'] !== null ? round($stats['nota_promedio'], 2) : 'N/A' ?> / 10
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 bg-danger text-white">
                    <div class="card-body">
                        <h6>Nota Mínima</h6>
                        <h2 class="fw-bold"><?= $stats['nota_minima'] !== null ? $stats['nota_minima'] : 'N/A' ?> / 10</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Detallada de Estudiantes -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="mb-3 text-secondary fw-bold">🎓 Calificaciones por Estudiante</h4>
                <p class="text-muted small mb-4">Lista de alumnos que han completado esta evaluación y su respectivo rendimiento.</p>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre del Estudiante</th>
                                <th>Correo Electrónico</th>
                                <th>Fecha de Rendición</th>
                                <th>Calificación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos_resultados as $index => $res): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($res['nombre']) ?></td>
                                    <td><?= htmlspecialchars($res['email']) ?></td>
                                    <td><small><?= $res['fecha_rendicion'] ?></small></td>
                                    <td>
                                        <?php if ($res['calificacion'] >= 7): ?>
                                            <span class="badge bg-success fs-6"><?= $res['calificacion'] ?> / 10</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6"><?= $res['calificacion'] ?> / 10</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Botón para ver el examen del alumno -->
                                        <a href="ver_intento_estudiante.php?estudiante_id=<?= $res['estudiante_id'] ?>&prueba_id=<?= $prueba_id ?>" class="btn btn-sm btn-primary">🔍 Ver Examen</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (count($alumnos_resultados) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Ningún estudiante ha rendido esta prueba todavía.</td>
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