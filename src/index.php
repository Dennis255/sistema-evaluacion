<?php 
session_start();
// Si ya hay una sesión activa, redirigir según el rol
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['rol'] == 'profesor') {
        header("Location: profesor_dashboard.php");
    } else {
        header("Location: estudiante_dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Pruebas de Inglés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0 mt-5">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4 text-primary">English Evaluation System</h3>
                        
                        <!-- Mostrar mensaje de error si existe -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="login_process.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" required placeholder="ejemplo@correo.com">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Ingresar al Sistema</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>