<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['prueba_id'])) {
    header("Location: profesor_dashboard.php");
    exit;
}

$prueba_id = $_GET['prueba_id'];

// Procesar el formulario cuando se agrega una nueva pregunta
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $texto_pregunta = $_POST['texto_pregunta'];
    $retroalimentacion = $_POST['retroalimentacion'];
    $opciones = $_POST['opciones']; // Array con los 4 textos de las opciones
    $correcta_index = $_POST['correcta']; // Índice de la opción correcta (0, 1, 2 o 3)

    try {
        // Iniciamos la transacción
        $pdo->beginTransaction();

        // 1. Guardar la pregunta
        $stmt_preg = $pdo->prepare("INSERT INTO preguntas (prueba_id, texto_pregunta, retroalimentacion) VALUES (?, ?, ?)");
        $stmt_preg->execute([$prueba_id, $texto_pregunta, $retroalimentacion]);
        $pregunta_id = $pdo->lastInsertId();

        // 2. Guardar las 4 opciones
        $stmt_opc = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, ?, ?)");
        
        foreach ($opciones as $index => $texto_opcion) {
            // Evaluamos si este índice es el que el profesor marcó como correcto
            $es_correcta = ($index == $correcta_index) ? 1 : 0; // 1 (True) o 0 (False) en PostgreSQL
            
            // Insertamos la opción
            $stmt_opc->execute([$pregunta_id, $texto_opcion, $es_correcta]);
        }

        // Confirmamos la transacción
        $pdo->commit();
        $mensaje_exito = "Pregunta agregada correctamente.";

    } catch (PDOException $e) {
        $pdo->rollBack(); // Si hay error, revertimos todo
        $error = "Error al guardar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Preguntas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Agregar Preguntas a la Prueba</h2>
            <a href="profesor_dashboard.php" class="btn btn-outline-primary">Volver al Dashboard</a>
        </div>

        <?php if(isset($mensaje_exito)) echo "<div class='alert alert-success'>$mensaje_exito</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Texto de la Pregunta</label>
                        <textarea name="texto_pregunta" class="form-control" rows="2" placeholder="Ej: What _____ she doing right now?" required></textarea>
                    </div>

                    <h5 class="mb-3 border-bottom pb-2">Opciones de Respuesta</h5>
                    <p class="text-muted small">Escribe las 4 opciones y selecciona con el círculo cuál es la correcta.</p>
                    
                    <!-- Opciones iteradas (0 al 3) -->
                    <?php for($i = 0; $i < 4; $i++): ?>
                    <div class="input-group mb-2">
                        <div class="input-group-text bg-white">
                            <input class="form-check-input mt-0" type="radio" name="correcta" value="<?= $i ?>" required title="Marcar como respuesta correcta">
                        </div>
                        <input type="text" name="opciones[]" class="form-control" placeholder="Opción <?= $i + 1 ?>" required>
                    </div>
                    <?php endfor; ?>

                    <div class="mb-4 mt-4">
                        <label class="form-label fw-bold text-primary">Feedback / Retroalimentación (Fundamental para tu tesis)</label>
                        <textarea name="retroalimentacion" class="form-control" rows="2" placeholder="Ej: Recuerda que con el pronombre 'she' en Present Continuous utilizamos 'is'..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Guardar Pregunta y Agregar Otra</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>