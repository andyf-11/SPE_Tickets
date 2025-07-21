<?php
// notifications_helper.php
require_once 'send-notifications.php';
require_once 'dbconnection.php';

/**
 * Notifica al admin y supervisor cuando se crea un ticket.
 */
function notificarCreacionTicket($ticket_id) {
    $mensaje = "Se ha creado un nuevo ticket #$ticket_id";

    sendNotification($mensaje, 'admin', null, "admin/ver_ticket.php?id=$ticket_id");
    sendNotification($mensaje, 'supervisor', null, "supervisor/ver_ticket.php?id=$ticket_id");
}

/**
 * Notifica al técnico cuando se le asigna un ticket.
 */
function notificarAsignacionTicket($ticket_id, $tecnico_id) {
    $mensaje = "Se te ha asignado el ticket #$ticket_id";

    sendNotification($mensaje, null, $tecnico_id, "tecnico/ver_ticket.php?id=$ticket_id");

    // Notificar al admin para control
    sendNotification("El ticket #$ticket_id ha sido asignado al técnico.", 'admin', null, "admin/ver_ticket.php?id=$ticket_id");
}

/**
 * Notifica cuando el técnico responde un ticket.
 */
function notificarRespuestaTicket($ticket_id, $id_usuario) {
    $mensaje = "El ticket #$ticket_id ha sido respondido por el técnico.";

    sendNotification($mensaje, 'usuario', $id_usuario, "usuario/view_tickets.php?id=$ticket_id");
    sendNotification($mensaje, 'supervisor', null, "supervisor/manage_tickets.php?id=$ticket_id");
    sendNotification($mensaje, 'admin', null, "admin/manage_tickets.php?id=$ticket_id");
}

/**
 * Notifica cuando hay una nueva solicitud de soporte.
 */
function notificarNuevaSolicitud($solicitud_id) {
    $mensaje = "Nueva solicitud de soporte técnico (ID: $solicitud_id).";

    sendNotification($mensaje, 'admin', null, "admin/ver_solicitud.php?id=$solicitud_id");
}
