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

try {
    $pdo->beginTransaction();

    // Inserta mensaje en la BD
    $stmt = $pdo->prepare("INSERT INTO messg_tech_user (chat_id, sender, message, timestamp) VALUES (?, 'usuario', ?, NOW())");
    $stmt->execute([$chat_id, $mensaje]);

    //Crea notificación para el técnico
    $msg_noti = "Un usuario te ha enviado un mensaje";
    $link = "chat-users-techs.php?chat_id=" . $chat_id;

    $stmtNoti = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link, is_read, created_at) VALUES (?, 'nuevo_mensaje', ?, ?, 0, NOW())");
    $stmtNoti->execute([$chat['tech_id'], $msg_noti, $link]);

    $pdo->commit();
    echo json_encode(['success' => true, 'tech_id' => $chat['tech_id']]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
