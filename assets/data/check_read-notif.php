<?php
require_once 'dbconnection.php';
session_start();

$id = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);

echo json_encode(['success' => true]);
