<?php
session_start();
require_once("dbconnection.php");
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
  <title>Chat con Técnico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
  <style>
    #chat-messages {
      height: 400px;
      overflow-y: auto;
      background-color: #f8f9fa;
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 5px;
    }
    .mensaje-tecnico { text-align: right; color: #0d6efd; }
    .mensaje-usuario { text-align: left; color: #212529; }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        Chat con el Técnico: <?= htmlspecialchars($tecnico['name']); ?>
      </div>
      <div class="card-body">
        <div id="chat-messages">
          <?php if ($mensajes): ?>
            <?php foreach ($mensajes as $msg): ?>
              <div class="<?= $msg['sender'] === 'usuario' ? 'mensaje-usuario' : 'mensaje-tecnico'; ?> mb-2">
                <strong><?= htmlspecialchars(ucfirst($msg['sender'])) ?>:</strong>
                <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                <small class="text-muted"><?= $msg['timestamp'] ?></small>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted">No hay mensajes aún.</p>
          <?php endif; ?>
        </div>

        <?php if ($chat['status_chat'] === 'abierto'): ?>
          <form id="formMensaje" class="mt-3">
            <div class="input-group">
              <input type="text" id="mensajeInput" class="form-control" placeholder="Escribe tu mensaje..." required autocomplete="off">
              <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-secondary mt-3">Este chat está cerrado.</div>
        <?php endif; ?>

        <a href="chat-list-tech.php" class="btn btn-secondary mt-3">Volver a Chats</a>
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

      socket.emit("joinRoom", chatId);

      socket.on("newMessage", (data) => {
        if (data.chat_id != chatId || data.tipo_chat !== "usuario") return;

        const div = document.createElement("div");
        div.classList.add("mb-2");
        div.classList.add(data.sender === 'usuario' ? 'mensaje-usuario' : 'mensaje-tecnico');
        div.innerHTML = `
          <strong>${data.sender.charAt(0).toUpperCase() + data.sender.slice(1)}:</strong>
          <div>${data.mensaje}</div>
          <small class="text-muted">${new Date(data.timestamp).toLocaleString()}</small>
        `;
        chatMessages.appendChild(div);
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
    });
  </script>
</body>
</html>
