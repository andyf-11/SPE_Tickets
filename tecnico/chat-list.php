<?php
session_start();
require_once("checklogin.php");
check_login("tecnico");

require("dbconnection.php");

$tecnico_id = $_SESSION['user_id'];
$page = 'chat-list'; // Para el leftbar activo

$filtro = $_GET['filtro'] ?? 'todos';

switch ($filtro) {
    case 'hoy':
        $condicionFecha = "AND DATE(a.apply_date) = CURDATE()";
        break;
    case 'semana':
        $condicionFecha = "AND a.apply_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'mes':
        $condicionFecha = "AND a.apply_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        break;
    default:
        $condicionFecha = "";
}

$sql = "
SELECT
  a.id AS apply_id,
  a.ticket_id,
  a.status,
  t.subject,
  MAX(m.date) AS last_message_date,
  (SELECT message FROM messg_tech_admin WHERE apply_id = a.id ORDER BY date DESC LIMIT 1) AS last_message,
  (SELECT emisor FROM messg_tech_admin WHERE apply_id = a.id ORDER BY date DESC LIMIT 1) AS last_sender
FROM
  application_approv a
JOIN ticket t ON t.id = a.ticket_id
LEFT JOIN messg_tech_admin m ON m.apply_id = a.id
WHERE
  a.tech_id = :tech_id
  $condicionFecha
GROUP BY
  a.id, a.ticket_id, a.status, t.subject
ORDER BY
  last_message_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['tech_id' => $tecnico_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lista de Chats - Soporte Técnico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../styles/tech.css" rel="stylesheet">
  <style>
    .chat-card {
      border-left: 5px solid #0d6efd;
      transition: box-shadow 0.2s;
    }
    .chat-card.unread {
      background-color: #f8f9fa;
    }
    .chat-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .status-aprobado { color: green; }
    .status-pendiente { color: orange; }
    .status-rechazado { color: red; }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 col-lg-2 p-0">
        <?php include("leftbar.php"); ?>
      </div>

      <main class="col-md-9 col-lg-10 px-4 py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 mb-0">
            <i class="fas fa-comments me-2 text-primary"></i>Mis Chats
          </h1>
          <button onclick="location.reload()" class="btn btn-md btn-outline-secondary" style="margin-right: 15px;">
            <i class="fas fa-sync-alt me-1"></i> Actualizar
          </button>
        </div>

        <div class="mb-4">
          <div class="btn-group" role="group">
            <a href="?filtro=todos" class="btn btn-md <?= $filtro == 'todos' ? 'btn-primary' : 'btn-outline-primary' ?>">
              <i class="fas fa-inbox me-1"></i> Todos
            </a>
            <a href="?filtro=hoy" class="btn btn-md <?= $filtro == 'hoy' ? 'btn-primary' : 'btn-outline-primary' ?>">
              <i class="fas fa-calendar-day me-1"></i> Hoy
            </a>
            <a href="?filtro=semana" class="btn btn-md <?= $filtro == 'semana' ? 'btn-primary' : 'btn-outline-primary' ?>">
              <i class="fas fa-calendar-week me-1"></i> Esta semana
            </a>
            <a href="?filtro=mes" class="btn btn-md <?= $filtro == 'mes' ? 'btn-primary' : 'btn-outline-primary' ?>">
              <i class="fas fa-calendar-alt me-1"></i> Este mes
            </a>
          </div>
        </div>

        <?php if (count($chats) > 0): ?>
          <div class="row">
            <?php foreach ($chats as $chat): ?>
              <div class="col-md-6 col-lg-4 mb-4">
                <a href="chat.php?apply_id=<?= $chat['apply_id'] ?>&ticket_id=<?= $chat['ticket_id'] ?>" class="text-decoration-none">
                  <div class="card chat-card <?= $chat['last_sender'] == 'admin' ? 'unread' : '' ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h6 class="mb-0">Ticket #<?= htmlspecialchars($chat['ticket_id']) ?></h6>
                      <span class="badge bg-light text-dark border border-secondary">
                        <?= ucfirst(htmlspecialchars($chat['status'])) ?>
                      </span>
                    </div>
                    <div class="card-body">
                      <h6 class="card-title"><?= htmlspecialchars($chat['subject']) ?></h6>
                      <p class="card-text">
                        <?php if ($chat['last_message']): ?>
                          <strong><?= $chat['last_sender'] == 'tecnico' ? 'Tú' : 'Admin' ?>:</strong>
                          <?= htmlspecialchars($chat['last_message']) ?>
                        <?php else: ?>
                          <em>No hay mensajes aún</em>
                        <?php endif; ?>
                      </p>
                      <small class="text-muted">
                        <i class="far fa-clock me-1"></i>
                        <?= $chat['last_message_date'] ? date('d/m/Y H:i', strtotime($chat['last_message_date'])) : 'Sin actividad' ?>
                      </small>
                    </div>
                  </div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <h5 class="alert-heading">No hay conversaciones</h5>
            <p class="mb-0">
              <?php if ($filtro != 'todos'): ?>
                No tienes conversaciones en el periodo seleccionado.
              <?php else: ?>
                No tienes conversaciones activas.
              <?php endif; ?>
            </p>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
