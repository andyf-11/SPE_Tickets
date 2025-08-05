<?php
require_once '../../dbconnection.php';
require_once 'mailer_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if (empty($email)) {
        die("Correo electrónico es requerido.");
    }
}

$stmt = $pdo->prepare("SELECT id, name FROM user WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    //Genera token y expiración
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+ 1 hour"));

    //Elimina cualquier token anterior que exista
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expres_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires]);

    if (sendPasswordResetEmail($email, $user['name'], $token)) {
        echo "Hemos enviado un correo con instrucciones para restablecer tu contraseña.";
    } else {
        echo "Error al enviar el correo. Intenta de nueva más tarde.";
    }
} else {
    echo "Hemos enviado un correo con instrucciones para restablecer tu contraseña.";
}