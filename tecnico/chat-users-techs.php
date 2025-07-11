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
  <title>Chat Técnico - Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>
<body>
<div class="container py-4">
  <h3>Chat Técnico - Usuario</h3>

  <div class="mb-3 d-flex justify-content-between align-items-center">
    <?php if ($chatAbierto): ?>
      <button id="btnCerrarChat" class="btn btn-danger">Cerrar chat</button>
    <?php else: ?>
      <span class="badge bg-secondary">Chat cerrado</span>
    <?php endif; ?>
    <a href="chat-list-users.php" class="btn btn-secondary">Volver a la lista de chats</a>
  </div>

  <div id="chatMessages" class="mb-4 border rounded p-3"
       style="height: 300px; overflow-y: scroll; background: #f8f9fa;">
    <?php if ($mensajes): ?>
      <?php foreach ($mensajes as $msg): ?>
        <div class="mb-2">
          <strong><?= htmlspecialchars(ucfirst($msg['sender'])) ?>:</strong>
          <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
          <small class="text-muted"><?= $msg['timestamp'] ?></small>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No hay mensajes aún.</p>
    <?php endif; ?>
  </div>

  <?php if ($chatAbierto): ?>
    <form id="formMensaje">
      <div class="mb-3">
        <textarea id="mensajeInput" rows="3" class="form-control" placeholder="Escribe tu mensaje..." required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
  <?php else: ?>
    <div class="alert alert-secondary mt-3">Este chat está cerrado.</div>
  <?php endif; ?>
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
      const div = document.createElement("div");
      div.classList.add("mb-2");
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
    });

    const btnCerrarChat = document.getElementById('btnCerrarChat');
    if (btnCerrarChat) {
      btnCerrarChat.addEventListener('click', function () {
        if (!confirm("¿Cerrar este chat?")) return;

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
  });
</script>
</body>
</html>
