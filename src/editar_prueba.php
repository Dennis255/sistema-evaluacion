<?php
// editar_prueba.php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$pdo = getDBConnection();

if (!isset($_GET['id'])) {
    header("Location: profesor_dashboard.php");
    exit;
}

$prueba_id = $_GET['id'];

try {
    // 1. Obtener datos de la prueba
    $stmt = $pdo->prepare("SELECT * FROM pruebas WHERE id = ?");
    $stmt->execute([$prueba_id]);
    $prueba = $stmt->fetch();

    if (!$prueba) {
        die("La evaluación solicitada no existe.");
    }

    // 2. Obtener todas las preguntas de esta prueba
    $stmtPreguntas = $pdo->prepare("SELECT * FROM preguntas WHERE prueba_id = ? ORDER BY id ASC");
    $stmtPreguntas->execute([$prueba_id]);
    $preguntas = $stmtPreguntas->fetchAll();

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar: <?= htmlspecialchars($prueba['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Administrar Evaluación</h2>
        <a href="profesor_dashboard.php" class="btn btn-outline-secondary">⬅ Volver al Panel</a>
    </div>

    <!-- Tarjeta de Resumen y Publicación -->
    <div class="card shadow-sm mb-5 border-0">
        <div class="card-body">
            <h4 class="text-primary fw-bold"><?= htmlspecialchars($prueba['titulo']) ?></h4>
            <p class="text-muted"><?= htmlspecialchars($prueba['descripcion']) ?></p>
            
            <div class="d-flex align-items-center gap-3 mt-4">
                <span class="badge bg-info text-dark fs-6 p-2">⏳ <?= $prueba['tiempo_minutos'] ?> minutos</span>
                
                <?php if ($prueba['estado'] === 'activa'): ?>
                    <span class="badge bg-success fs-6 p-2">✅ Publicada (Visible)</span>
                    <a href="estado_prueba.php?id=<?= $prueba['id'] ?>" class="btn btn-warning btn-sm">Ocultar Prueba</a>
                <?php else: ?>
                    <span class="badge bg-secondary fs-6 p-2">🔒 Borrador (Oculta)</span>
                    <a href="estado_prueba.php?id=<?= $prueba['id'] ?>" class="btn btn-success btn-sm">Publicar Prueba</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lista de Preguntas -->
    <h4 class="mb-4">Preguntas (<?= count($preguntas) ?>)</h4>
    
    <div class="row">
        <?php foreach ($preguntas as $index => $pregunta): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white fw-bold">
                        <?= ($index + 1) . ". " . htmlspecialchars($pregunta['texto_pregunta']) ?>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php
                        // Buscar las opciones para esta pregunta específica
                        $stmtOpciones = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = ? ORDER BY id ASC");
                        $stmtOpciones->execute([$pregunta['id']]);
                        $opciones = $stmtOpciones->fetchAll();

                        foreach ($opciones as $opcion):
                            // Si es correcta, le ponemos un fondo verde claro
                            $claseCorrecta = $opcion['es_correcta'] ? 'list-group-item-success fw-bold' : '';
                        ?>
                            <li class="list-group-item <?= $claseCorrecta ?>">
                                <?= htmlspecialchars($opcion['texto_opcion']) ?>
                                <?= $opcion['es_correcta'] ? ' <span class="float-end">✅</span>' : '' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>