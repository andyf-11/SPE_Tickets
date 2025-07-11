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
    <title>Chat de Aprobación - Ticket #<?= htmlspecialchars($ticket_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>

<body class="bg-light">
    <div class="container py-4">
        <h4>Chat de Aprobación - Ticket #<?= htmlspecialchars($ticket_id) ?> -
            <?= htmlspecialchars($ticket['subject']) ?>
        </h4>

        <div class="border rounded p-3 mb-3 bg-white" style="height: 400px; overflow-y: auto;">
            <?php if (count($mensajes) > 0): ?>
                <?php foreach ($mensajes as $msg): ?>
                    <div class="mb-2">
                        <strong><?= $msg['emisor'] == 'tecnico' ? 'Tú' : 'Admin' ?>:</strong>
                        <?= htmlspecialchars($msg['message']) ?><br>
                        <small class="text-muted"><?= $msg['date'] ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No hay mensajes aún.</p>
            <?php endif; ?>
        </div>

        <form id="formMensaje" class="d-flex gap-2">
            <input type="hidden" name="apply_id" value="<?= $apply_id ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
            <input type="hidden" name="emisor" value="tecnico">
            <input type="text" id="mensajeInput" name="message" class="form-control" placeholder="Escribe tu mensaje..." required
                autocomplete="off" />
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>

        <div class="mt-3">
            <a href="chat-list.php" class="btn btn-secondary">Volver a la lista de chats</a>
        </div>
    </div>

    <script>
        const socket = io("http://localhost:3000");
        const applyId = <?= $apply_id ?>;
        const sender = "tecnico";
        const tipoChat = "admin"; // para distinguirlo del otro tipo

        socket.emit("joinRoom", `apply_${applyId}`);

        // Escuchar nuevos mensajes
        socket.on("newMessage", (data) => {
            if (data.tipo_chat !== "admin" || data.chat_id != applyId) return;

            const chatContainer = document.querySelector(".border.rounded.p-3");
            const msgDiv = document.createElement("div");
            msgDiv.classList.add("mb-2");
            msgDiv.innerHTML = `
        <strong>${data.sender === 'tecnico' ? 'Tú' : 'Admin'}:</strong> ${data.mensaje}<br>
        <small class="text-muted">${new Date(data.timestamp).toLocaleString()}</small>
    `;
            chatContainer.appendChild(msgDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
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
        });
    </script>

</body>

</html>