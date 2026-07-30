<?php
session_start();
// Proteger la ruta: Solo estudiantes pueden entrar
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: index.php");
    exit;
}
?>
<h1>Bienvenido Estudiante <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
<p>Aquí verás las pruebas habilitadas para rendir.</p>
<a href="logout.php">Cerrar Sesión</a>