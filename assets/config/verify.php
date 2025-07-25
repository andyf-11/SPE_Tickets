<?php
require 'dbconnection.php';

if (isset($_GET['token'])) {
    $token = trim($_GET['token']); // Limpieza básica

    $stmt = $pdo->prepare("SELECT id FROM user WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE user SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        echo "<h2>✅ ¡Cuenta verificada exitosamente!</h2>";
        echo "<p>Ahora puedes <a href='login1.php'>iniciar sesión</a>.</p>";
    } else {
        echo "<h2>⚠️ Token inválido o la cuenta ya fue verificada.</h2>";
    }
} else {
    echo "<h2>❌ Token no proporcionado.</h2>";
}
?>
