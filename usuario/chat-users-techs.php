<?php
session_start();
require_once("../dbconnection.php");
require("checklogin.php");
check_login("usuario");

$user_id = $_SESSION['user_id'] ?? 0;
$chat_id = intval($_GET['chat_id'] ?? 0);

// Verificar que el chat pertenece a este usuario
$stmt = $pdo->prepare("SELECT * FROM chat_user_tech WHERE id = ? AND user_id = ?");
$stmt->execute([$chat_id, $user_id]);
$chat = $stmt->fetch();

if (!$chat) {
  die("Chat no encontrado o acceso no autorizado.");
}

// Obtener info del técnico
$stmt = $pdo->prepare("SELECT name FROM user WHERE id = ?");
$stmt->execute([$chat['tech_id']]);
$tecnico = $stmt->fetch();

// Obtener mensajes
$stmt = $pdo->prepare("SELECT * FROM messg_tech_user WHERE chat_id = ? ORDER BY timestamp ASC");
$stmt->execute([$chat_id]);
$mensajes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat con Técnico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link href="../styles/usuario/chat-users-techs.css" rel="stylesheet">
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>

<body class="bg-light">
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center mb-4">
          <a href="chat-list-tech.php" class="btn btn-primary back-btn me-3">
            <i class="bi bi-arrow-left"></i>
          </a>
          <h4 class="mb-0" style="color: white;">Chat con técnico</h4>
        </div>

        <div class="chat-container bg-white">
          <div class="chat-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 40px; height: 40px;">
                <i class="bi bi-person-fill-gear text-primary"></i>
              </div>
              <div>
                <h5 class="mb-0"><?= htmlspecialchars($tecnico['name']); ?></h5>
                <small class="opacity-75">Técnico de soporte</small>
              </div>
            </div>
            <span class="status-badge badge bg-<?= $chat['status_chat'] === 'abierto' ? 'success' : 'secondary'; ?>">
              <?= ucfirst($chat['status_chat']); ?>
            </span>
          </div>

          <div class="chat-messages" id="chat-messages">
            <?php if ($mensajes): ?>
              <?php foreach ($mensajes as $msg): ?>
                <div
                  class="message d-flex <?= $msg['sender'] === 'usuario' ? 'message-user user-message' : 'message-tech tech-message'; ?>">
                  <div>
                    <div class="message-content">
                      <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </div>
                    <div class="message-time text-end">
                      <?= date('H:i', strtotime($msg['timestamp'])) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center text-muted py-5">
                <i class="bi bi-chat-dots display-4 d-block mb-3"></i>
                <p>No hay mensajes aún.<br>Inicia la conversación con el técnico.</p>
              </div>
            <?php endif; ?>
          </div>

          <?php if ($chat['status_chat'] === 'abierto'): ?>
            <div class="chat-input-container">
              <form id="formMensaje" class="d-flex">
                <input type="text" id="mensajeInput" class="form-control rounded-end-0"
                  placeholder="Escribe tu mensaje..." required autocomplete="off">
                <button type="submit" class="btn btn-primary btn-send">
                  <i class="bi bi-send-fill"></i>
                </button>
              </form>
            </div>
          <?php else: ?>
            <div class="chat-input-container text-center py-4">
              <div class="alert alert-secondary mb-0">
                <i class="bi bi-chat-square-text"></i> Este chat está cerrado. No puedes enviar mensajes.
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const socket = io("http://localhost:3000");
      const chatId = <?= $chat_id ?>;
      const tipoChat = "usuario";
      const sender = "usuario";
      const chatMessages = document.getElementById("chat-messages");

      socket.emit("joinRoom", 'chat_${chatId}');

      socket.on("newMessage", (data) => {
        if (data.chat_id != chatId || data.tipo_chat !== "usuario") return;

        const messageDiv = document.createElement("div");
        messageDiv.classList.add("message", "d-flex");

        if (data.sender === 'usuario') {
          messageDiv.classList.add("message-user", "user-message");
        } else {
          messageDiv.classList.add("message-tech", "tech-message");
        }

        const now = new Date(data.timestamp);
        const timeString = now.getHours().toString().padStart(2, '0') + ':' +
          now.getMinutes().toString().padStart(2, '0');

        messageDiv.innerHTML = `
          <div>
            <div class="message-content">
              ${data.mensaje}
            </div>
            <div class="message-time text-end">
              ${timeString}
            </div>
          </div>
        `;

        // Remove empty state if it exists
        const emptyState = chatMessages.querySelector('.text-center');
        if (emptyState) {
          emptyState.remove();
        }

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      });

      const form = document.getElementById("formMensaje");
      const mensajeInput = document.getElementById("mensajeInput");

      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const mensaje = mensajeInput.value.trim();
        if (!mensaje) return;

        socket.emit("sendMessage", {
          chat_id: chatId,
          tipo_chat: tipoChat,
          sender: sender,
          mensaje: mensaje
        });

        mensajeInput.value = "";
      });

      // Auto-scroll to bottom on load
      chatMessages.scrollTop = chatMessages.scrollHeight;
    });
  </script>

  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="chat-server/notification.js"></script>

</body>

</html>