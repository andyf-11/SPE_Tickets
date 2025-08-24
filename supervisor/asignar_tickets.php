<?php
session_start();
require_once("dbconnection.php");
include("checklogin.php");
check_login("supervisor");

$ticketId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ticketId <= 0) {
  header("Location: s_dashboard.php");
  exit;
}

// Obtener datos del ticket
$stmt = $pdo->prepare("SELECT * FROM ticket WHERE id = :id");
$stmt->execute([':id' => $ticketId]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
  header("Location: s_dashboard.php");
  exit;
}

$ticketCerrado = ($ticket['status'] === 'closed');

// Obtener técnicos
$stmt = $pdo->prepare("SELECT id, name FROM user WHERE role = 'tecnico'");
$stmt->execute();
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar asignación de ticket (con notificación al técnico)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$ticketCerrado) {
  $tecnico = $_POST['tecnico_id'] ?? null;
  $prioridad = $_POST['prioridad'] ?? null;
  $contexto = $_POST['contexto'] ?? null;

  if ($tecnico) {
    try {
      $pdo->beginTransaction();

      // Actualizar el ticket con técnico, prioridad y contexto
      $stmt = $pdo->prepare("UPDATE ticket 
                       SET assigned_to = :tecnico, status = 'en proceso', 
                           priority = :prioridad
                       WHERE id = :id");
      $stmt->execute([
        ':tecnico' => $tecnico,
        ':prioridad' => $prioridad,
        ':id' => $ticketId
      ]);

      // Crear mensaje de notificación
      $mensaje = "Se te ha asignado un nuevo ticket.";
      if (!empty($prioridad))
        $mensaje .= " Prioridad: $prioridad.";
      if (!empty($contexto))
        $mensaje .= " Contexto: $contexto.";

      // Insertar notificación al técnico
      $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) 
                             VALUES (:user_id, :message, 0, NOW())");
      $stmt->execute([
        ':user_id' => $tecnico,
        ':message' => $mensaje
      ]);

      $pdo->commit();

      $_SESSION['mensaje_exito'] = "Ticket asignado correctamente";
      header("Location: asignar_tickets.php?id=$ticketId");
      exit;

    } catch (PDOException $e) {
      $pdo->rollBack();
      $error = "Error al asignar el ticket: " . $e->getMessage();
    }
  } else {
    $error = "Debes seleccionar un técnico.";
  }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Asignar Ticket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../styles/supervisor/asignar-tickets.css" rel="stylesheet">
</head>

<body>
  <!-- Navbar superior -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
    <div class="container-fluid px-4">
      <button class="btn btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#leftbar" aria-controls="leftbar">
        <i class="fas fa-bars"></i>
      </button>
      <a class="navbar-brand d-flex align-items-center" href="#">
        <i class="fas fa-ticket-alt me-2"></i>
        <span>Sistema de Tickets</span>
      </a>
    </div>
  </nav>

  <!-- Contenido principal -->
  <div class="main-content">
    <div class="container-fluid px-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
          <i class="fas fa-ticket-alt me-2"></i>
          Asignar Ticket #<?= htmlspecialchars($ticketId) ?>
        </h1>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4">
          <i class="fas fa-exclamation-circle me-2"></i>
          <div><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['mensaje_exito'])): ?>
        <div id="mensajeExito" class="alert alert-success mb-4" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          <div><?= htmlspecialchars($_SESSION['mensaje_exito']) ?></div>
        </div>
        <script>
          setTimeout(() => {
            window.location.href = "manage-tickets.php";
          }, 2000);
        </script>
        <?php unset($_SESSION['mensaje_exito']); ?>
      <?php endif; ?>

      <div class="row">
        <div class="col-lg-8">
          <div class="card mb-4">
            <div class="card-body p-4">
              <h5 class="card-title mb-4">
                <i class="fas fa-user-cog me-2"></i>Asignación del Ticket
              </h5>

              <form method="post" class="needs-validation" novalidate>
                <!-- Select de prioridad (ahora obligatorio) -->
                <div class="mb-4">
                  <label for="prioridad" class="form-label">Prioridad del Ticket <span
                      class="text-danger">*</span></label>
                  <select class="form-select" id="prioridad" name="prioridad" required>
                    <option value="" selected disabled>-- Selecciona prioridad --</option>
                    <option value="Importante" <?= ($ticket['priority'] ?? '') === 'Importante' ? 'selected' : '' ?>>
                      Importante</option>
                    <option value="Urgente-(Problema Funcional)" <?= ($ticket['priority'] ?? '') === 'Urgente-(Problema Funcional)' ? 'selected' : '' ?>>Urgente (Problema Funcional)</option>
                    <option value="No-Urgente" <?= ($ticket['priority'] ?? '') === 'No-Urgente' ? 'selected' : '' ?>>No
                      Urgente</option>
                    <option value="Pregunta" <?= ($ticket['priority'] ?? '') === 'Pregunta' ? 'selected' : '' ?>>Pregunta
                    </option>
                  </select>
                  <div class="invalid-feedback">Por favor selecciona una prioridad.</div>
                </div>

                <!-- Contexto previo -->
                <div class="mb-4">
                  <label for="contexto" class="form-label">Contexto previo para el técnico</label>
                  <textarea class="form-control" id="contexto" name="contexto" rows="4"
                    placeholder="Escribe algún detalle que deba saber el técnico"></textarea>

                </div>

                <!-- Select de técnico -->
                <div class="mb-4">
                  <label for="tecnico" class="form-label">Técnico disponible <span class="text-danger">*</span></label>
                  <select class="form-select" id="tecnico" name="tecnico_id" required>
                    <option value="" disabled selected>-- Selecciona un técnico --</option>
                    <?php foreach ($tecnicos as $tecnico): ?>
                      <option value="<?= htmlspecialchars($tecnico['id']) ?>" <?= ($ticket['assigned_to'] ?? '') == $tecnico['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tecnico['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Por favor selecciona un técnico.</div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-check me-2"></i>Asignar Ticket
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Información del Ticket -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body p-4">
              <h5 class="card-title mb-4">
                <i class="fas fa-info-circle me-2"></i>Información del Ticket
              </h5>

              <div class="ticket-info-item">
                <strong>ID</strong>
                <span>#<?= htmlspecialchars($ticket['id']) ?></span>
              </div>

              <div class="ticket-info-item">
                <strong>Asunto</strong>
                <span><?= htmlspecialchars($ticket['subject']) ?></span>
              </div>

              <div class="ticket-info-item">
                <strong>Estado actual</strong>
                <span><?= $ticketCerrado ? 'Cerrado' : ($ticket['status'] === 'en proceso' ? 'En proceso' : 'Pendiente') ?></span>
              </div>

              <div class="ticket-info-item">
                <strong>Fecha de creación</strong>
                <span><?= date('d/m/Y H:i', strtotime($ticket['posting_date'])) ?></span>
              </div>

              <?php if (!empty($ticket['priority'])): ?>
                <div class="ticket-info-item">
                  <strong>Prioridad actual</strong>
                  <span class="badge 
                    <?= $ticket['priority'] === 'Urgente-(Problema Funcional)' ? 'bg-danger' : '' ?>
                    <?= $ticket['priority'] === 'Importante' ? 'bg-warning text-dark' : '' ?>
                    <?= $ticket['priority'] === 'No-Urgente' ? 'bg-info' : '' ?>
                    <?= $ticket['priority'] === 'Pregunta' ? 'bg-light text-dark' : '' ?>
                  ">
                    <?= htmlspecialchars($ticket['priority']) ?>
                  </span>
                </div>
              <?php endif; ?>

              <?php if (!empty($ticket['context'])): ?>
                <div class="ticket-info-item">
                  <strong>Contexto actual</strong>
                  <p class="mb-0"><?= nl2br(htmlspecialchars($ticket['context'])) ?></p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict';
      const form = document.querySelector('.needs-validation');
      if (form) {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        });
      }
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