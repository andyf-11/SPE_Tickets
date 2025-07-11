<?php
session_start();
require_once("../dbconnection.php");
require_once("checklogin.php");
check_login("admin");

header('Content-Type: application/json; charset=utf-8');

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

try {
    if ($busqueda !== '') {
        $sql = "SELECT id, name, email, mobile, role, posting_date 
                FROM user 
                WHERE name LIKE :busqueda_name OR email LIKE :busqueda_email 
                ORDER BY posting_date DESC";
        $stmt = $pdo->prepare($sql);
        $likeBusqueda = "%$busqueda%";
        $stmt->execute([
            ':busqueda_name' => $likeBusqueda,
            ':busqueda_email' => $likeBusqueda
        ]);
    } else {
        $sql = "SELECT id, name, email, mobile, role, posting_date 
                FROM user 
                ORDER BY posting_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $results = [];
    $cnt = 1;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'num' => $cnt,
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'mobile' => htmlspecialchars($row['mobile']),
            'role' => htmlspecialchars($row['role']),
            'posting_date' => htmlspecialchars($row['posting_date']),
        ];
        $cnt++;
    }

    echo json_encode(['success' => true, 'data' => $results]);

} catch (PDOException $e) {
    error_log("Error en tabla-usuarios-ajax.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos.']);
}
