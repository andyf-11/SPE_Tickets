<?php
// send_notification.php
require_once("dbconnection.php");

/**
 * Envía una notificación a Node.js y la guarda en la base de datos.
 *
 * @param string $mensaje   Texto de la notificación.
 * @param string|null $rol  Rol destinatario ('admin', 'supervisor', 'tecnico', 'usuario') o null.
 * @param int|null $usuarioId ID específico del usuario (para notificación individual) o null.
 * @param string|null $link  URL para redirigir al usuario cuando haga clic.
 * @return bool
 */
function sendNotification($mensaje, $rol = null, $usuarioId = null, $link = '#')
{
    global $pdo;

    // 1. Guardar en la base de datos
    try {
        $sql = "INSERT INTO notifications (user_id, role, message, link, is_read, created_at)
                VALUES (:user_id, :role, :message, :link, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $usuarioId,
            ':role'    => $rol,
            ':message' => $mensaje,
            ':link'    => $link
        ]);
    } catch (Exception $e) {
        error_log("❌ Error guardando notificación en DB: " . $e->getMessage());
    }

    // 2. Enviar a Node.js vía HTTP (Socket.IO)
    $data = [
        'mensaje'   => $mensaje,
        'rol'       => $rol,
        'usuarioId' => $usuarioId
    ];

    $ch = curl_init('http://localhost:3000/notificar'); // Node.js endpoint
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response !== false;
}
