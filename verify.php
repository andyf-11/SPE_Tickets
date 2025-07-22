<?php
require 'dbconnection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT id FROM user WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE user SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update->execute([$user['id']]);
        echo "¡Cuenta verificada! Ahora puedes <a href=login1.php>Iniciar Sesión</a>";
    }else {
        echo "Token inválido o cuenta verificada.";
    }
} else {
    echo "Token no proporcionado";
}
?>