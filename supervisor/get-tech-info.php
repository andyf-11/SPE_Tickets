<?php
require_once '../dbconnection.php';

$id =intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT name, email, phone, edificio_id FROM user WHERE id = ?");
    
}