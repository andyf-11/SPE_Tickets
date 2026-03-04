<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? ''; // debe ser 'tecnico'
$chat_id = intval($_GET['chat_id'] ?? 0);

if (!$chat_id) {
  die("Chat inválido.");
}

// Verificar que el técnico tiene acceso
$stmt = $pdo->prepare("SELECT * FROM chat_user_tech WHERE id = ?");
$stmt->execute([$chat_id]);
$chat = $stmt->fetch();

if (!$chat || $chat['tech_id'] != $user_id) {
  die("No tienes acceso a este chat.");
}

// Obtener mensajes
$stmt = $pdo->prepare("SELECT * FROM messg_tech_user WHERE chat_id = ? ORDER BY timestamp ASC");
$stmt->execute([$chat_id]);
$mensajes = $stmt->fetchAll();

$chatAbierto = ($chat['status_chat'] === 'abierto');
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat Técnico - Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
  <link href="../styles/tecnico/chat-users-techs.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-4">
    <div class="chat-container bg-white">
      <div class="chat-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Chat con Usuario</h5>
        <div>
          <?php if ($chatAbierto): ?>
            <button id="btnCerrarChat" class="btn btn-sm btn-light text-danger">
              <i class="fas fa-times-circle me-1"></i>Cerrar chat
            </button>
          <?php else: ?>
            <span class="badge bg-secondary status-badge">
              <i class="fas fa-lock me-1"></i>Chat cerrado
            </span>
          <?php endif; ?>
        </div>
      </div>

      <div id="chatMessages" class="chat-messages">
        <?php if ($mensajes): ?>
          <?php foreach ($mensajes as $msg): ?>
            <div class="message <?= $msg['sender'] === 'tecnico' ? 'message-tech' : 'message-user' ?>">
              <div class="message-sender">
                <?= htmlspecialchars(ucfirst($msg['sender'])) === 'Tecnico' ? 'Tú' : htmlspecialchars(ucfirst($msg['sender'])) ?>
              </div>
              <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
              <small class="message-time">
                <i class="far fa-clock me-1"></i><?= date('H:i', strtotime($msg['timestamp'])) ?>
              </small>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center text-muted py-4">
            <i class="far fa-comment-dots fa-2x mb-2"></i>
            <p>No hay mensajes aún</p>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($chatAbierto): ?>
        <div class="chat-input">
          <form id="formMensaje" class="d-flex align-items-center gap-2">
            <textarea id="mensajeInput" rows="2" class="form-control flex-grow-1" placeholder="Escribe tu mensaje..."
              required></textarea>
            <button type="submit" class="btn btn-send text-white">
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      <?php else: ?>
        <div class="chat-input text-center py-3">
          <div class="alert alert-secondary mb-0">
            <i class="fas fa-info-circle me-2"></i>Este chat está cerrado y no acepta nuevos mensajes
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="text-center mt-3">
      <a href="chat-list-users.php" class="btn btn-outline-dark">
        <i class="fas fa-arrow-left me-2"></i>Volver a la lista de chats
      </a>
    </div>
  </div>

    <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Validación de seguridad: Si no hay ID, se detiene el script
        const chatId = <?= intval($chat_id ?? 0) ?>;
        if (chatId === 0) {
            console.warn("⚠️ No se puede iniciar el chat: ID de chat inválido o ausente.");
            return;
        }

        const socket = io("http://localhost:3000");
        const chatMessages = document.getElementById("chatMessages");
        const tipoChat = "usuario"; 
        const sender = "tecnico";   

        // Unirse a la sala correcta
        socket.emit("joinRoom", `chat_${chatId}`);

        // ESCUCHAR mensajes nuevos
        socket.on("newMessage", (data) => {
            // Filtro de seguridad por ID de chat
            if (data.chat_id != chatId) return;

            const messageDiv = document.createElement("div");
            messageDiv.classList.add("message", "d-flex");

            // Lógica de burbujas: Técnico (Tú) a la derecha, Usuario a la izquierda
            if (data.sender === 'tecnico') {
                messageDiv.classList.add("message-user", "user-message");
            } else {
                messageDiv.classList.add("message-tech", "tech-message");
            }

            const now = new Date(data.timestamp);
            const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                               now.getMinutes().toString().padStart(2, '0');

            messageDiv.innerHTML = `
                <div>
                    <div class="message-content">${data.mensaje}</div>
                    <div class="message-time text-end">${timeString}</div>
                </div>
            `;

            // Limpiar mensaje de "No hay mensajes" si existe
            const emptyState = chatMessages.querySelector('.text-center');
            if (emptyState) emptyState.remove();

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });

        // ENVIAR mensajes
        const form = document.getElementById("formMensaje");
        const mensajeInput = document.getElementById("mensajeInput");

        if (form) {
            form.addEventListener("submit", (e) => {
                e.preventDefault();
                const mensaje = mensajeInput.value.trim();
                if (!mensaje) return;

               const formData = new FormData();
               formData.append('chat_id', chatId);
               formData.append('sender', sender);
               formData.append('message', mensaje);

               //Crea registro de la notificación
               fetch('send-message-tech-user.php', {
                method: 'POST',
                body: formData
               })
               .then(res => res.json())
               .then(data => {
                if (data.success) {
                  socket.emit("sendMessage", {
                    chat_id: chatId,
                    tipo_chat: tipoChat,
                    sender: sender,
                    mensaje: mensaje
                  });

                  //Dispara el toast
                  fetch("http://localhost:3000/notificar", {
                    method: "POST",
                    headers: { "Content-Type": "application/json"},
                    body: JSON.stringify({
                      mensaje: "El técnico te ha enviado un mensaje",
                      usuarioId: data.destinatario_id,
                      link: `chat-list-users.php?chat_id=${chatId}`
                    })
                  });

                  mensajeInput.value = "";
                  mensajeInput.focus();
                }
               })
            });
        }

        // --- LÓGICA DEL BOTÓN CERRAR  ---
        const btnCerrarChat = document.getElementById("btnCerrarChat");
        if (btnCerrarChat) {
            btnCerrarChat.addEventListener("click", () => {
                if(confirm("¿Estás seguro de cerrar este chat? Se bloquearán los mensajes nuevos.")){

                    const formData = new FormData(); 
                    formData.append('chat_id', chatId);

                    fetch('close-chat.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json()) 
                    .then(data => {
                        if (data.success) {
                            socket.emit("sendMessage", {
                                chat_id : chatId,
                                tipo_chat: "usuario",
                                sender: "sistema",
                                mensaje: "CHAT_FINALIZADO"
                            });

                            alert("Chat finalizado.");
                            window.location.href = "chat-list-users.php";
                        } else {
                            alert("Error al cerrar el chat.");
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        }

        // Auto-scroll inicial
        chatMessages.scrollTop = chatMessages.scrollHeight;


    });
</script>
  <script src="../chat-server/notifications.js"></script>

</body>

</html>