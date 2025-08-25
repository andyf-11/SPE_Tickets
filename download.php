<?php
session_start();
require_once(__DIR__ . "/dbconnection.php"); // Ajusta la ruta si es necesario
require_once(__DIR__ . "/usuario/checklogin.php");
check_login();

// Obtener información del ticket usando PDO
if (isset($_GET['ticket_id'])) {
    $ticket_id = intval($_GET['ticket_id']);

    $stmt = $pdo->prepare("SELECT archivo, email_id, technician_id FROM ticket WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        die("Ticket no encontrado.");
    }
    
    $filename = basename($ticket['archivo']);

} else {
    die("Parámetro ticket_id no recibido.");
}

$filepath = __DIR__ . "/uploads/" . $filename;

if (!file_exists($filepath)) {
    die("El archivo no existe en el servidor.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>
