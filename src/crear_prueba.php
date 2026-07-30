<?php
session_start();
require 'config.php';

// Validar que solo el profesor pueda acceder
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $tiempo = $_POST['tiempo_minutos'];

    try {
        // Insertamos la prueba (por defecto el estado en la BD es 'borrador')
        $stmt = $pdo->prepare("INSERT INTO pruebas (titulo, descripcion, tiempo_minutos) VALUES (?, ?, ?)");
        $stmt->execute([$titulo, $descripcion, $tiempo]);
        
        // Obtenemos el ID de la prueba recién creada
        $prueba_id = $pdo->lastInsertId();
        
        // Redirigimos al panel para agregarle preguntas
        header("Location: gestionar_preguntas.php?prueba_id=" . $prueba_id);
        exit;
    } catch (PDOException $e) {
        $error = "Error al crear la prueba: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nueva Prueba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Crear Nueva Evaluación de Inglés</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tema / Título</label>
                                <!-- Puedes basarte en tu lista de temas aquí -->
                                <input type="text" name="titulo" class="form-control" placeholder="Ej: Unit 1 - Present Simple" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instrucciones / Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Read carefully and choose the correct answer..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tiempo Límite (en minutos)</label>
                                <input type="number" name="tiempo_minutos" class="form-control" value="30" min="1" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="profesor_dashboard.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Guardar y Agregar Preguntas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>