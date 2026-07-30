<?php
// login_process.php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Buscar el usuario por email
        $stmt = $pdo->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el usuario existe y la contraseña coincide
        if ($user && password_verify($password, $user['password'])) {
            
            // Crear variables de sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            // Redirección controlada por niveles
            if ($user['rol'] === 'profesor') {
                header("Location: profesor_dashboard.php");
            } else {
                header("Location: estudiante_dashboard.php");
            }
            exit;
            
        } else {
            // Credenciales incorrectas
            $_SESSION['error'] = "Correo o contraseña incorrectos.";
            header("Location: index.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en el servidor. Intente más tarde.";
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>