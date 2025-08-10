<?php
session_start();
require_once '../../dbconnection.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token no proporcionado.");
}

// Buscar el token en la base de datos
$stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

//Validar token
if (!$resetRequest) {
    die("Token inválido");
}

if (strtotime($resetRequest['expires_at']) < time()) {
    die("El token ha expirado. Solicita una nueva recuperación. ");
}

$email = $resetRequest['email'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/reset-password.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card password-reset-card">
                    <div class="card-header password-reset-header text-center py-3">
                        <h2 class="mb-0">Restablecer Contraseña</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="process_reset.php" method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva contraseña:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmar contraseña:</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-reset">Cambiar contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>