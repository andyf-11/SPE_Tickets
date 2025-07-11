<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("tecnico");

$ticket_id = intval($_GET['ticket_id'] ?? 0);
$tecnico_id = $_SESSION['user_id'] ?? 0;

try {
    // Verificar que el ticket existe y está asignado a este técnico
    $stmt = $pdo->prepare("SELECT id, email_id FROM ticket WHERE id = ? AND assigned_to = ?");
    $stmt->execute([$ticket_id, $tecnico_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        exit("No tienes permiso para abrir este chat.");
    }

    // Buscar el ID del usuario en la tabla user, usando el email_id
    $stmtUser = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmtUser->execute([$ticket['email_id']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        exit("El usuario relacionado a este ticket no existe.");
    }

    $user_id = $user['id'];

    // ¿Ya existe un chat para este ticket?
    $stmtChat = $pdo->prepare("SELECT id FROM chat_user_tech WHERE ticket_id = ?");
    $stmtChat->execute([$ticket_id]);
    $chat = $stmtChat->fetch(PDO::FETCH_ASSOC);

    if ($chat) {
        // Si ya existe → actualizar a 'abierto'
        $stmtUpdate = $pdo->prepare("UPDATE chat_user_tech SET status_chat = 'abierto' WHERE id = ?");
        $stmtUpdate->execute([$chat['id']]);
        $chat_id = $chat['id'];
    } else {
        // Si no existe → crear nuevo chat
        $stmtInsert = $pdo->prepare("INSERT INTO chat_user_tech (ticket_id, tech_id, user_id, status_chat) VALUES (?, ?, ?, 'abierto')");
        $stmtInsert->execute([$ticket_id, $tecnico_id, $user_id]);
        $chat_id = $pdo->lastInsertId();
    }

    header("Location: chat-users-techs.php?chat_id=" . $chat_id);
    exit;

} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
