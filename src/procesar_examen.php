<?php
session_start();
require 'config.php';
$pdo = getDBConnection();

// Validar acceso del estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prueba_id = $_POST['prueba_id'];
    $estudiante_id = $_SESSION['user_id'];
    // El array de respuestas que enviamos desde el formulario
    $respuestas_estudiante = isset($_POST['respuestas']) ? $_POST['respuestas'] : [];

    try {
        // 1. Obtener todas las preguntas de esta prueba para evaluar
        $stmt_preguntas = $pdo->prepare("SELECT id, texto_pregunta, retroalimentacion FROM preguntas WHERE prueba_id = ?");
        $stmt_preguntas->execute([$prueba_id]);
        $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

        $total_preguntas = count($preguntas);
        $respuestas_correctas = 0;
        
        // Array para guardar el detalle y mostrarlo en la vista
        $reporte = []; 

        foreach ($preguntas as $pregunta) {
            $pregunta_id = $pregunta['id'];
            
            // Buscar cuál era la opción correcta real en la base de datos
            $stmt_correcta = $pdo->prepare("SELECT id, texto_opcion FROM opciones WHERE pregunta_id = ? AND es_correcta = TRUE");
            $stmt_correcta->execute([$pregunta_id]);
            $opcion_correcta = $stmt_correcta->fetch(PDO::FETCH_ASSOC);

            // Verificar qué respondió el estudiante
            $opcion_elegida_id = isset($respuestas_estudiante[$pregunta_id]) ? $respuestas_estudiante[$pregunta_id] : null;
            $texto_elegida = "No respondida (Tiempo agotado o saltada)";
            $es_acierto = false;

            if ($opcion_elegida_id) {
                // Obtener el texto de lo que eligió
                $stmt_elegida = $pdo->prepare("SELECT texto_opcion FROM opciones WHERE id = ?");
                $stmt_elegida->execute([$opcion_elegida_id]);
                $texto_elegida = $stmt_elegida->fetchColumn();

                // Si el ID de la opción que eligió es igual al ID de la opción correcta
                if ($opcion_elegida_id == $opcion_correcta['id']) {
                    $respuestas_correctas++;
                    $es_acierto = true;
                }
            }

            // Guardamos el detalle para imprimirlo en el HTML
            $reporte[] = [
                'texto_pregunta' => $pregunta['texto_pregunta'],
                'texto_elegida' => $texto_elegida,
                'texto_correcta' => $opcion_correcta['texto_opcion'],
                'es_acierto' => $es_acierto,
                // CORRECCIÓN: Si es null, le pasamos una cadena vacía para evitar el warning
                'retroalimentacion' => $pregunta['retroalimentacion'] ?? ''
            ];
        }

        // 2. Calcular la calificación sobre 10 (con 2 decimales)
        $calificacion_final = ($total_preguntas > 0) ? round(($respuestas_correctas / $total_preguntas) * 10, 2) : 0;

        // 3. Guardar el resultado en la base de datos
        $stmt_insert = $pdo->prepare("INSERT INTO resultados (prueba_id, estudiante_id, calificacion) VALUES (?, ?, ?)");
        $stmt_insert->execute([$prueba_id, $estudiante_id, $calificacion_final]);

    } catch (PDOException $e) {
        die("Error al procesar el examen: " . $e->getMessage());
    }
} else {
    header("Location: estudiante_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de la Prueba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- Tarjeta de Calificación Final -->
                <div class="card shadow border-0 mb-4 text-center">
                    <div class="card-body py-5 <?= $calificacion_final >= 7 ? 'bg-success text-white' : 'bg-warning text-dark' ?> rounded">
                        <h2 class="fw-bold">Calificación Final</h2>
                        <h1 class="display-1 fw-bold"><?= $calificacion_final ?> / 10</h1>
                        <p class="fs-5 mt-2">
                            Aciertos: <?= $respuestas_correctas ?> de <?= $total_preguntas ?> preguntas.
                        </p>
                    </div>
                </div>

                <!-- Análisis de Respuestas -->
                <h4 class="mb-3 text-primary fw-bold">Revisión y Retroalimentación</h4>
                <p class="text-muted">Revisa tus respuestas y la retroalimentación para mejorar en tu proceso de aprendizaje.</p>

                <?php foreach ($reporte as $index => $item): ?>
                    <div class="card mb-3 shadow-sm border-0 border-start border-4 <?= $item['es_acierto'] ? 'border-success' : 'border-danger' ?>">
                        <div class="card-body">
                            <h5 class="fw-bold"><?= ($index + 1) ?>. <?= htmlspecialchars($item['texto_pregunta']) ?></h5>
                            
                            <div class="mt-3">
                                <p class="mb-1">
                                    <strong>Tu respuesta:</strong> 
                                    <span class="<?= $item['es_acierto'] ? 'text-success fw-bold' : 'text-danger text-decoration-line-through' ?>">
                                        <?= htmlspecialchars($item['texto_elegida']) ?>
                                    </span>
                                </p>
                                
                                <?php if (!$item['es_acierto']): ?>
                                    <p class="mb-2 text-success">
                                        <strong>Respuesta correcta:</strong> <?= htmlspecialchars($item['texto_correcta']) ?>
                                    </p>
                                <?php else: ?>
                                    <span class="badge bg-success mt-1 mb-2">¡Correcto!</span>
                                <?php endif; ?>

                                <!-- CORRECCIÓN: Mostramos el feedback siempre que exista texto, sin importar si acertó o falló -->
                                <?php if (!empty(trim($item['retroalimentacion']))): ?>
                                    <div class="alert alert-info mt-3 mb-0 border-0 bg-opacity-10 bg-primary text-dark">
                                        <strong>💡 Feedback del profesor:</strong><br>
                                        <?= nl2br(htmlspecialchars($item['retroalimentacion'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-4 text-center">
                    <a href="estudiante_dashboard.php" class="btn btn-primary btn-lg px-5 shadow">Volver al Inicio</a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>