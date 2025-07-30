<?php
session_start();
require_once("checklogin.php");
check_login("admin");
require("dbconnection.php");

$admin_id = $_SESSION['user_id'] ?? null;

if (!isset($_GET['apply_id']) || !is_numeric($_GET['apply_id'])) {
    die("ID de solicitud no válido.");
}
$apply_id = (int) $_GET['apply_id'];

$stmt = $pdo->prepare("
    SELECT a.*, t.subject, t.id AS ticket_id, u.name AS tecnico_nombre
    FROM application_approv a
    JOIN ticket t ON t.id = a.ticket_id
    JOIN user u ON u.id = a.tech_id
    WHERE a.id = :apply_id
");
$stmt->execute(['apply_id' => $apply_id]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$solicitud) {
    die("Solicitud no encontrada.");
}

$stmt2 = $pdo->prepare("SELECT * FROM messg_tech_admin WHERE apply_id = :apply_id ORDER BY date ASC");
$stmt2->execute(['apply_id' => $apply_id]);
$mensajes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Chat con Técnico | SPE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="../styles/tickets/chat-tech-admin_(admin).css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include("header.php"); ?>

    <div class="container-fluid p-0 mt-5">
        <div class="chat-container bg-white">
            <div class="chat-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        Ticket #<?= htmlspecialchars($solicitud['ticket_id']) ?> - <?= htmlspecialchars($solicitud['subject']) ?>
                    </h5>
                    <a href="chat-list-admin.php" class="btn btn-sm btn-light back-btn">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
                <div class="mt-2">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-tech">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-white-80"><small>Técnico asignado:</small></p>
                            <p class="mb-0 fw-bold text-white"><?= htmlspecialchars($solicitud['tecnico_nombre']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="chatMessages" class="chat-messages">
                <?php if (count($mensajes) > 0): ?>
                    <?php 
                    $currentDate = null;
                    foreach ($mensajes as $msg): 
                        $messageDate = date('Y-m-d', strtotime($msg['date']));
                        if ($currentDate !== $messageDate) {
                            $currentDate = $messageDate;
                            echo '<div class="message-divider text-center text-muted small my-2">'.date('d/m/Y', strtotime($currentDate)).'</div>';
                        }
                    ?>
                        <div class="<?= ($msg['emisor'] === 'admin') ? 'align-self-end' : 'align-self-start' ?>">
                            <div class="d-flex <?= ($msg['emisor'] === 'admin') ? 'flex-row-reverse' : 'flex-row' ?> align-items-end">
                                <?php if ($msg['emisor'] !== 'admin'): ?>
                                    <div class="avatar avatar-tech">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="avatar avatar-admin">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <div class="<?= ($msg['emisor'] === 'admin') ? 'message-admin' : 'message-tech' ?> p-3">
                                        <div class="message-content"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                        <small class="message-time">
                                            <?= date('H:i', strtotime($msg['date'])) ?>
                                            <i class="fas fa-check-circle ms-2" style="font-size: 0.7rem;"></i>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-chat">
                        <i class="far fa-comment-dots fa-3x mb-3"></i>
                        <p class="mb-1">No hay mensajes aún</p>
                        <small class="text-muted">Envía un mensaje para iniciar la conversación</small>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($solicitud['status'] === 'pendiente'): ?>
                <div class="chat-input">
                    <form id="formMensaje" class="d-flex flex-column gap-3">
                        <input type="hidden" id="apply_id" value="<?= $apply_id ?>">
                        
                        <div class="d-flex align-items-center gap-2">
                            <textarea id="message" rows="1" class="form-control flex-grow-1" 
                                placeholder="Escribe tu mensaje aquí..." required 
                                style="border-radius: 20px; padding: 10px 15px; resize: none;"></textarea>
                            <button type="button" id="scrollToBottom" class="btn btn-outline-secondary rounded-circle" title="Ir al final">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" data-action="aprobar" class="btn btn-success action-btn">
                                    <i class="fas fa-check-circle me-1"></i> Aprobar
                                </button>
                                <button type="submit" data-action="rechazar" class="btn btn-danger action-btn">
                                    <i class="fas fa-times-circle me-1"></i> Rechazar
                                </button>
                                <button type="submit" data-action="resuelto" class="btn btn-primary action-btn">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar
                                </button>
                            </div>
                            <small class="text-muted">Estado: 
                                <span class="badge status-badge 
                                    <?= $solicitud['status'] === 'pendiente' ? 'bg-warning text-dark' : 
                                       ($solicitud['status'] === 'aprobado' ? 'bg-success' : 
                                       ($solicitud['status'] === 'rechazado' ? 'bg-danger' : 'bg-info text-dark')) ?>">
                                    <?= ucfirst($solicitud['status']) ?>
                                </span>
                            </small>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll al final del chat
        function scrollToBottom() {
            const chatContainer = document.getElementById('chatMessages');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            scrollToBottom();
            
            // Botón para scroll al final
            document.getElementById('scrollToBottom')?.addEventListener('click', scrollToBottom);
            
            const socket = io("http://localhost:3000");
            const applyId = <?= $apply_id ?>;
            const chatMessages = document.getElementById('chatMessages');
            const messageInput = document.getElementById('message');
            const form = document.getElementById('formMensaje');

            // Unirse a la sala del chat
            const room = `apply_${applyId}`;
            socket.emit("joinRoom", room);
            console.log("✅ Unido a sala:", room);

            // Escuchar nuevos mensajes
            socket.on("newMessage", (data) => {
                console.log("📥 Mensaje recibido:", data);

                // Validar que sea para esta sala y tipo admin
                if (data.tipo_chat !== "admin" || parseInt(data.chat_id) !== applyId) {
                    console.warn("Mensaje no relevante para esta sala");
                    return;
                }

                const emptyChat = chatMessages.querySelector('.empty-chat');
                if (emptyChat) emptyChat.remove();

                const messageDate = new Date(data.timestamp);
                const currentDate = messageDate.toISOString().split('T')[0];
                
                // Verificar si necesitamos añadir un divisor de fecha
                const lastDivider = chatMessages.lastElementChild;
                const lastMessageDate = lastDivider?.classList?.contains('message-divider') ? 
                    lastDivider.textContent : null;
                
                if (!lastMessageDate || lastMessageDate !== messageDate.toLocaleDateString()) {
                    const divider = document.createElement("div");
                    divider.className = "message-divider text-center text-muted small my-2";
                    divider.textContent = messageDate.toLocaleDateString();
                    chatMessages.appendChild(divider);
                }

                const wrapper = document.createElement("div");
                wrapper.className = data.sender === 'admin' ? 'align-self-end' : 'align-self-start';
                
                wrapper.innerHTML = `
                    <div class="d-flex ${data.sender === 'admin' ? 'flex-row-reverse' : 'flex-row'} align-items-end">
                        ${data.sender !== 'admin' ? `
                            <div class="avatar avatar-tech">
                                <i class="fas fa-user-tie"></i>
                            </div>` : `
                            <div class="avatar avatar-admin">
                                <i class="fas fa-user-shield"></i>
                            </div>`}
                        <div>
                            <div class="${data.sender === 'admin' ? 'message-admin' : 'message-tech'} p-3">
                                <div class="message-content">${data.mensaje}</div>
                                <small class="message-time">
                                    ${messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                    <i class="fas fa-check-circle ms-2" style="font-size: 0.7rem;"></i>
                                </small>
                            </div>
                        </div>
                    </div>
                `;
                
                chatMessages.appendChild(wrapper);
                scrollToBottom();
            });

            // Envío de mensajes
            if (form) {
                form.addEventListener("submit", function (e) {
                    e.preventDefault();
                    const mensaje = messageInput.value.trim();
                    if (!mensaje) return;

                    const action = e.submitter?.dataset.action || "resuelto";

                    socket.emit("sendMessage", {
                        chat_id: applyId,
                        tipo_chat: "admin",
                        sender: "admin",
                        mensaje: mensaje
                    });

                    messageInput.value = "";
                    messageInput.focus();

                    // Enviar actualización de estado (opcional)
                    if (["aprobar", "rechazar", "resuelto"].includes(action)) {
                        fetch("update-status.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `apply_id=${applyId}&action=${action}`
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }

            // Autoajuste del textarea
            if (messageInput) {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        });
    </script>
</body>
</html>