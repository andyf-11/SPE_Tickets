<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");

$chat_id = intval($_POST['chat_id'] ?? 0);
$sender = $_POST['sender'] ?? 'tecnico';
$message = trim($_POST['message'] ?? '');

if (!$chat_id || empty($message)) {
    die(json_encode(['success' => false, 'error' => 'Datos incompletos']));
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSER INTO messg_tech_user (chat_id, sender, message, timestamp VALUES (?, ?, ?, NOW()");
    $stmt->execute([$chat_id, $sender, $message]);

    //Obtiene ID para enviar notificación
    $stmtUser = $pdo->prepare("SELECT user_id FROM chat_user_tech WHERE id = ?");
    $stmtUser->execute([$chat_id]);
    $chatInfo = $stmtUser->fetch();
    $destinatario_id = $chatInfo['user_id'];

    //Insertar notificación en la tabla
    $msg_noti = "El técnico ha enviado un nuevo mensaje";
    $link = "chat-list-users.php?chat_id=$chat_id";

    $stmtNoti = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link, is_Read, created_at) VALUES (?, 'nuevo_mensaje_chat', ?, ?, 0, NOW())");
    $stmtNoti->execute([$destinatario_id, $msg_noti, $link]);

    $pdo->commit();
    echo json_encode(['success' => true, 'destinatario_id' => $destinatario_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}