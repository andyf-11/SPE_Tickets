<?php
session_start();
require_once("../dbconnection.php");
require("checklogin.php");
check_login("usuario"); // o técnico si compartido

$chat_id = intval($_GET['chat_id'] ?? 0);

if (!$chat_id) {
    exit("Chat no válido.");
}

// Verificar que el usuario o técnico tiene acceso al chat
$usuario_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM chat_user_tech WHERE id = ?");
$stmt->execute([$chat_id]);
$chat = $stmt->fetch();

if (!$chat) {
    exit("Chat no encontrado.");
}

if (($role === 'usuario' && $chat['user_id'] != $usuario_id) || ($role === 'tecnico' && $chat['tech_id'] != $usuario_id)) {
    exit("Acceso no autorizado.");
}

// Obtener mensajes del chat
$stmt = $pdo->prepare("SELECT * FROM messg_tech_user WHERE chat_id = ? ORDER BY created_at ASC");
$stmt->execute([$chat_id]);
$mensajes = $stmt->fetchAll();

if (!$mensajes) {
    echo '<p class="text-muted text-center">No hay mensajes aún.</p>';
    exit;
}

// Mostrar mensajes con estilo adecuado
foreach ($mensajes as $msg) {
    $clase = ($msg['sender'] === 'usuario') ? 'mensaje-usuario text-start' : 'mensaje-tecnico text-end';
    echo '<div class="' . $clase . ' mb-2">';
    echo '<div class="fw-bold">' . ucfirst(htmlspecialchars($msg['sender'])) . '</div>';
    echo '<div>' . nl2br(htmlspecialchars($msg['message'])) . '</div>';
    echo '<div class="small text-muted">' . htmlspecialchars($msg['created_at']) . '</div>';
    echo '</div>';
}
