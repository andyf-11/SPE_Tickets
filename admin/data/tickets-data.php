<?php
require_once '../dbconnection.php';
session_start();
require("../checklogin.php");
check_login("admin");

header('Content-Type: application/json');

try {
    //Tickets abiertos (TODOS)
    $stmt = $pdo->query("SELECT COUNT(*) FROM ticket WHERE status = 'Abierto'");
    $ticketsAbiertos = $stmt->fetchColumn();

    //Tickets Resueltos en el mes
    $stmt = $pdo->query("SELECT COUNT(*) FROM ticket WHERE status = 'Cerrado'
AND fecha_cierre >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stmt->execute();
    $ticketsCerrados = $stmt->fetchColumn();

    //Usuarios activos con tickets en el mes
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM ticket
WHERE posting_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stmt->execute();
    $usuariosAct = $stmt->fetchColumn();

    echo json_encode([
        "ticketsAbiertos" => $icketsAbiertos ?: 0,
        "ticketsCerrados" => $ticketsCerrados ?: 0,
        "usuariosAct" => $usuariosAct ?: 0,
    ]);
} catch (Exception $e){
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);

}