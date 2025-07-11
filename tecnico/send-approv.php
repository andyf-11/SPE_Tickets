<?php
session_start();
require_once("checklogin.php");
check_login("tecnico");
require("dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tecnico_id = $_SESSION['user_id'];
    $apply_id = $_POST['apply_id'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $emisor = $_POST['emisor'] ?? 'tecnico';

    if (!$apply_id || !is_numeric($apply_id) || $message === '') {
        die("Datos inválidos.");
    }
    $apply_id = (int)$apply_id;

    // Verificar que la solicitud pertenece al técnico y está pendiente o abierta
    $stmt = $pdo->prepare("SELECT * FROM application_approv WHERE id = :apply_id AND tech_id = :tech_id");
    $stmt->execute(['apply_id' => $apply_id, 'tech_id' => $tecnico_id]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$solicitud) {
        die("Solicitud no encontrada o acceso no autorizado.");
    }

    // Insertar mensaje del técnico o admin (según $emisor)
    $stmt2 = $pdo->prepare("INSERT INTO messg_tech_admin (apply_id, emisor, message, date) VALUES (:apply_id, :emisor, :message, NOW())");
    $stmt2->execute(['apply_id' => $apply_id, 'emisor' => $emisor, 'message' => $message]);

    // Redirigir a la lista de chats
    header("Location: chat-list.php");
    exit;
}
?>
