<?php
require_once '../dbconnection.php';
session_start();
require("../checklogin.php");
check_login("admin");

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'priority':
            $stmt = $pdo->query("SELECT priority, COUNT(*) as total FROM ticket GROUP BY priority");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'problems':
            $stmt = $pdo->query("SELECT subject, COUNT(*) as total FROM ticket GROUP BY subject ORDER BY total DESC LIMIT 5");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'monthly_count':
            // Obtener tickets por día del mes actual
            $stmt = $pdo->query("SELECT DATE(posting_date) as fecha, COUNT(*) as total 
                                FROM ticket 
                                WHERE MONTH(posting_date) = MONTH(CURRENT_DATE()) 
                                AND YEAR(posting_date) = YEAR(CURRENT_DATE())
                                GROUP BY DATE(posting_date) 
                                ORDER BY fecha");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'areas':
            $stmt = $pdo->query("
                SELECT a.name AS area, COUNT(t.id) AS total
                FROM ticket t
                LEFT JOIN areas a ON t.area_id = a.id
                GROUP BY a.id, a.name
                ORDER BY total DESC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        default:
            $data = [];
            break;
    }

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}