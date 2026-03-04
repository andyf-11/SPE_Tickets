<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");

// 1. Capturamos los datos
$chat_id = intval($_POST['chat_id'] ?? 0);
$sender = $_POST['sender'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$chat_id || empty($sender) || empty($message)) {
    die(json_encode(['success' => false, 'error' => 'Datos inválidos']));
}

try {

    $pdo->beginTransaction();

    // CORRECCIÓN: Usar la tabla messg_tech_admin y el campo apply_id
    $stmt = $pdo->prepare("INSERT INTO messg_tech_admin (apply_id, emisor, message, date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$chat_id, $sender, $message]);

    //Genera notificación para el admin
    $msg_noti = "Nuevo mensaje del técnico en el ticket #$chat_id";
    $type = 'nuevo_mensaje_chat';
    $link = "chat-tech-admin.php?apply_id=$chat_id";

    
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>