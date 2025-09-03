<?php
require_once '../dbconnection.php';

$id =intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT u.name, u.email, u.mobile, e.name AS edificio
        FROM user u
        LEFT JOIN edificios e ON u.edificio_id = e.id
        WHERE u.id = ?");
    $stmt->execute([$id]);
    $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tecnico) {
        header('Content-Type: application/json');
        echo json_encode($tecnico);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Tecnico no encontrado']);