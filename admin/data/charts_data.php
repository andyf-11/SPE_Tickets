<?php
require_once '../dbconnection.php';
session_start();
require("../checklogin.php");
check_login("admin");

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

try {
    switch ($type) {
        case 'priority':
            $stmt = $pdo->prepare("SELECT priority, COUNT(*) as total 
                                  FROM ticket 
                                  WHERE MONTH(posting_date) = :month 
                                  AND YEAR(posting_date) = :year
                                  GROUP BY priority");
            $stmt->execute([':month' => $month, ':year' => $year]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'problems':
            $stmt = $pdo->prepare("SELECT subject, COUNT(*) as total 
                                  FROM ticket 
                                  WHERE MONTH(posting_date) = :month 
                                  AND YEAR(posting_date) = :year
                                  GROUP BY subject 
                                  ORDER BY total DESC 
                                  LIMIT 5");
            $stmt->execute([':month' => $month, ':year' => $year]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'monthly_count':
            $stmt = $pdo->prepare("SELECT DATE(posting_date) as fecha, COUNT(*) as total 
                                  FROM ticket 
                                  WHERE MONTH(posting_date) = :month 
                                  AND YEAR(posting_date) = :year
                                  GROUP BY DATE(posting_date) 
                                  ORDER BY fecha");
            $stmt->execute([':month' => $month, ':year' => $year]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'areas':
            $stmt = $pdo->prepare("
                SELECT a.name AS area, COUNT(t.id) AS total
                FROM ticket t
                INNER JOIN user u ON t.email_id = u.email
                INNER JOIN areas a ON u.area_id = a.id
                WHERE MONTH(t.posting_date) = :month
                AND YEAR(t.posting_date) = :year
                GROUP BY a.id, a.name
                ORDER BY total DESC
            ");
            $stmt->execute([':month' => $month, ':year' => $year]);
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