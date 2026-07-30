<?php
session_start();
require 'config.php';

// Validar que sea un estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['prueba_id'])) {
    die("Error: No se especificó la prueba.");
}

$prueba_id = $_GET['prueba_id'];
$estudiante_id = $_SESSION['user_id'];

try {
    // 1. Obtener los datos de la prueba (Título y Tiempo)
    $stmt = $pdo->prepare("SELECT * FROM pruebas WHERE id = ? AND estado = 'activa'");
    $stmt->execute([$prueba_id]);
    $prueba = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prueba) {
        die("La prueba no existe o no está habilitada en este momento.");
    }

    // 2. Obtener todas las preguntas de esta prueba
    $stmt_preguntas = $pdo->prepare("SELECT * FROM preguntas WHERE prueba_id = ?");
    $stmt_preguntas->execute([$prueba_id]);
    $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Meta viewport crucial para que se vea bien en celulares -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($prueba['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Un pequeño estilo para que el cronómetro flote en la pantalla */
        .timer-flotante {
            position: sticky;
            top: 10px;
            z-index: 1000;
            background: #fff;
            border: 2px solid #dc3545; /* Rojo Bootstrap */
            border-radius: 8px;
            padding: 10px;
            font-size: 1.25rem;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light pb-5">
    <div class="container mt-4">
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                
                <!-- Encabezado de la prueba -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body bg-primary text-white rounded">
                        <h3 class="mb-1"><?= htmlspecialchars($prueba['titulo']) ?></h3>
                        <p class="mb-0"><?= htmlspecialchars($prueba['descripcion']) ?></p>
                    </div>
                </div>

                <!-- El Cronómetro Visual -->
                <div class="timer-flotante text-danger mb-4" id="timer-display">
                    ⏱️ Tiempo restante: Cargando...
                </div>

                <!-- Formulario del Examen -->
                <form id="examen-form" action="procesar_examen.php" method="POST">
                    <input type="hidden" name="prueba_id" value="<?= $prueba['id'] ?>">

                    <?php 
                    $numero = 1;
                    foreach ($preguntas as $pregunta): 
                    ?>
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">
                                    <?= $numero ?>. <?= htmlspecialchars($pregunta['texto_pregunta']) ?>
                                </h5>
                                
                                <div class="mt-3">
                                    <?php
                                    // 3. Obtener opciones para ESTA pregunta
                                    $stmt_opciones = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = ? ORDER BY id ASC");
                                    $stmt_opciones->execute([$pregunta['id']]);
                                    $opciones = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($opciones as $opcion):
                                    ?>
                                        <div class="form-check mb-2">
                                            <!-- Guardamos el ID de la opción seleccionada en un array indexado por el ID de la pregunta -->
                                            <input class="form-check-input" type="radio" 
                                                   name="respuestas[<?= $pregunta['id'] ?>]" 
                                                   value="<?= $opcion['id'] ?>" required>
                                            <label class="form-check-label">
                                                <?= htmlspecialchars($opcion['texto_opcion']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                    $numero++;
                    endforeach; 
                    ?>

                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-5 shadow">
                        Finalizar y Enviar Examen
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- Lógica del Temporizador en JavaScript -->
    <script>
        // Obtenemos el tiempo en minutos desde PHP y lo pasamos a segundos
        let tiempoRestante = <?= $prueba['tiempo_minutos'] ?> * 60;
        const timerDisplay = document.getElementById('timer-display');
        const formExamen = document.getElementById('examen-form');

        function actualizarReloj() {
            let minutos = Math.floor(tiempoRestante / 60);
            let segundos = tiempoRestante % 60;

            // Formatear para que siempre tenga dos dígitos (ej. 09:05)
            minutos = minutos < 10 ? '0' + minutos : minutos;
            segundos = segundos < 10 ? '0' + segundos : segundos;

            timerDisplay.innerHTML = `⏱️ Tiempo restante: ${minutos}:${segundos}`;

            // Cambiar a color rojo parpadeante si quedan menos de 3 minutos (180 seg)
            if (tiempoRestante <= 180) {
                timerDisplay.style.backgroundColor = '#ffe6e6';
                timerDisplay.style.color = '#dc3545';
            }

            if (tiempoRestante <= 0) {
                clearInterval(intervalo);
                timerDisplay.innerHTML = "¡Tiempo Finalizado!";
                // Bloquear pantalla para que no modifiquen nada
                document.body.style.opacity = '0.5';
                document.body.style.pointerEvents = 'none';
                
                // Enviar el formulario automáticamente
                formExamen.submit();
            } else {
                tiempoRestante--;
            }
        }

        // Ejecutar inmediatamente y luego cada 1 segundo (1000 ms)
        actualizarReloj();
        const intervalo = setInterval(actualizarReloj, 1000);
    </script>
</body>
</html>
