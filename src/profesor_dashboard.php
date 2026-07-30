<?php
session_start();
// Proteger la ruta: Solo profesores pueden entrar
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}
?>
<h1>Bienvenido Profesor <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
<p>Aquí podrás crear pruebas y ver calificaciones.</p>
<a href="logout.php">Cerrar Sesión</a>