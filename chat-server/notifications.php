<?php
session_start();
require_once("../dbconnection.php");
require_once("../checklogin.php");
check_login();

$userId = $_SESSION['user_id'] ?? 0;
$role   = $_SESSION['user_role'] ?? '';
?>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// VARIABLES DE SESIÓN
const userId = <?= json_encode($userId) ?>;
const role   = <?= json_encode($role) ?>;


// CONEXIÓN SOCKET
const socket = io("http://localhost:3000");

// Unirse a salas
socket.emit('joinNotificationRoom', { userId: userId, role: role });

// ==========================
// HELPER: ENVIAR NOTIFICACIÓN
// role -> enviar a todos de ese rol
// usuarioId -> enviar a un usuario específico
// senderId -> quien envía la acción (por defecto yo mismo)
function enviarNotificacion(mensaje, role = null, usuarioId = null) {
    fetch("http://localhost:3000/notificar", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            mensaje,
            role,
            usuarioId,
            senderId: userId
        })
    })
    .then(res => res.json())
    .then(data => console.log("📨 Notificación enviada:", data))
    .catch(err => console.error("❌ Error enviando notificación:", err));
}


// ESCUCHAR NOTIFICACIONES
socket.on("receiveNotification", (data) => {
    console.log("🔔 Notificación recibida:", data);

    // Ignorar notificaciones propias
    if (data.senderId && parseInt(data.senderId) === parseInt(userId)) {
        console.log("⏩ Notificación ignorada (era mía)");
        return;
    }

   
    toastr.info(data.mensaje);

    // Actualizar contador en el badge
    const badge = document.getElementById("noti-count");
    if (badge) {
        let current = parseInt(badge.innerText || "0");
        badge.innerText = current + 1;
        badge.classList.remove("d-none");
    }

    // Insertar en lista de notificaciones si existe
    const lista = document.getElementById("lista-notificaciones");
    if (lista) {
        const nueva = document.createElement("div");
        nueva.classList.add("card", "mb-2", "fw-bold");
        nueva.innerHTML = `
            <div class="card-body">
                <a href="#" class="text-decoration-none">${data.mensaje}</a>
                <div><small class="text-muted">Ahora</small></div>
            </div>
        `;
        lista.prepend(nueva);
    }
});


// CONEXIÓN DESCONECTADA
socket.on('disconnect', () => {
    console.log('🔴 Desconectado del servidor de notificaciones');
});
</script>