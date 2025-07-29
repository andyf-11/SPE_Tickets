<?php
session_start();
require_once("checklogin.php");
check_login("admin");
require("dbconnection.php");

$page = 'chat-list-admin';

// Consulta para obtener chats
$sql = "
SELECT
  a.id AS apply_id,
  a.ticket_id,
  a.status,
  t.subject,
  u.name AS tecnico_nombre,
  MAX(m.date) AS last_message_date,
  (SELECT message FROM messg_tech_admin WHERE apply_id = a.id ORDER BY date DESC LIMIT 1) AS last_message,
  (SELECT emisor FROM messg_tech_admin WHERE apply_id = a.id ORDER BY date DESC LIMIT 1) AS last_sender
FROM
  application_approv a
JOIN ticket t ON t.id = a.ticket_id
JOIN user u ON u.id = a.tech_id
LEFT JOIN messg_tech_admin m ON m.apply_id = a.id
GROUP BY
  a.id, a.ticket_id, a.status, t.subject, u.name
ORDER BY
  last_message_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Solicitudes de Aprobación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/tickets/chat-list-admin.css" rel="stylesheet" /> <!-- Ajusta ruta si es necesario -->
  <style>
  /* Sidebar fijo con altura completa menos header */
  #leftbar {
    position: fixed;
    top: 56px;
    /* altura del header fijo */
    left: 0;
    width: 250px;
    height: calc(100vh - 56px);
    background-color: #fff;
    border-right: 1px solid #dee2e6;
    z-index: 1030;
    overflow-y: auto;
    font-weight: 400;
  }

  /* Para el contenido principal, margen izquierdo igual al sidebar para evitar superposición */
  #main-content {
    margin-left: 250px;
    padding-top: 70px;
    /* espacio para header */
    min-height: 100vh;
  }
</style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Botón toggle para móviles -->
      <button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#leftbar" aria-controls="leftbar">
        <i class="fas fa-bars"></i>
      </button>

      <!-- Sidebar -->
      <nav class="col-lg-2 p-0 bg-light offcanvas-md offcanvas-start" id="leftbar">
        <?php include("leftbar.php"); ?>
      </nav>

      <!-- Contenido principal -->
      <main class="col-lg-10 px-2 py-4 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4 mt-md-0">
          <h2 class="h4 mb-0"><i class="fas fa-comments me-2 text-primary"></i>Solicitudes de Aprobación</h2>
          <a href="home.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
          </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            Solicitud aprobada correctamente.
          </div>
        <?php endif; ?>

        <?php if (!$chats): ?>
          <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            No hay solicitudes de aprobación registradas.
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($chats as $chat):
              $statusClass = 'status-' . $chat['status'];
              $badgeClass = '';
              $icon = '';

              switch ($chat['status']) {
                case 'pendiente':
                  $badgeClass = 'bg-warning text-dark';
                  $icon = 'fas fa-clock';
                  break;
                case 'aprobado':
                  $badgeClass = 'bg-success';
                  $icon = 'fas fa-check';
                  break;
                case 'rechazado':
                  $badgeClass = 'bg-danger';
                  $icon = 'fas fa-times';
                  break;
                case 'resuelto':
                  $badgeClass = 'bg-info text-dark';
                  $icon = 'fas fa-check-double';
                  break;
                default:
                  $badgeClass = 'bg-light text-dark';
              }

              $senderLabel = ($chat['last_sender'] === 'tecnico') ? 'Técnico: ' : (($chat['last_sender'] === 'admin') ? 'Admin: ' : '');
              $fecha = $chat['last_message_date'] ? date('d/m/Y H:i', strtotime($chat['last_message_date'])) : 'Sin mensajes';
              ?>
              <div class="col-12">
                <a href="chat-tech-admin.php?apply_id=<?= $chat['apply_id'] ?>" class="text-decoration-none">
                  <div class="card chat-card <?= $statusClass ?> mb-0 h-100">
                    <div class="card-body">
                      <div class="d-flex align-items-start">
                        <div class="avatar">
                          <i class="fas fa-user-cog text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                          <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1">
                              Ticket #<?= $chat['ticket_id'] ?> - <?= htmlspecialchars($chat['subject']) ?>
                            </h5>
                            <small class="text-muted"><?= $fecha ?></small>
                          </div>

                          <p class="card-text text-muted last-message mb-2">
                            <?= $senderLabel . htmlspecialchars($chat['last_message'] ?? 'Sin mensajes aún') ?>
                          </p>

                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <i class="fas fa-user me-1"></i> <?= htmlspecialchars($chat['tecnico_nombre']) ?>
                            </small>
                            <span class="badge rounded-pill <?= $badgeClass ?> badge-status">
                              <i class="<?= $icon ?> me-1"></i>
                              <?= ucfirst($chat['status']) ?>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>