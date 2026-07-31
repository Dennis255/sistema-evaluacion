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
    // 1. Obtener datos de la prueba y el último resultado del estudiante
    $stmtPrueba = $pdo->prepare("SELECT * FROM pruebas WHERE id = ?");
    $stmtPrueba->execute([$prueba_id]);
    $prueba = $stmtPrueba->fetch(PDO::FETCH_ASSOC);

    $stmtRes = $pdo->prepare("SELECT id, calificacion, fecha_rendicion FROM resultados WHERE estudiante_id = ? AND prueba_id = ? ORDER BY id DESC LIMIT 1");
    $stmtRes->execute([$estudiante_id, $prueba_id]);
    $resultado = $stmtRes->fetch(PDO::FETCH_ASSOC);

    if (!$prueba || !$resultado) {
        die("No se encontró información de esta prueba para este usuario.");
    }

    $resultado_id = $resultado['id'];
    $calificacion_final = $resultado['calificacion'];

    // 2. Obtener las preguntas de la prueba
    $stmt_preguntas = $pdo->prepare("SELECT id, texto_pregunta, retroalimentacion FROM preguntas WHERE prueba_id = ?");
    $stmt_preguntas->execute([$prueba_id]);
    $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener el detalle de las respuestas que marcó el estudiante en este intento
    $stmtDetalle = $pdo->prepare("SELECT pregunta_id, opcion_elegida_id, es_acierto FROM detalle_resultados WHERE resultado_id = ?");
    $stmtDetalle->execute([$resultado_id]);
    $detalles_raw = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Indexar por pregunta_id
    $respuestas_alumno = [];
    foreach ($detalles_raw as $det) {
        $respuestas_alumno[$det['pregunta_id']] = [
            'opcion_elegida_id' => $det['opcion_elegida_id'],
            'es_acierto' => $det['es_acierto']
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
    <title>Revisión de Prueba - Estudiante</title>
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

                <!-- Tarjeta de Calificación -->
                <div class="card shadow border-0 mb-4 text-center">
                    <div class="card-body py-4 <?= $calificacion_final >= 7 ? 'bg-success text-white' : 'bg-warning text-dark' ?> rounded">
                        <h4 class="fw-bold">Calificación Registrada</h4>
                        <h1 class="display-3 fw-bold"><?= $calificacion_final ?> / 10</h1>
                        <p class="mb-0"><small>Fecha de realización: <?= $resultado['fecha_rendicion'] ?></small></p>
                    </div>
                </div>

                <h4 class="mb-3 text-primary fw-bold">Desglose de tus Respuestas</h4>
                <p class="text-muted">Revisa qué opciones seleccionaste, cuáles fueron correctas o incorrectas y lee la retroalimentación para afianzar tu aprendizaje.</p>

                <?php foreach ($preguntas as $index => $pregunta): ?>
                    <?php
                        $pregunta_id = $pregunta['id'];
                        
                        // Obtener las opciones de esta pregunta
                        $stmtOpc = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = ?");
                        $stmtOpc->execute([$pregunta_id]);
                        $opciones = $stmtOpc->fetchAll(PDO::FETCH_ASSOC);

                        // Obtener respuesta del estudiante
                        $info_respuesta = $respuestas_alumno[$pregunta_id] ?? null;
                        $opcion_elegida_id = $info_respuesta['opcion_elegida_id'] ?? null;
                        $es_acierto = $info_respuesta['es_acierto'] ?? false;
                    ?>
                    <div class="card mb-3 shadow-sm border-0 border-start border-4 <?= $es_acierto ? 'border-success' : 'border-danger' ?>">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3"><?= ($index + 1) . ". " . htmlspecialchars($pregunta['texto_pregunta']) ?></h5>
                            
                            <ul class="list-group mb-3">
                                <?php foreach ($opciones as $opcion): ?>
                                    <?php 
                                        $estiloOpcion = "";
                                        $badge = "";

                                        if ($opcion['es_correcta']) {
                                            $estiloOpcion = "list-group-item-success fw-bold";
                                            $badge = ' <span class="float-end badge bg-success">✅ Respuesta Correcta</span>';
                                        }

                                        if ($opcion['id'] == $opcion_elegida_id) {
                                            if ($opcion['es_correcta']) {
                                                $estiloOpcion = "list-group-item-success fw-bold border-2 border-dark";
                                                $badge = ' <span class="float-end badge bg-success">✅ Tu respuesta (Correcta)</span>';
                                            } else {
                                                $estiloOpcion = "list-group-item-danger text-decoration-line-through fw-bold";
                                                $badge = ' <span class="float-end badge bg-danger">❌ Tu respuesta (Incorrecta)</span>';
                                            }
                                        }
                                    ?>
                                    <li class="list-group-item <?= $estiloOpcion ?>">
                                        <?= htmlspecialchars($opcion['texto_opcion']) ?>
                                        <?= $badge ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php 
                                $feedback = $pregunta['retroalimentacion'] ?? '';
                                if (!empty(trim($feedback))): 
                            ?>
                                <div class="alert alert-info mb-0 border-0 bg-opacity-10 bg-primary text-dark">
                                    <strong>💡 Feedback pedagógico:</strong><br>
                                    <?= nl2br(htmlspecialchars($feedback)) ?>
                                </div>
                            <?php endif; ?>
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