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

   try {
    $pdo->beginTransaction();

    //Verifica solicitud y obtiene ID del técnico
    $stmt = $pdo->prepare("SELECT *FROM application_approv WHERE id = :apply_id AND status = 'pendiente'");
    $stmt->execute(['apply_id => $apply_id']);
    $solicitud =$stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        throw new Exception ("Solicitud no encontradao ya cerrada.");
    }
    $tecnico_id = $solicitud['tech_id'];

    //Insertar el emsaje del admin
    $stmt2 = $pdo->prepare("INSERT INTO messg_tech_admin (apply_id, emisor, message) VALUES (:apply_id, 'admin', :message)");
    $stmt2->execute(['apply_id' => $apply_id, 'message' => $message]);

    //Determinar nuevo estado
    $nuevo_estado = match($action) {
        'aprobar' => 'aprobado',
        'rechazar' => 'rechazado',
        'resuelto' => 'resuelto',
        default => null
    };

    if (!$nuevo_estado) throw new Exception("Acción inválida.");

    //Actualiza el estado de la solicitud
    $stmt3 =$pdo->prepare("UPDATE application_approv SET status = :nuevo_estado WHERE id = :apply_id");
    $stmt3->execute(['nuevo_estado' => $nuevo_estado, 'apply_id' => $apply_id]);

    //Registra notificación para el técnico
    $msg_noti = "El administrador ha respondido a tu solicitud (Estado: " . ucfirst($nuevo_estado) .")";
    $type = 'respuesta_aprobación';
    $link = "chat-tech-admin.php?ticket_id=" . $solicitud['ticket_id'];

    $stmtNoti = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link, is_read, created_at VALUES (?, ?, ?, ?, 0, NOW()");
    $stmtNoti->execute([$tecnico_id, $type, $msg_noti, $link]);

    $pdo->commit();
    header("Location: chat-list-admin.php?success=1");
    exit;

   } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Error: " . $e->getMessage());
   }
}