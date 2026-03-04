<?php
session_start();
require_once("dbconnection.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chat_id = intval($_POST['chat_id'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? 0;

    if ($chat_id > 0 && $user_id > 0) {
        // AGREGAMOS: close_date = NOW() para guardar la fecha y hora actual
        $stmt = $pdo->prepare("UPDATE chat_user_tech 
                               SET status_chat = 'cerrado', 
                                   close_date = NOW() 
                               WHERE id = ? AND tech_id = ?");

        if ($stmt->execute([$chat_id, $user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error en base de datos']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Sesión o ID inválido']);
    }
}