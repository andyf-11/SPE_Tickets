<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
require_once '../assets/data/notifications_helper.php';
require_once '../file-badge.php';
check_login("admin");

// Obtener filtro desde GET
$filtro = $_GET['filtro'] ?? 'todos';

// Guardar respuesta del admin sin sobrescribir
if (isset($_POST['update'])) {
    $adminremark = trim($_POST['aremark']);
    $fid = intval($_POST['frm_id']);

    if (empty($adminremark)) {
        $error = "Debes escribir una respuesta antes de enviarla.";
    } else {
        $stmt = $pdo->prepare("SELECT admin_remark, email_id FROM ticket WHERE id = :id");
        $stmt->execute([':id' => $fid]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            $oldRemark = $ticket['admin_remark'];
            $newRemark = $oldRemark . "\n[" . date("Y-m-d H:i") . "] Admin: " . $adminremark;

            $stmt = $pdo->prepare("UPDATE ticket SET admin_remark = :remark, status = 'Cerrado' WHERE id = :id");
            $stmt->execute([':remark' => $newRemark, ':id' => $fid]);

            $stmtUser = $pdo->prepare("SELECT id, name FROM user WHERE email = :email LIMIT 1");
            $stmtUser->execute([':email' => $ticket['email_id']]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                notificarRespuestaTicket($fid, $user['id']);
            }

            $_SESSION['ticket_updated'] = true;
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        } else {
            $error = "No se encontró el ticket.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin | Gestión de Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../assets/css/style-bootstrap5.css" rel="stylesheet" />
  <link href="../styles/tickets/manage-tickets(admin).css" rel="stylesheet">
  <link href="../styles/file-badge.css" rel=stylesheet>
  <style>
    #leftbar {
      position: fixed;
      top: 56px;
      left: 0;
      width: 250px;
      height: calc(100vh - 56px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    #main-content {
      margin-left: 250px;
      padding-top: 70px;
      min-height: 100vh;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <?php if (isset($_SESSION['ticket_updated'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
      <div id="ticketToast" class="toast align-items-center text-white bg-success border-0 show" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            ✅ Ticket actualizado correctamente.
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Cerrar"></button>
        </div>
      </div>
    </div>
    <script>
      setTimeout(() => {
        const toastEl = document.getElementById('ticketToast');
        if (toastEl) {
          const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
          toast.hide();
        }
      }, 3000);
    </script>
    <?php unset($_SESSION['ticket_updated']); ?>
  <?php endif; ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 col-lg-2 p-0 bg-light">
        <?php include("leftbar.php"); ?>
      </div>

      <div class="col px-4 py-4 mt-5" style="padding-top: 70px; min-height: 100vh; margin-left: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="mb-0 fw-bold mt-4">Gestión de Tickets</h2>
            <p class="text-muted mb-0">Administración y seguimiento de tickets del sistema</p>
          </div>
        </div>

        <!-- Filtros -->
        <div class="filter-container">
          <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
              <h5 class="mb-0">Filtrar tickets</h5>
            </div>
            <div class="col-md-6">
              <form method="GET" class="d-flex align-items-center">
                <label for="filtro" class="me-2 mb-0">Edificio:</label>
                <select name="filtro" id="filtro" class="form-select" onchange="this.form.submit()">
                  <option value="todos" <?= $filtro == 'todos' ? 'selected' : '' ?>>Todos los edificios</option>
                  <option value="Santa Esmeralda" <?= $filtro == 'Santa Esmeralda' ? 'selected' : '' ?>>Santa Esmeralda
                  </option>
                  <option value="Palmira" <?= $filtro == 'Palmira' ? 'selected' : '' ?>>Palmira</option>
                </select>
              </form>
            </div>
          </div>
        </div>

        <div class="accordion" id="ticketsAccordion">
          <?php
          try {
              if ($filtro !== 'todos') {
                  $stmt = $pdo->prepare("
                      SELECT t.*, e.name AS edificio_nombre, u.name AS user_name
                      FROM ticket t
                      LEFT JOIN edificios e ON t.edificio_id = e.id
                      LEFT JOIN user u ON t.email_id = u.email
                      WHERE e.name = :nombreEdificio
                      ORDER BY t.id DESC
                  ");
                  $stmt->execute(['nombreEdificio' => $filtro]);
              } else {
                  $stmt = $pdo->query("
                      SELECT t.*, e.name AS edificio_nombre, u.name AS user_name
                      FROM ticket t
                      LEFT JOIN edificios e ON t.edificio_id = e.id
                      LEFT JOIN user u ON t.email_id = u.email
                      ORDER BY t.id DESC
                  ");
              }

              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $id = $row['id'];
                  $estado = strtolower($row['status']);
                  $badgeClass = 'status-open';
                  $estadoTexto = 'Abierto';
                  if ($estado === 'en proceso') {
                      $badgeClass = 'status-progress';
                      $estadoTexto = 'En Proceso';
                  } elseif ($estado === 'cerrado') {
                      $badgeClass = 'status-closed';
                      $estadoTexto = 'Cerrado';
                  }

                  $edificio = htmlspecialchars($row['edificio_nombre'] ?? 'Sin edificio');
                  $fecha = date('d/m/Y H:i', strtotime($row['posting_date']));
                  $priority = !empty($row['priority']) ? htmlspecialchars($row['priority']) : 'Pendiente de asignación';
                  $userName = htmlspecialchars($row['user_name'] ?? 'Usuario');

                  $priorityBadgeClass = 'secondary';
                  switch ($priority) {
                      case 'Urgente-(Problema Funcional)': $priorityBadgeClass = 'danger'; break;
                      case 'Importante': $priorityBadgeClass = 'warning text-dark'; break;
                      case 'No-Urgente': $priorityBadgeClass = 'info'; break;
                      case 'Pregunta': $priorityBadgeClass = 'light text-dark'; break;
                  }
                  ?>
                  <div class="ticket-card">
                      <div class="ticket-header accordion-header <?= $estado === 'cerrado' ? 'bg-light' : '' ?>" id="heading<?= $id ?>">
                          <button class="accordion-button <?= $estado === 'cerrado' ? 'bg-light' : '' ?> collapsed d-flex justify-content-between align-items-center"
                              type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $id ?>" aria-expanded="false" aria-controls="collapse<?= $id ?>">
                              <div class="d-flex flex-column flex-md-row align-items-md-center w-100">
                                  <div class="me-md-4 mb-2 mb-md-0">
                                      <span class="badge building-badge me-2"><?= $edificio ?></span>
                                      <span class="badge <?= $badgeClass ?>"><?= $estadoTexto ?></span>
                                      <span class="badge bg-<?= $priorityBadgeClass ?> ticket-status-badge"><i class="fas fa-flag me-1"></i><?= $priority ?></span>
                                  </div>
                                  <div class="flex-grow-1">
                                      <h5 class="mb-1"><?= htmlspecialchars($row['subject']) ?></h5>
                                      <div class="d-flex flex-wrap text-muted small">
                                          <span class="me-3"><i class="far fa-id-card me-1"></i>#<?= htmlspecialchars($row['ticket_id']) ?></span>
                                          <span><i class="far fa-clock me-1"></i> <?= $fecha ?></span>
                                      </div>
                                  </div>
                              </div>
                          </button>
                      </div>

                      <div id="collapse<?= $id ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $id ?>" data-bs-parent="#ticketsAccordion">
                          <div class="ticket-body">
                              <div class="message-box">
                                  <div class="d-flex justify-content-between align-items-center mb-2">
                                      <h6 class="mb-0 fw-bold text-primary">
                                          <i class="fas fa-user-circle me-2"></i>Mensaje de <?= $userName ?>
                                      </h6>
                                  </div>
                                  <div class="text-muted"><?= nl2br(htmlspecialchars($row['ticket'])) ?></div>
                              </div>

                              <?php if (!empty($row['archivo'])): ?>
                                  <div class="mt-3">
                                      <?php mostrarArchivoBadge($row['archivo'], $row['ticket_id']); ?>
                                  </div>
                              <?php endif; ?>

                              <?php if (!empty($row['tech_remark'])): ?>
                                  <div class="message-box" style="border-left-color: var(--warning-color);">
                                      <div class="d-flex justify-content-between align-items-center mb-2">
                                          <h6 class="mb-0 fw-bold text-warning">
                                              <i class="fas fa-user-cog me-2"></i>Respuesta del técnico
                                          </h6>
                                      </div>
                                      <div class="text-muted"><?= nl2br(htmlspecialchars($row['tech_remark'])) ?></div>
                                  </div>
                              <?php endif; ?>

                              <?php if (!empty($row['admin_remark'])): ?>
                                  <div class="message-box" style="border-left-color: var(--primary-color);">
                                      <div class="d-flex justify-content-between align-items-center mb-2">
                                          <h6 class="mb-0 fw-bold text-primary">
                                              <i class="fas fa-user-shield me-2"></i>Respuesta del administrador
                                          </h6>
                                      </div>
                                      <div class="text-muted"><?= nl2br(htmlspecialchars($row['admin_remark'])) ?></div>
                                  </div>
                              <?php endif; ?>

                              <form method="post" class="needs-validation mt-4" novalidate>
                                  <div class="mb-3">
                                      <label for="aremark<?= $id ?>" class="form-label fw-bold">
                                          <i class="fas fa-reply me-1"></i>Agregar respuesta
                                      </label>
                                      <textarea class="form-control" id="aremark<?= $id ?>" name="aremark" rows="4"
                                          placeholder="Escribe tu respuesta aquí..." required></textarea>
                                      <div class="invalid-feedback">Por favor ingrese un comentario.</div>
                                  </div>
                                  <input type="hidden" name="frm_id" value="<?= $id ?>" />
                                  <div class="d-flex justify-content-end">
                                      <button type="submit" name="update" class="btn btn-primary px-4">
                                          <i class="fas fa-paper-plane me-2"></i>Enviar respuesta
                                      </button>
                                  </div>
                              </form>
                          </div>
                      </div>
                  </div>
              <?php } ?>
          <?php
          } catch (PDOException $e) {
              echo '<div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2 fs-4"></i> Error al obtener tickets: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
          ?>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>
</body>

</html>
