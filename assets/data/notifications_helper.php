<?php
// notifications_helper.php
require_once 'send-notifications.php';
require_once(__DIR__ . "/../../dbconnection.php");

/**
 * Mapeo de rutas por rol para tickets o chats
 */
function getLinkForRole($rol, $id, $tipo = 'ticket') {
    $links = [
        'usuario'     => "../../usuario/" . ($tipo === 'ticket' ? "view_tickets.php?id=$id" : "view_tickets.php?id=$id"),
        'tecnico'     => "../../tecnico/" . ($tipo === 'ticket' ? "manage_ticket.php?id=$id" : "manage_ticket.php?id=$id"),
        'admin'       => "../../admin/" . ($tipo === 'ticket' ? "manage_ticket.php?id=$id" : "chat-tech-admin.php?id=$id"),
        'supervisor'  => "../../supervisor/" . ($tipo === 'ticket' ? "manage_ticket.php?id=$id" : "manage_ticket.php?id=$id")
    ];

    return $links[$rol] ?? "#";
}

/**
 * Función genérica para enviar notificaciones a varios roles
 */
function enviarNotificacion($mensaje, $roles = [], $usuarioId = null, $tipo = 'ticket', $id = null) {
    foreach ($roles as $rol) {
        $link = $id ? getLinkForRole($rol, $id, $tipo) : "#";
        sendNotification($mensaje, $rol, $usuarioId, $link);
    }
}

/**
 * Notifica al admin y supervisor cuando se crea un ticket
 */
function notificarCreacionTicket($ticket_id) {
    $mensaje = "Se ha creado un nuevo ticket #$ticket_id creado por el usuario.";
    
enviarNotificacion($mensaje, ['admin', 'supervisor'], null, 'ticket', $ticket_id);
}

/**
 * Notifica al técnico cuando se le asigna un ticket
 */
function notificarAsignacionTicket($ticket_id, $tecnico_id) {
    $mensaje = "Se te ha asignado el ticket #$ticket_id";
    enviarNotificacion($mensaje, ['tecnico'], $tecnico_id, 'ticket', $ticket_id);

    // Notificar al admin para control
    enviarNotificacion("El ticket #$ticket_id ha sido asignado al técnico.", ['admin'], null, 'ticket', $ticket_id);
}

/**
 * Notifica cuando el técnico responde un ticket
 */
function notificarRespuestaTicket($ticket_id, $id_usuario) {
    //Mensaje para el usuario
    $mensajeUsuario = "El ticket #$ticket_id ha sido respondido por el técnico.";
    enviarNotificacion($mensajeUsuario, ['usuario'], $id_usuario, 'ticket', $ticket_id);

    //Mensaje para el admin
    $mensajeAdmin = "El técnico ha respondido al ticket (ID interno: $ticket_id).";
    enviarNotificacion($mensajeAdmin, ['admin', 'supervisor'], null, 'ticket', $ticket_id);
}

/**
 * Notifica cuando hay una nueva solicitud de soporte
 */
function notificarNuevaSolicitud($solicitud_id) {
    $mensaje = "Nueva solicitud de soporte técnico (ID: $solicitud_id).";
    enviarNotificacion($mensaje, ['admin'], null, 'chat', $solicitud_id);
}

/**
 * Notifica cuando hay un nuevo mensaje de chat
 */
function notificarNuevoMensajeChat($chat_id, $usuarioIdDestino, $rolDestino) {
    $mensaje = "Tienes un nuevo mensaje en el chat #$chat_id";
    enviarNotificacion($mensaje, [$rolDestino], $usuarioIdDestino, 'chat', $chat_id);
}

/**
 * Notifica a un usuario sobre permisos o contraseñas
 */
function notificarPermisoPassword($usuarioId, $mensaje) {
    sendNotification($mensaje, 'usuario', $usuarioId, "../../usuario/change-password.php");
}
