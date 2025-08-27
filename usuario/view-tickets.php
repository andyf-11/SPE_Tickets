<?php
session_start();
require($_SERVER['DOCUMENT_ROOT']."/SPE_Soporte_Tickets/dbconnection.php");
require($_SERVER['DOCUMENT_ROOT']."/SPE_Soporte_Tickets/usuario/checklogin.php");
require_once $_SERVER['DOCUMENT_ROOT']."/SPE_Soporte_Tickets/file-badge.php";
check_login("usuario");

$page = 'view-tickets';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Mis Tickets de Soporte</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- CSS Absoluto -->
  <link href="SPE_Soporte_Tickets/styles/tickets/usuario/view-tickets.css" rel="stylesheet">
  <link href="../styles/file-badge.css" rel="stylesheet">

  <style>
     body {
      font-family: 'Poppins', sans-serif;
      font-weight: 300;
      margin-left: 70px;
    }

    #leftbar {
      position: fixed;
      top: 41px;
      left: 0;
      width: 250px;
      height: calc(100vh - 41px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    main.main-content {
      margin-left: 250px;
      padding: 2rem;
      min-height: calc(100vh - 56px);
    }

    @media (max-width: 767px) {
      #leftbar {
        position: relative;
        top: 0;
        width: 100%;
        height: auto;
      }

      main.main-content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body class="bg-light">

  <?php include $_SERVER['DOCUMENT_ROOT']."/SPE_Soporte_Tickets/usuario/header.php"; ?>

  <div class="container-fluid" style="padding-top: 1.5rem;">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-2 p-0">
        <?php include $_SERVER['DOCUMENT_ROOT']."/SPE_Soporte_Tickets/usuario/leftbar.php"; ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-lg-10 py-4 px-4">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb py-2 px-3 bg-white rounded shadow-sm">
            <li class="breadcrumb-item"><a href="/SPE_Soporte_Tickets/usuario/dashboard.php"><i class="fas fa-home me-1"></i> Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-ticket-alt me-1"></i> Mis Tickets</li>
          </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="h2 mb-1"><i class="fas fa-ticket text-primary me-2"></i> Mis Tickets de Soporte</h1>
            <p class="text-muted mb-0">Gestiona y revisa el estado de todos tus tickets</p>
          </div>
          <a href="/SPE_Soporte_Tickets/usuario/create-ticket.php" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Nuevo Ticket
          </a>
        </div>

        <?php
        try {
          $stmt = $pdo->prepare("
          SELECT t.*, e.name AS edificio_nombre
          FROM ticket t
          LEFT JOIN edificios e ON t.edificio_id = e.id
          WHERE t.email_id = :email
          ORDER BY t.id DESC
        ");
          $stmt->execute([':email' => $_SESSION['login']]);
          $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if ($tickets) {
            echo '<div class="accordion" id="ticketAccordion">';
            foreach ($tickets as $index => $row) {
              $ticketId = htmlspecialchars($row['ticket_id']);
              $subject = htmlspecialchars($row['subject']);
              $postingDate = date('d/m/Y H:i', strtotime($row['posting_date']));
              $status = htmlspecialchars($row['status']);
              $ticketText = nl2br(htmlspecialchars($row['ticket']));
              $collapseId = "collapse$index";
              $headingId = "heading$index";

              $statusBadgeClass = 'bg-secondary';
              switch (strtolower(trim($status))) {
                case 'abierto':
                  $statusBadgeClass = 'bg-primary';
                  break;
                case 'en proceso':
                  $statusBadgeClass = 'bg-warning text-dark';
                  break;
                case 'cerrado':
                  $statusBadgeClass = 'bg-success';
                  break;
              }
              ?>
              <div class="accordion-item ticket-card mb-3">
                <h2 class="accordion-header" id="<?= $headingId ?>">
                  <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                    <div class="d-flex w-100 align-items-center ticket-header">
                      <div class="ticket-subject me-3 flex-grow-1">
                        <?= $subject ?>
                      </div>
                      <div class="d-flex align-items-center ticket-meta">
                        <span class="badge bg-dark me-2">#<?= $ticketId ?></span>
                        <span class="badge bg-info text-dark me-2">
                          <i class="fas fa-building me-1"></i>
                          <?= htmlspecialchars($row['edificio_nombre'] ?? 'Sin ubicación') ?>
                        </span>
                        <span class="badge status-badge <?= $statusBadgeClass ?> me-2">
                          <i class="fas fa-circle me-1 small" style="font-size: 0.5rem; vertical-align: middle;"></i>
                          <?= $status ?>
                        </span>
                        <small class="text-muted"><i class="far fa-clock me-1"></i> <?= $postingDate ?></small>
                      </div>
                    </div>
                  </button>
                </h2>
                <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>"
                  data-bs-parent="#ticketAccordion">
                  <div class="accordion-body pt-3">
                    <div class="d-flex mb-3">
                      <img src="/SPE_Soporte_Tickets/assets/img/user.png" alt="Usuario" class="user-avatar rounded-circle me-3 shadow-sm">
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <h6 class="mb-0 fw-bold text-dark"><?= $_SESSION['user_name'] ?? 'Usuario' ?></h6>
                          <small class="text-muted"><i class="far fa-calendar me-1"></i> <?= $postingDate ?></small>
                        </div>
                        <div class="ticket-content">
                          <?= $ticketText ?>
                        </div>
                        <?php if (!empty($row['archivo'])): ?>
                          <div class="mt-3">
                            <?php mostrarArchivoBadge($row['archivo'], $row['ticket_id']); ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  
                  <?php if (!empty($row['tech_remark'])): ?>
                    <div class="response-card">
                      <div class="d-flex">
                        <img src="/SPE_Soporte_Tickets/assets/img/Logo-Gobierno_small.png" alt="Técnico" class="user-avatar rounded-circle me-3 shadow-sm">
                        <div class="flex-grow-1">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="response-header mb-0">
                              <i class="fas fa-tools me-1 text-primary"></i> Respuesta del técnico
                            </h6>
                            <small class="text-muted"><i class="far fa-calendar me-1"></i> <?= date('d/m/Y H:i', strtotime($row['tech_remark_date'])) ?></small>
                          </div>
                          <div class="response-text">
                            <?= nl2br(htmlspecialchars($row['tech_remark'])) ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($row['admin_remark'])): ?>
                    <div class="response-card">
                      <div class="d-flex">
                        <img src="/SPE_Soporte_Tickets/assets/img/Logo-Gobierno_small.png" alt="Admin" class="user-avatar rounded-circle me-3 shadow-sm">
                        <div class="flex-grow-1">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="response-header mb-0">
                              <i class="fas fa-user-shield me-1 text-primary"></i> Respuesta administrativa
                            </h6>
                            <small class="text-muted"><i class="far fa-calendar me-1"></i> <?= date('d/m/Y H:i', strtotime($row['admin_remark_date'])) ?></small>
                          </div>
                          <div class="response-text">
                            <?= nl2br(htmlspecialchars($row['admin_remark'])) ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php
            }
            echo '</div>';
          } else {
            echo '<div class="empty-state">
                  <i class="fas fa-inbox text-muted"></i>
                  <h3 class="text-muted mb-3">No tienes tickets registrados</h3>
                  <p class="text-muted mb-4">Cuando crees un ticket, aparecerá listado aquí</p>
                  <a href="/SPE_Soporte_Tickets/usuario/create-ticket.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i> Crear primer ticket
                  </a>
                </div>';
          }
        } catch (PDOException $e) {
          echo '<div class="alert alert-danger d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                <div>
                  <h5 class="alert-heading mb-1">Error al obtener los tickets</h5>
                  <p class="mb-0">' . htmlspecialchars($e->getMessage()) . '</p>
                </div>
              </div>';
        }
        ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="/SPE_Soporte_Tickets/usuario/chat-server/notifications.js"></script>

</body>

</html>