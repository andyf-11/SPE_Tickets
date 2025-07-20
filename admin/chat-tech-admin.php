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
    <title>Chat con Técnico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <link href="../styles/admin.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fb;
        }

        .chat-container {
            max-height: 60vh;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .chat-container::-webkit-scrollbar {
            width: 8px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .message-admin {
            background-color: #4361ee;
            color: white;
            border-radius: 18px 18px 0 18px;
            max-width: 70%;
        }

        .message-tech {
            background-color: #3a0ca3;
            color: white;
            border-radius: 18px 18px 18px 0;
            max-width: 70%;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .message-input {
            border-radius: 20px;
            padding: 12px 20px;
            resize: none;
        }

        .action-btn {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
        }

        .chat-header {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>

    <div class="container py-4 mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Encabezado del chat -->
                <div class="chat-header mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="mb-0">
                            <i class="fas fa-comment-dots me-2 text-primary"></i>
                            Ticket #<?= htmlspecialchars($solicitud['ticket_id']) ?>:
                            <?= htmlspecialchars($solicitud['subject']) ?>
                        </h4>
                        <span class="badge status-badge 
                            <?php
                            switch ($solicitud['status']) {
                                case 'pendiente':
                                    echo 'bg-warning text-dark';
                                    break;
                                case 'aprobado':
                                    echo 'bg-success';
                                    break;
                                case 'rechazado':
                                    echo 'bg-danger';
                                    break;
                                case 'resuelto':
                                    echo 'bg-info text-dark';
                                    break;
                                default:
                                    echo 'bg-secondary';
                            }
                            ?>">
                            <i class="fas 
                                <?php
                                switch ($solicitud['status']) {
                                    case 'pendiente':
                                        echo 'fa-clock';
                                        break;
                                    case 'aprobado':
                                        echo 'fa-check-circle';
                                        break;
                                    case 'rechazado':
                                        echo 'fa-times-circle';
                                        break;
                                    case 'resuelto':
                                        echo 'fa-check-double';
                                        break;
                                    default:
                                        echo 'fa-question-circle';
                                }
                                ?> me-1">
                            </i>
                            <?= ucfirst($solicitud['status']) ?>
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="avatar">
                            <i class="fas fa-user-cog text-muted"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted"><small>Técnico asignado:</small></p>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($solicitud['tecnico_nombre']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Área de mensajes -->
                <div class="chat-container bg-white rounded-3 p-4 mb-4 shadow-sm">
                    <?php if (!$mensajes): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No hay mensajes en esta solicitud aún.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($mensajes as $msg): ?>
                                <div class="<?= ($msg['emisor'] === 'admin') ? 'align-self-end' : 'align-self-start' ?>">
                                    <div
                                        class="d-flex <?= ($msg['emisor'] === 'admin') ? 'flex-row-reverse' : 'flex-row' ?> align-items-end">
                                        <?php if ($msg['emisor'] !== 'admin'): ?>
                                            <div class="avatar me-2">
                                                <i class="fas fa-user-tie text-muted"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <div
                                                class="<?= ($msg['emisor'] === 'admin') ? 'message-admin' : 'message-tech' ?> p-3 shadow-sm">
                                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                            </div>
                                            <small
                                                class="d-block message-time mt-1 <?= ($msg['emisor'] === 'admin') ? 'text-end' : 'text-start' ?>">
                                                <?= date('d/m/Y H:i', strtotime($msg['date'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Formulario de mensajes -->
                <?php if ($solicitud['status'] === 'pendiente'): ?>
                    <form id="formMensaje" class="bg-white rounded-3 p-4 shadow-sm">
                        <input type="hidden" id="apply_id" value="<?= $apply_id ?>">

                        <div class="mb-3">
                            <label for="message" class="form-label fw-bold">Escribe tu mensaje:</label>
                            <textarea id="message" rows="3" class="form-control message-input"
                                placeholder="Escribe tu mensaje aquí..." required></textarea>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" data-action="aprobar" class="btn btn-success action-btn">
                                    <i class="fas fa-check me-1"></i> Aprobar
                                </button>
                                <button type="submit" data-action="rechazar" class="btn btn-danger action-btn">
                                    <i class="fas fa-times me-1"></i> Rechazar
                                </button>
                                <button type="submit" data-action="resuelto" class="btn btn-primary action-btn">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar
                                </button>
                            </div>
                            <a href="chat-list-admin.php" class="btn btn-outline-secondary action-btn">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll al final del chat
        document.addEventListener('DOMContentLoaded', function () {
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const socket = io("http://localhost:3000");
            const applyId = <?= $apply_id ?>;
            const chatContainer = document.querySelector('.chat-container');
            const messageInput = document.getElementById("message");
            const form = document.getElementById("formMensaje");

            // Unirse a la sala del chat
            const room = `apply_${applyId}`;
            socket.emit("joinRoom", room);
            console.log("✅ Unido a sala:", room);

            // Escuchar nuevos mensajes
            socket.on("newMessage", (data) => {
                console.log("📥 Mensaje recibido:", data);

                // Validar que sea para esta sala y tipo admin
                if (data.tipo_chat !== "admin") {
                    console.warn("⛔ tipo_chat no es 'admin'");
                    return;
                }

                if (parseInt(data.chat_id) !== applyId) {
                    console.warn("⛔ apply_id no coincide", data.chat_id, applyId);
                    return;
                }

                const wrapper = document.createElement("div");
                wrapper.className = (data.sender === 'admin') ? 'align-self-end' : 'align-self-start';

                wrapper.innerHTML = `
                <div class="d-flex ${data.sender === 'admin' ? 'flex-row-reverse' : 'flex-row'} align-items-end">
                    ${data.sender !== 'admin' ? `
                        <div class="avatar me-2">
                            <i class="fas fa-user-tie text-muted"></i>
                        </div>` : ''}
                    <div>
                        <div class="${data.sender === 'admin' ? 'message-admin' : 'message-tech'} p-3 shadow-sm">
                            ${data.mensaje}
                        </div>
                        <small class="d-block message-time mt-1 ${data.sender === 'admin' ? 'text-end' : 'text-start'}">
                            ${new Date(data.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </small>
                    </div>
                </div>
            `;

                chatContainer.appendChild(wrapper);
                chatContainer.scrollTop = chatContainer.scrollHeight;
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

            // Scroll inicial
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    </script>


</body>

</html>