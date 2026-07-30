<?php
// estado_prueba.php
session_start();
require 'config.php'; // Asegúrate de que este archivo carga tu función getDBConnection()

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$pdo = getDBConnection();

if (isset($_GET['id'])) {
    $prueba_id = $_GET['id'];
    
    try {
        // Consultar el estado actual de la prueba
        $stmt = $pdo->prepare("SELECT estado FROM pruebas WHERE id = ?");
        $stmt->execute([$prueba_id]);
        $prueba = $stmt->fetch();

        if ($prueba) {
            // Si está activa, la pasamos a borrador (oculta). Si es borrador, la activamos.
            $nuevo_estado = ($prueba['estado'] === 'activa') ? 'borrador' : 'activa';

            $update = $pdo->prepare("UPDATE pruebas SET estado = ? WHERE id = ?");
            $update->execute([$nuevo_estado, $prueba_id]);
        }
    } catch (PDOException $e) {
        die("Error al actualizar el estado: " . $e->getMessage());
    }
}

// Redirigir de vuelta al panel
header("Location: profesor_dashboard.php");
exit;
?>