<?php
session_start();
require_once("../dbconnection.php");
require("checklogin.php");
check_login("usuario");

$usuario_id = $_SESSION['user_id'] ?? 0;
$chat_id = intval($_POST['chat_id'] ?? 0);
$mensaje = trim($_POST['message'] ?? '');

if (empty($mensaje)) exit;

$stmt = $pdo->prepare("SELECT * FROM chat_user_tech WHERE id = ? AND user_id = ? AND status_chat = 'abierto'");
$stmt->execute([$chat_id, $usuario_id]);
$chat = $stmt->fetch();

if (!$chat) exit("No autorizado o chat cerrado");

$stmt = $pdo->prepare("INSERT INTO messg_user_tech (chat_id, sender, message) VALUES (?, 'usuario', ?)");
$stmt->execute([$chat_id, $mensaje]);

echo "ok";
?>
