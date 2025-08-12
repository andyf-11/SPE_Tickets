<?php
require_once '../dbconnection.php';

$id =intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name, email, mobile, edificio_id FROM user WHERE id = ?");
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