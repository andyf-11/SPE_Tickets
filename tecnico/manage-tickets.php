<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
require_once '../assets/data/notifications_helper.php';
require_once '../assets/config/mailer_config.php';
require_once '../file-badge.php';
check_login("tecnico");

$tecnico_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['frm_id']) && isset($_POST['aremark'])) {
  $ticketId = intval($_POST['frm_id']);
  $remark = trim($_POST['aremark']);

  if ($remark) {
    try {
      $pdo->beginTransaction();

      $stmt = $pdo->prepare("
        UPDATE ticket 
        SET admin_remark = :remark, 
            status = 'Cerrado', 
            admin_remark_date = NOW(),
            fecha_cierre = NOW()
        WHERE id = :id 
          AND assigned_to = :tecnico_id 
          AND status = 'En Proceso'
      ");
      $stmt->execute([
        ':remark' => $remark,
        ':id' => $ticketId,
        ':tecnico_id' => $tecnico_id
      ]);

      if ($stmt->rowCount() > 0) {
        // Obtener el ID del usuario dueño del ticket
        $stmt = $pdo->prepare("SELECT u.id 
                               FROM user u 
                               JOIN ticket t ON t.email_id = u.email 
                               WHERE t.id = :ticket_id LIMIT 1");
        $stmt->execute([':ticket_id' => $ticketId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_usuario = $usuario['id'] ?? null;

        // 🔔 Notificar al usuario
        if ($id_usuario) {
          error_log("Intentando notificar al usuario $id_usuario para ticket $ticketId");
          $result = notificarRespuestaTicket($ticketId, $id_usuario);
          error_log("Resultado de notificación: " . ($result ? "Éxito ✅" : "Fallo ❌"));
        }


        $pdo->commit();
        $mensaje_exito = "Respuesta enviada. El ticket se marcó como CERRADO.";
      } else {
        $pdo->rollBack();
        $error = "No puedes cerrar un ticket que no te pertenece o ya está cerrado.";
      }
    } catch (PDOException $e) {
      $pdo->rollBack();
      $error = "Error al actualizar el ticket: " . $e->getMessage();
    }
  } else {
    $error = "Debes escribir un comentario.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Técnicos | Tickets Asignados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="../styles/tickets/manage-tickets.css" rel="stylesheet">
  <link href="../styles/file-badge.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>
  <?php $page = 'manage-tickets'; ?>
  <?php include("leftbar.php"); ?>

  <main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="t_dashboard.php"><i class="fas fa-home me-1"></i> Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-ticket-alt me-1"></i> Tickets
            Asignados</li>
        </ol>
      </nav>
      <h3 class="mb-0"><i class="fas fa-ticket me-2"></i>Tickets Asignados</h3>
    </div>

    <?php if (isset($mensaje_exito)): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($mensaje_exito) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php elseif (isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php
    try {
      $stmt = $pdo->prepare("
        SELECT t.*, e.name AS edificio_nombre, u.name AS usuario_nombre
        FROM ticket t
        LEFT JOIN edificios e ON t.edificio_id = e.id
        LEFT JOIN user u ON t.email_id = u.email
        WHERE t.assigned_to = :tecnico_id 
          AND t.status IN ('En Proceso', 'Cerrado') 
        ORDER BY t.id DESC
      ");
      $stmt->execute([':tecnico_id' => $tecnico_id]);

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ticketId = htmlspecialchars($row['id']);
        $subject = htmlspecialchars($row['subject']);
        $postingDate = htmlspecialchars($row['posting_date']);
        $status = htmlspecialchars($row['status']);
        $ticketText = htmlspecialchars($row['ticket']);
        $adminRemark = htmlspecialchars($row['admin_remark']);
        $adminRemarkDate = htmlspecialchars($row['admin_remark_date']);
        $usuarioNombre = htmlspecialchars($row['usuario_nombre'] ?? 'Usuario');
        $id = (int) $row['id'];

        $priority = !empty($row['priority']) ? htmlspecialchars($row['priority']) : 'Pendiente de asignación';

        $priorityBadgeClass = 'bg-secondary';
        switch ($priority) {
          case 'Urgente-(Problema Funcional)':
            $priorityBadgeClass = 'bg-danger';
            break;
          case 'Importante':
            $priorityBadgeClass = 'bg-warning text-dark';
            break;
          case 'No-Urgente':
            $priorityBadgeClass = 'bg-info';
            break;
          case 'Pregunta':
            $priorityBadgeClass = 'bg-light text-dark';
            break;
        }

        $stmtNotif = $pdo->prepare("SELECT message 
                                    FROM notifications 
                                    WHERE user_id = :uid 
                                      AND message LIKE :msg
                                    ORDER BY created_at DESC LIMIT 1");
        $stmtNotif->execute([
          ':uid' => $tecnico_id,
          ':msg' => "%ticket (ID interno: {$id})%"
        ]);
        $notif = $stmtNotif->fetch(PDO::FETCH_ASSOC);

        $stmtChat = $pdo->prepare("SELECT status_chat FROM chat_user_tech WHERE ticket_id = ?");
        $stmtChat->execute([$id]);
        $chatInfo = $stmtChat->fetch();
        $btnText = ($chatInfo && $chatInfo['status_chat'] === 'abierto') ? 'Continuar Consulta' : 'Iniciar Consulta';
        ?>

        <div class="card card-ticket">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <h5 class="mb-2"><?= $subject ?></h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <span class="text-muted small"><i class="far fa-id-card me-1"></i>Ticket #<?= $ticketId ?></span>
                  <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= $postingDate ?></span>
                  <span class="badge badge-status <?= ($status === 'Cerrado') ? 'badge-cerrado' : 'badge-en-proceso'; ?>">
                    <i class="fas fa-<?= ($status === 'Cerrado') ? 'lock' : 'spinner'; ?> me-1"></i><?= $status ?>
                  </span>
                  <span class="badge <?= $priorityBadgeClass ?>">
                    <i class="fas fa-flag me-1"></i><?= $priority ?>
                  </span>
                  <?php if (!empty($row['edificio_nombre'])): ?>
                    <span class="badge badge-edificio badge-status">
                      <i class="fas fa-building me-1"></i><?= htmlspecialchars($row['edificio_nombre']) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                  data-bs-target="#ticketDetails<?= $id ?>" aria-expanded="false" aria-controls="ticketDetails<?= $id ?>">
                  <i class="fas fa-<?= ($status === 'Cerrado') ? 'eye' : 'edit'; ?> me-1"></i>
                  <?= ($status === 'Cerrado') ? 'Ver Detalles' : 'Responder' ?>
                </button>

                <?php if ($status === 'Cerrado'): ?>
                  <button class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="fa-solid fa-comment-slash me-1"></i> Chat Cerrado
                  </button>
                <?php else: ?>
                  <a href="open-chat-user.php?ticket_id=<?= $id ?>" class="btn btn-sm btn-success">
                    <i class="fa-solid fa-comments me-1"></i> <?= $btnText ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="collapse" id="ticketDetails<?= $id ?>">
            <div class="card-body">
              <?php if ($notif && strpos($notif['message'], 'Contexto:') !== false): ?>
                <div class="alert alert-info">
                  <i class="fas fa-user-tie me-2"></i><strong>Contexto del Supervisor:</strong><br>
                  <?= nl2br(htmlspecialchars($notif['message'])) ?>
                </div>
              <?php endif; ?>

              <div class="d-flex align-items-start mb-4">
                <img src="../assets/img/user.png" alt="Usuario" class="user-avatar me-3 rounded-circle" />
                <div>
                  <div class="ticket-text mb-3"><strong>Mensaje de
                      <?= $usuarioNombre ?>:</strong><br><?= nl2br($ticketText) ?></div>

                  <?php if (!empty($row['archivo'])): ?>
                    <div class="mt-3">
                      <?php mostrarArchivoBadge($row['archivo'], $row['id']); ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <hr class="my-4">

              <?php if ($status === 'Cerrado'): ?>
                <div class="mb-4">
                  <h6 class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-check-circle text-success"></i> Respuesta enviada
                  </h6>
                  <div class="alert alert-light border">
                    <div class="d-flex align-items-start">
                      <img src="../assets/img/Logo-Gobierno_small.png" alt="Técnico" class="user-avatar me-3 rounded-circle"
                        style="width: 35px; height: 35px;" />
                      <div>
                        <?= nl2br($adminRemark) ?>
                        <div class="text-muted small mt-2">
                          <i class="far fa-clock me-1"></i> Enviado el: <?= $adminRemarkDate ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                  <h6 class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-reply text-primary"></i> Responder al ticket
                  </h6>
                  <div class="mb-3">
                    <textarea class="form-control" id="aremark<?= $id ?>" name="aremark" rows="5"
                      placeholder="Escribe tu respuesta aquí..." required><?= htmlspecialchars($adminRemark) ?></textarea>
                    <div class="invalid-feedback">Por favor ingrese un comentario.</div>
                  </div>
                  <input type="hidden" name="frm_id" value="<?= $id ?>" />
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse"
                      data-bs-target="#ticketDetails<?= $id ?>">Cancelar</button>
                    <button type="submit" name="update" class="btn btn-primary">
                      <i class="fas fa-paper-plane me-1"></i> Enviar y Cerrar Ticket
                    </button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php
      }
    } catch (PDOException $e) {
      echo '<div class="alert alert-danger alert-dismissible fade show">
              <i class="fas fa-exclamation-triangle me-2"></i>Error al obtener tickets: ' . htmlspecialchars($e->getMessage()) . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
    ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>

  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="../chat-server/notifications.js"></script>

</body>

</html>