<?php
session_start();
require_once("checklogin.php");
check_login("admin");
require("dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['user_id'] ?? null;
    $apply_id = $_POST['apply_id'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $action = $_POST['action'] ?? null; // nuevo campo para saber qué acción se eligió

    if (!$apply_id || !is_numeric($apply_id) || $message === '' || !$action) {
        die("Datos inválidos.");
    }
    $apply_id = (int)$apply_id;

    // Verificar que la solicitud exista y siga pendiente
    $stmt = $pdo->prepare("SELECT * FROM application_approv WHERE id = :apply_id AND status = 'pendiente'");
    $stmt->execute(['apply_id' => $apply_id]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$solicitud) {
        die("Solicitud no encontrada o ya cerrada.");
    }

    // Insertar el mensaje
    $stmt2 = $pdo->prepare("INSERT INTO messg_tech_admin (apply_id, emisor, message) VALUES (:apply_id, 'admin', :message)");
    $stmt2->execute(['apply_id' => $apply_id, 'message' => $message]);

    // Determinar el nuevo estado según la acción
    $nuevo_estado = null;
    if ($action === 'aprobar') {
        $nuevo_estado = 'aprobado';
    } elseif ($action === 'rechazar') {
        $nuevo_estado = 'rechazado';
    } elseif ($action === 'resuelto') {
        $nuevo_estado = 'resuelto';
    } else {
        die("Acción inválida.");
    }

    // Actualizar estado de la solicitud
    $stmt3 = $pdo->prepare("UPDATE application_approv SET status = :nuevo_estado WHERE id = :apply_id");
    $stmt3->execute(['nuevo_estado' => $nuevo_estado, 'apply_id' => $apply_id]);

    header("Location: chat-list-admin.php?success=1");
    exit;
}
?>
