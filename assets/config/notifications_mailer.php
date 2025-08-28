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
    global $pdo;

    $titulo = "Nueva respuesta en ticket #$ticketId";
    $mensaje = "Tu ticket <strong>#$ticketId</strong> ha recibido una nueva respuesta. Ingresa al sistema para revisarla.";
    $link = "/usuario/ver_ticket.php?id=" . $ticketId;

    // 1️⃣ Guardar notificación en DB
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, message, link, is_read, created_at)
        VALUES (:user_id, :type, :message, :link, 0, NOW())
    ");
    $stmt->execute([
        ':user_id' => $idUsuario,
        ':type' => 'respuesta_ticket',
        ':message' => $mensaje,
        ':link' => $link
    ]);

    // 2️⃣ Enviar correo
    notifyUser($pdo, $idUsuario, 'usuario', $titulo, $mensaje);

    // 3️⃣ Emitir notificación por WebSocket a tu endpoint Node.js
    $data = [
        'mensaje' => strip_tags($mensaje), // mejor enviar sin HTML
        'role' => 'usuario',               // opcional, según tu server.js
        'usuarioId' => $idUsuario
    ];

    $ch = curl_init("http://localhost:3000/notificar");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // $response contiene { success: true } si todo salió bien
}