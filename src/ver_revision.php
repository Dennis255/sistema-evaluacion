<?php
session_start();
require 'config.php';
$pdo = getDBConnection();

// Validar acceso del estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: index.php");
    exit;
}

$estudiante_id = $_SESSION['user_id'];
$prueba_id = $_GET['prueba_id'] ?? null;

if (!$prueba_id) {
    header("Location: estudiante_dashboard.php");
    exit;
}

try {
    // 1. Obtener datos de la prueba y la calificación obtenida por el estudiante
    $stmtPrueba = $pdo->prepare("SELECT * FROM pruebas WHERE id = ?");
    $stmtPrueba->execute([$prueba_id]);
    $prueba = $stmtPrueba->fetch(PDO::FETCH_ASSOC);

    $stmtRes = $pdo->prepare("SELECT calificacion FROM resultados WHERE estudiante_id = ? AND prueba_id = ? ORDER BY id DESC LIMIT 1");
    $stmtRes->execute([$estudiante_id, $prueba_id]);
    $resultado = $stmtRes->fetch(PDO::FETCH_ASSOC);

    if (!$prueba || !$resultado) {
        die("No se encontró información de esta prueba para este usuario.");
    }

    $calificacion_final = $resultado['calificacion'];

    // 2. Obtener las preguntas de la prueba
    $stmt_preguntas = $pdo->prepare("SELECT id, texto_pregunta, retroalimentacion FROM preguntas WHERE prueba_id = ?");
    $stmt_preguntas->execute([$prueba_id]);
    $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

    // Nota: Como alternativa ideal para revisión histórica detallada, 
    // mostramos la estructura base de preguntas y la respuesta correcta.
    $reporte = [];
    foreach ($preguntas as $pregunta) {
        $pregunta_id = $pregunta['id'];
        
        $stmt_correcta = $pdo->prepare("SELECT texto_opcion FROM opciones WHERE pregunta_id = ? AND es_correcta = TRUE");
        $stmt_correcta->execute([$pregunta_id]);
        $texto_correcta = $stmt_correcta->fetchColumn();

        $reporte[] = [
            'texto_pregunta' => $pregunta['texto_pregunta'],
            'texto_correcta' => $texto_correcta,
            'retroalimentacion' => $pregunta['retroalimentacion'] ?? ''
        ];
    }

} catch (PDOException $e) {
    die("Error al cargar la revisión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Prueba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>🔍 Revisión: <?= htmlspecialchars($prueba['titulo']) ?></h2>
                    <a href="estudiante_dashboard.php" class="btn btn-outline-secondary">⬅ Volver al Panel</a>
                </div>

                <div class="card shadow border-0 mb-4 text-center">
                    <div class="card-body py-4 bg-success text-white rounded">
                        <h4 class="fw-bold">Calificación Registrada</h4>
                        <h1 class="display-3 fw-bold"><?= $calificacion_final ?> / 10</h1>
                    </div>
                </div>

                <h4 class="mb-3 text-primary fw-bold">Banco de Preguntas y Respuestas Correctas</h4>
                <p class="text-muted">Repasa los conceptos clave de este tema para afianzar tu aprendizaje.</p>

                <?php foreach ($reporte as $index => $item): ?>
                    <div class="card mb-3 shadow-sm border-0 border-start border-4 border-success">
                        <div class="card-body">
                            <h5 class="fw-bold"><?= ($index + 1) ?>. <?= htmlspecialchars($item['texto_pregunta']) ?></h5>
                            
                            <div class="mt-3">
                                <p class="mb-2 text-success">
                                    <strong>Respuesta correcta:</strong> <?= htmlspecialchars($item['texto_correcta']) ?>
                                </p>
                                
                                <?php if (!empty(trim($item['retroalimentacion']))): ?>
                                    <div class="alert alert-info mt-3 mb-0 border-0 bg-opacity-10 bg-primary text-dark">
                                        <strong>💡 Feedback pedagógico:</strong><br>
                                        <?= nl2br(htmlspecialchars($item['retroalimentacion'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-4 text-center">
                    <a href="estudiante_dashboard.php" class="btn btn-primary btn-lg px-5 shadow">Volver al Panel</a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>