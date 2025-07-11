<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['chat_id'])) {
    http_response_code(400);
    echo "Petición inválida.";
    exit;
}

$chat_id = intval($_POST['chat_id']);
if ($chat_id <= 0) {
    http_response_code(400);
    echo "ID de chat inválido.";
    exit;
}

// Buscar chat y validar acceso
$stmt = $pdo->prepare("SELECT * FROM chat_user_tech WHERE id = ?");
$stmt->execute([$chat_id]);
$chat = $stmt->fetch();

if (!$chat) {
    http_response_code(403);
    echo "Chat no encontrado.";
    exit;
}

// Validar que usuario o técnico tiene permiso para cerrar el chat
if (($role === 'tecnico' && $chat['tech_id'] != $user_id) ||
    ($role === 'usuario' && $chat['user_id'] != $user_id)) {
    http_response_code(403);
    echo "No tienes permiso para cerrar este chat.";
    exit;
}

if ($chat['status_chat'] === 'cerrado') {
    echo "El chat ya está cerrado.";
    exit;
}

// Actualizar estado a cerrado
$stmt = $pdo->prepare("UPDATE chat_user_tech SET status_chat = 'cerrado', close_date = NOW() WHERE id = ?");
$stmt->execute([$chat_id]);

echo "Chat cerrado correctamente.";
