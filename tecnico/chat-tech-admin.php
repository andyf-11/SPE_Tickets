<?php
session_start();
require_once("checklogin.php");
check_login("tecnico");
require("dbconnection.php");

$tecnico_id = $_SESSION['user_id'];

// Obtener ticket_id desde GET o POST
$ticket_id = $_GET['ticket_id'] ?? $_POST['ticket_id'] ?? null;
if (!$ticket_id || !is_numeric($ticket_id)) {
    die("ID de ticket no válido.");
}
$ticket_id = (int) $ticket_id;

// Buscar si existe conversación (apply_id) para este ticket y técnico
$stmt = $pdo->prepare("SELECT id FROM application_approv WHERE ticket_id = :ticket_id AND tech_id = :tech_id LIMIT 1");
$stmt->execute(['ticket_id' => $ticket_id, 'tech_id' => $tecnico_id]);
$apply = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apply) {
    // Crear conversación nueva
    $stmtInsert = $pdo->prepare("INSERT INTO application_approv (ticket_id, tech_id, status) VALUES (:ticket_id, :tech_id, 'pendiente')");
    $stmtInsert->execute(['ticket_id' => $ticket_id, 'tech_id' => $tecnico_id]);
    $apply_id = $pdo->lastInsertId();
} else {
    $apply_id = $apply['id'];
}

// Obtener asunto del ticket
$stmt2 = $pdo->prepare("SELECT subject FROM ticket WHERE id = :ticket_id");
$stmt2->execute(['ticket_id' => $ticket_id]);
$ticket = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$ticket) {
    die("Ticket no encontrado.");
}

// Obtener mensajes de la conversación
$stmt3 = $pdo->prepare("SELECT * FROM messg_tech_admin WHERE apply_id = :apply_id ORDER BY date ASC");
$stmt3->execute(['apply_id' => $apply_id]);
$mensajes = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat de Aprobación - Ticket #<?= htmlspecialchars($ticket_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <link href="../styles/tech.css" rel="stylesheet">
    <style>
        body {
            background: #BE93C5;
            /* fallback for old browsers */
            background: -webkit-linear-gradient(to left, #7BC6CC, #BE93C5);
            /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to left, #7BC6CC, #BE93C5);
            /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

        }

        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .chat-header {
            background-color: #4e73df;
            color: white;
            padding: 15px 20px;
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            background-color: #f8f9fc;
            padding: 20px;
        }

        .message {
            margin-bottom: 15px;
            max-width: 80%;
            padding: 12px 15px;
            border-radius: 15px;
            position: relative;
            word-wrap: break-word;
        }

        .message-tech {
            background-color: #e3f2fd;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }

        .message-admin {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
            display: block;
            text-align: right;
        }

        .message-sender {
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tech-sender {
            color: #2c5282;
            justify-content: flex-end;
        }

        .admin-sender {
            color: #4e73df;
            justify-content: flex-start;
        }

        .chat-input {
            border-top: 1px solid #e0e0e0;
            padding: 15px;
            background-color: #ffffff;
        }

        .btn-send {
            background-color: #4e73df;
            border: none;
            min-width: 100px;
        }

        .btn-send:hover {
            background-color: #3a5bbf;
        }

        .empty-chat {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="chat-container bg-white">
            <div class="chat-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        Ticket #<?= htmlspecialchars($ticket_id) ?> - <?= htmlspecialchars($ticket['subject']) ?>
                    </h5>
                    <a href="chat-list.php" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
                <div class="mt-2">
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-user-shield me-1"></i> Chat con Administración
                    </span>
                </div>
            </div>

            <div id="chatMessages" class="chat-messages">
                <?php if (count($mensajes) > 0): ?>
                    <?php foreach ($mensajes as $msg): ?>
                        <div class="message <?= $msg['emisor'] == 'tecnico' ? 'message-tech' : 'message-admin' ?>">
                            <div class="message-sender <?= $msg['emisor'] == 'tecnico' ? 'tech-sender' : 'admin-sender' ?>">
                                <?php if ($msg['emisor'] == 'admin'): ?>
                                    <i class="fas fa-user-shield"></i>
                                <?php endif; ?>
                                <?= $msg['emisor'] == 'tecnico' ? 'Tú' : 'Administrador' ?>
                                <?php if ($msg['emisor'] == 'tecnico'): ?>
                                    <i class="fas fa-user-cog"></i>
                                <?php endif; ?>
                            </div>
                            <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                            <small class="message-time">
                                <i class="far fa-clock me-1"></i><?= date('H:i', strtotime($msg['date'])) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-chat">
                        <i class="far fa-comment-dots fa-3x mb-3"></i>
                        <p>No hay mensajes aún</p>
                        <small class="text-muted">Envía un mensaje para iniciar la conversación</small>
                    </div>
                <?php endif; ?>
            </div>

            <div class="chat-input">
                <form id="formMensaje" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="apply_id" value="<?= $apply_id ?>">
                    <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
                    <input type="hidden" name="emisor" value="tecnico">
                    <textarea id="mensajeInput" name="message" class="form-control" placeholder="Escribe tu mensaje..."
                        rows="1" required autocomplete="off"></textarea>
                    <button type="submit" class="btn btn-send text-white">
                        <i class="fas fa-paper-plane me-1"></i> Enviar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const socket = io("http://localhost:3000");
            const applyId = <?= $apply_id ?>;
            const sender = "tecnico";
            const tipoChat = "admin"; // para distinguirlo del otro tipo

            socket.emit("joinRoom", `apply_${applyId}`);

            // Escuchar nuevos mensajes
            socket.on("newMessage", (data) => {
                if (data.tipo_chat !== "admin" || data.chat_id != applyId) return;

                const chatMessages = document.getElementById("chatMessages");
                const emptyChat = chatMessages.querySelector('.empty-chat');
                if (emptyChat) emptyChat.remove();

                const msgDiv = document.createElement("div");
                msgDiv.classList.add("message", data.sender === 'tecnico' ? 'message-tech' : 'message-admin');
                msgDiv.innerHTML = `
                    <div class="message-sender ${data.sender === 'tecnico' ? 'tech-sender' : 'admin-sender'}">
                        ${data.sender === 'admin' ? '<i class="fas fa-user-shield"></i>' : ''}
                        ${data.sender === 'tecnico' ? 'Tú' : 'Administrador'}
                        ${data.sender === 'tecnico' ? '<i class="fas fa-user-cog"></i>' : ''}
                    </div>
                    <div>${data.mensaje}</div>
                    <small class="message-time">
                        <i class="far fa-clock me-1"></i>${new Date(data.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </small>
                `;
                chatMessages.appendChild(msgDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });

            // Enviar mensaje
            document.getElementById("formMensaje").addEventListener("submit", function (e) {
                e.preventDefault();
                const mensajeInput = document.getElementById("mensajeInput");
                const mensaje = mensajeInput.value.trim();
                if (!mensaje) return;

                socket.emit("sendMessage", {
                    chat_id: applyId,
                    tipo_chat: "admin",
                    sender: "tecnico",
                    mensaje: mensaje
                });

                mensajeInput.value = "";
                mensajeInput.focus();
            });

            // Autoajuste del textarea
            const textarea = document.getElementById("mensajeInput");
            textarea.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Scroll al final del chat al cargar
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    </script>

</body>

</html>