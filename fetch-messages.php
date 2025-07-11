<?php
session_start();
require_once("dbconnection.php");

$user_id = $_SESSION['id'] ?? null;

if (!$user_id) {
    die("Usuario no autenticado.");
}

try {
    $sql = "SELECT * FROM mensajes 
            WHERE sender_id = :id OR receiver_id = :id 
            ORDER BY created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div><strong>{$row['sender_id']}:</strong> {$row['mensaje']}</div>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
