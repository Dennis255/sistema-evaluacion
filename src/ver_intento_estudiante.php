<?php
session_start();
require 'config.php';
$pdo = getDBConnection();

// Validar que sea un profesor autenticado
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$estudiante_id = $_GET['estudiante_id'] ?? null;
$prueba_id = $_GET['prueba_id'] ?? null;

if (!$estudiante_id || !$prueba_id) {
    header("Location: profesor_dashboard.php");
    exit;
}

try {
    // 1. Obtener datos del estudiante y de la prueba
    $stmtEst = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmtEst->execute([$estudiante_id]);
    $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

    $stmtPrueba = $pdo->prepare("SELECT * FROM pruebas WHERE id = ?");
    $stmtPrueba->execute([$prueba_id]);
    $prueba = $stmtPrueba->fetch(PDO::FETCH_ASSOC);

    $stmtRes = $pdo->prepare("SELECT calificacion, fecha_rendicion FROM resultados WHERE estudiante_id = ? AND prueba_id = ?");
    $stmtRes->execute([$estudiante_id, $prueba_id]);
    $resultado = $stmtRes->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante || !$prueba || !$resultado) {
        die("No se encontraron registros de este intento.");
    }

    // 2. Obtener las preguntas y evaluar qué respondió el alumno en su momento 
    // (Nota: Si guardas las respuestas detalladas por opción seleccionada, puedes consultarlas aquí. 
    //  Como alternativa robusta, listamos las preguntas, la opción correcta y el feedback).
    $stmt_preguntas = $pdo->prepare("SELECT id, texto_pregunta, retroalimentacion FROM preguntas WHERE prueba_id = ?");
    $stmt_preguntas->execute([$prueba_id]);
    $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Examen - Alumno</title>
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
            <h2>🔍 Auditoría de Examen</h2>
            <h5 class="text-muted">Estudiante: <span class="text-dark fw-bold"><?= htmlspecialchars($estudiante['nombre']) ?></span> (<?= htmlspecialchars($estudiante['email']) ?>)</h5>
        </div>
        <a href="ver_estadisticas.php?id=<?= $prueba_id ?>" class="btn btn-outline-secondary">⬅ Volver a Estadísticas</a>
    </div>

    <!-- Tarjeta de Resumen -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center bg-white rounded">
            <div>
                <h4 class="text-primary fw-bold mb-1"><?= htmlspecialchars($prueba['titulo']) ?></h4>
                <p class="text-muted mb-0"><small>Fecha de realización: <?= $resultado['fecha_rendicion'] ?></small></p>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">Calificación Obtenida</span>
                <h3 class="fw-bold text-success mb-0"><?= $resultado['calificacion'] ?> / 10</h3>
            </div>
        </div>
    </div>

    <h4 class="mb-3 text-secondary fw-bold">Desglose de Preguntas y Respuestas</h4>
    <p class="text-muted small mb-4">Revisa las opciones correctas y el feedback pedagógico configurado para este tema.</p>

    <?php foreach ($preguntas as $index => $pregunta): ?>
        <?php
            // Obtener las opciones de esta pregunta
            $stmtOpc = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = ?");
            $stmtOpc->execute([$pregunta['id']]);
            $opciones = $stmtOpc->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><?= ($index + 1) . ". " . htmlspecialchars($pregunta['texto_pregunta']) ?></h5>
                
                <ul class="list-group mb-3">
                    <?php foreach ($opciones as $opcion): ?>
                        <?php 
                            $estiloOpcion = "";
                            if ($opcion['es_correcta']) {
                                $estiloOpcion = "list-group-item-success fw-bold"; // Resalta la correcta en verde
                            }
                        ?>
                        <li class="list-group-item <?= $estiloOpcion ?>">
                            <?= htmlspecialchars($opcion['texto_opcion']) ?>
                            <?= $opcion['es_correcta'] ? ' <span class="float-end">✅ (Respuesta Correcta)</span>' : '' ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty(trim($pregunta['retroalimentacion']))): ?>
                    <div class="alert alert-info mb-0 border-0 bg-opacity-10 bg-primary text-dark">
                        <strong>💡 Feedback pedagógico de refuerzo:</strong><br>
                        <?= nl2br(htmlspecialchars($pregunta['retroalimentacion'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="mt-4 text-center">
        <a href="ver_estadisticas.php?id=<?= $prueba_id ?>" class="btn btn-primary px-5 shadow">Volver a Estadísticas</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>