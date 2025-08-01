<?php
require_once("../../dbconnection.php");

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} elseif (isset($_GET['all'])) {
    session_start();
    $userId = $_SESSION['user_id'] ?? 0;
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
