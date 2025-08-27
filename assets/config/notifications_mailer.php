<?php
require_once __DIR__ . "/../config/mailer_config.php"; // Ajusta la ruta según tu proyecto

function notifyUser($pdo, $user_id, $role, $subject, $message) {
    try {
        // Guarda notificación en DB
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, role, message, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $role, $message]);

        // Busca correo y nombre en tabla user
        $stmt = $pdo->prepare("SELECT nombre, email, role FROM user WHERE id = ? AND role = ?");
        $stmt->execute([$user_id, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['email'])) {
            // 3. Enviar correo
            return sendRoleNotification(
                $user['email'],
                $user['nombre'],
                $user['role'],   // usamos el role real del user
                $subject,
                $message
            );
        }

        return false;

    } catch (Exception $e) {
        error_log("Error en notifyUser: " . $e->getMessage());
        return false;
    }
}

function notificarRespuestaTicket($ticketId, $idUsuario) {
    $titulo = "Nueva respuesta en ticket #$ticketId";
    $mensaje = "Tu ticket <strong>#$ticketId</strong> ha recibido una nueva respuesta. Ingresa al sistema para revisarla.";

    notifyUser($GLOBALS['pdo'], $idUsuario, 'usuario', $titulo, $mensaje);
}