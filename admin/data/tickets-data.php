<?php
require_once '../dbconnection.php';
session_start();
require("../checklogin.php");
check_login("admin");

header('Content-Type: application/json');

try {
    // Tickets Abiertos
    $stmt = $pdo->query("SELECT COUNT(*) FROM ticket WHERE status = 'Abierto'");
    $ticketsAbiertos = $stmt->fetchColumn();

    // Tickets Cerrados en el último mes
    $stmt = $pdo->query("SELECT COUNT(*) FROM ticket WHERE status = 'Cerrado' AND fecha_cierre >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $ticketsCerrados = $stmt->fetchColumn();

    // Usuarios activos con tickets en el último mes
    $stmt = $pdo->query("SELECT COUNT(DISTINCT email_id) FROM ticket WHERE posting_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $usuariosAct = $stmt->fetchColumn();

    echo json_encode([
        "openTickets" => (int)$ticketsAbiertos ?: 0,
        "closedTickets" => (int)$ticketsCerrados ?: 0,
        "activeUsers" => (int)$usuariosAct ?: 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
