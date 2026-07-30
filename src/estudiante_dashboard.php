<?php
session_start();
require 'config.php';

// Validar que el usuario esté logueado y sea estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: index.php");
    exit;
}

$pdo = getDBConnection();
$estudiante_id = $_SESSION['user_id'];
$nombre_estudiante = $_SESSION['nombre'] ?? 'Estudiante';

try {
    // 1. Obtener solo las pruebas que el profesor marcó como "activa"
    $stmt = $pdo->query("SELECT * FROM pruebas WHERE estado = 'activa' ORDER BY id ASC");
    $pruebas_activas = $stmt->fetchAll();

    // 2. Obtener los resultados previos de este estudiante usando 'estudiante_id' y 'calificacion'
    $stmtResultados = $pdo->prepare("SELECT prueba_id, calificacion FROM resultados WHERE estudiante_id = ?");
    $stmtResultados->execute([$estudiante_id]);
    $resultados_raw = $stmtResultados->fetchAll();
    
    // Convertimos los resultados a un formato fácil de buscar: [prueba_id => calificacion]
    $pruebas_rendidas = [];
    foreach ($resultados_raw as $res) {
        $pruebas_rendidas[$res['prueba_id']] = $res['calificacion'];
    }

} catch (PDOException $e) {
    die("Error al cargar el panel: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Estudiante - English Evaluation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">🎓 Bienvenido, <?= htmlspecialchars($nombre_estudiante) ?></a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Evaluaciones Disponibles</h2>

    <div class="row">
        <?php if (count($pruebas_activas) === 0): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No hay evaluaciones activas en este momento. Vuelve más tarde.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pruebas_activas as $prueba): ?>
                <?php 
                    $ya_rendida = array_key_exists($prueba['id'], $pruebas_rendidas);
                    $puntaje = $ya_rendida ? $pruebas_rendidas[$prueba['id']] : null;
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 <?= $ya_rendida ? 'border-success' : 'border-primary' ?>">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($prueba['titulo']) ?></h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars($prueba['descripcion']) ?></p>
                            <p class="mb-2">⏱️ Tiempo: <?= $prueba['tiempo_minutos'] ?> min</p>
                            
                            <?php if ($ya_rendida): ?>
                                <div class="alert alert-success p-2 text-center mb-0">
                                    <strong>Calificación: <?= $puntaje ?></strong><br>
                                    <small>Ya completaste esta prueba</small>
                                </div>
                            <?php else: ?>
                                <a href="rendir_prueba.php?id=<?= $prueba['id'] ?>" class="btn btn-primary w-100">📝 Iniciar Prueba</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>