<?php
session_start();
require_once '../../dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'];

    if (empty($token) || empty($email) || empty($password)) {
        die("Todos los campos son obligatorios.");
    }

    if ($password !==$confirmPassword) {
        die("Las contraseñas no coinciden.");
    }

    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND email = ?");
    $stmt->execute([$token, $email]);
    $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resetRequest) {
        die("Token inválido o correo incorrecto. ");
    }

    if (strtotime($resetRequest['expires_at']) < time()) {
        die("El token ha expirado. Solicita una nueva recuperación.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);

    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);

    echo "Contraseña actualizada correctamente. Ya puedes iniciar sesión.";

} else {
    http_response_code(405);
    echo "Métdo no permitido.";
}