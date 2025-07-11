<?php
session_start();
include("dbconnection.php");

// Verificar que el usuario sea admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $stmt = $con->prepare("UPDATE user SET login_attempts = 0, locked_until = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

header("Location: ../manage-users.php"); // Redirige al panel donde está la tabla de usuarios
exit();
