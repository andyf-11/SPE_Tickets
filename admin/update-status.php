<?php
require("dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apply_id = (int) ($_POST['apply_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $map = [
        'aprobar' => 'aprobado',
        'rechazar' => 'rechazado',
        'resuelto' => 'resuelto'
    ];

    if ($apply_id && isset($map[$action])) {
        $stmt = $pdo->prepare("UPDATE application_approv SET status = ? WHERE id =?");
        $stmt->execute([$map[$action], $apply_id]);
    }
}
?>