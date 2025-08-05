<?php
session_start();
require_once '../../dbconnection.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token no proporcionado.");
}

// Buscar el token en la base de datos
$stmt = $pdo->prepare("SELECT email, expires_at FROM passwords_reset WHERE token = ?");
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