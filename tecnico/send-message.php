<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");

$chat_id = intval($_POST['chat_id'] ?? 0);
$sender = $_POST['sender'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$chat_id || empty($sender) || empty($message)) {
    die(json_encode(['success' => false, 'error' => 'Datos inválidos']));
}

try {
    $stmt = $pdo->prepare("INSERT INTO messg_tech_user (chat_id, sender, message) VALUES (?, ?, ?)");
    $stmt->execute([$chat_id, $sender, $message]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>