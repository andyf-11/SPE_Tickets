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
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
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
    }

    .chat-header {
      background-color: #4e73df;
      color: white;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
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
      padding: 10px 15px;
      border-radius: 15px;
      position: relative;
    }

    .message-user {
      background-color: #e3f2fd;
      margin-left: auto;
      border-bottom-right-radius: 5px;
    }

    .message-tech {
      background-color: #ffffff;
      border: 1px solid #e0e0e0;
      border-bottom-left-radius: 5px;
    }

    .message-time {
      font-size: 0.75rem;
      color: #6c757d;
      margin-top: 5px;
      display: block;
    }

    .message-sender {
      font-weight: 600;
      margin-bottom: 5px;
      color: #4e73df;
    }

    .chat-input {
      border-top: 1px solid #e0e0e0;
      padding: 15px;
      background-color: #ffffff;
      border-bottom-left-radius: 10px;
      border-bottom-right-radius: 10px;
    }

    .btn-send {
      background-color: #4e73df;
      border: none;
    }

    .btn-send:hover {
      background-color: #3a5bbf;
    }

    .status-badge {
      font-size: 0.9rem;
      padding: 5px 10px;
    }
  </style>
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
    document.addEventListener('DOMContentLoaded', function () {
      const socket = io("http://localhost:3000");
      const chatId = <?= $chat_id ?>;
      const tipoChat = "usuario"; // Esto era el error antes ❌
      const sender = "<?= $role ?>";

      // ⚠️ CORRECTO para técnico:
      const fixedTipoChat = "usuario"; // así lo interpreta el server.js, no poner 'tecnico'

      socket.emit('joinRoom', chatId);

      socket.on("newMessage", (data) => {
        if (data.chat_id != chatId || data.tipo_chat !== fixedTipoChat) return;

        const chatMessages = document.getElementById("chatMessages");
        const noMessages = chatMessages.querySelector('.text-center.text-muted');
        if (noMessages) noMessages.remove();

        const div = document.createElement("div");
        div.classList.add("message", data.sender === 'tecnico' ? 'message-tech' : 'message-user');
        div.innerHTML = `
        <div class="message-sender">${data.sender === 'tecnico' ? 'Tú' : data.sender.charAt(0).toUpperCase() + data.sender.slice(1)}</div>
        <div>${data.mensaje}</div>
        <small class="message-time">
          <i class="far fa-clock me-1"></i>${new Date(data.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
        </small>
      `;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      });

      const form = document.getElementById("formMensaje");
      const mensajeInput = document.getElementById("mensajeInput");

      form.addEventListener("submit", function (e) {
        e.preventDefault();
        const mensaje = mensajeInput.value.trim();
        if (!mensaje) return;

        socket.emit("sendMessage", {
          chat_id: chatId,
          tipo_chat: fixedTipoChat,
          sender: sender,
          mensaje: mensaje
        });

        mensajeInput.value = "";
        mensajeInput.focus();
      });

      const btnCerrarChat = document.getElementById('btnCerrarChat');
      if (btnCerrarChat) {
        btnCerrarChat.addEventListener('click', function () {
          if (!confirm("¿Estás seguro de que deseas cerrar este chat? No podrás enviar más mensajes.")) return;

          fetch('close-chat-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'chat_id=<?= $chat_id ?>'
          })
            .then(response => response.text())
            .then(data => {
              alert(data);
              location.reload();
            })
            .catch(() => alert('Error al cerrar el chat.'));
        });
      }

      // Auto-scroll to bottom on load
      const chatMessages = document.getElementById("chatMessages");
      chatMessages.scrollTop = chatMessages.scrollHeight;
    });
  </script>

  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="../chat-server/notifications.js"></script>

</body>

</html>