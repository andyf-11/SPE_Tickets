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

// Obtener estado del ticket
$stmt = $pdo->prepare("SELECT status FROM ticket WHERE id = :id");
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

  if ($tecnico) {
    try {
      $pdo->beginTransaction();

      // Actualizar el ticket
      $stmt = $pdo->prepare("UPDATE ticket SET assigned_to = :tecnico, status = 'en proceso' WHERE id = :id");
      $stmt->execute([
        ':tecnico' => $tecnico,
        ':id' => $ticketId
      ]);

      // Insertar notificación al técnico
      $mensaje = "Se te ha asignado un nuevo ticket (ID interno: $ticketId).";
      $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:user_id, :message, 0, NOW())");
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
  <style>
    :root {
      --primary-color: #4361ee;
      --primary-hover: #3a56d4;
    }

    body {
      min-height: 100vh;
      background-color: #f8fafc;
    }

    .navbar-brand {
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .main-content {
      margin-left: 280px;
      padding: 2rem;
      margin-top: 60px;
    }

    @media (max-width: 991.98px) {
      .main-content {
        margin-left: 0;
      }
    }

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .form-select,
    .form-control {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
    }

    .form-select:focus,
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
    }

    .btn-primary {
      background-color: var(--primary-color);
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
    }

    .alert-success {
      background-color: #f0fdf4;
      border-color: #bbf7d0;
      color: #166534;
    }

    .alert-danger {
      background-color: #fef2f2;
      border-color: #fecaca;
      color: #991b1b;
    }

    .page-title {
      color: #1e293b;
      font-weight: 600;
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 0.75rem;
    }

    .page-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 60px;
      height: 4px;
      background-color: var(--primary-color);
      border-radius: 2px;
    }
  </style>
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
        <h1 class="page-title">Asignar Ticket #<?= htmlspecialchars($ticketId) ?></h1>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4">
          <i class="fas fa-exclamation-circle me-2"></i>
          <div><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['mensaje_exito'])): ?>
        <div id="mensajeExito" class="alert alert-success d-flex align-items-center mb-4" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          <div><?= htmlspecialchars($_SESSION['mensaje_exito']) ?></div>
        </div>
        <script>
          // Redirigir al dashboard después de 2 segundos
          setTimeout(() => {
            window.location.href = "manage-tickets.php";
          }, 2000);
        </script>
        <?php unset($_SESSION['mensaje_exito']); ?>
      <?php endif; ?>

      <div class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-body p-4">
              <h5 class="card-title mb-4 text-primary">
                <i class="fas fa-user-cog me-2"></i>Seleccionar Técnico
              </h5>

              <form method="post" class="needs-validation" novalidate>
                <div class="mb-4">
                  <label for="tecnico" class="form-label fw-semibold">Técnico disponible</label>
                  <select class="form-select" id="tecnico" name="tecnico_id" required>
                    <option value="" disabled selected>-- Selecciona un técnico --</option>
                    <?php foreach ($tecnicos as $tecnico): ?>
                      <option value="<?= htmlspecialchars($tecnico['id']) ?>">
                        <?= htmlspecialchars($tecnico['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Por favor selecciona un técnico.</div>
                </div>

                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary px-4 py-2">
                    <i class="fas fa-user-check me-2"></i>Asignar Ticket
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
          <div class="card">
            <div class="card-body p-4">
              <h5 class="card-title mb-4 text-primary">
                <i class="fas fa-info-circle me-2"></i>Información
              </h5>

              <div class="d-flex align-items-center mb-3">
                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                  <i class="fas fa-ticket-alt text-primary"></i>
                </div>
                <div>
                  <small class="text-muted">Ticket ID</small>
                  <div class="fw-semibold">#<?= htmlspecialchars($ticketId) ?></div>
                </div>
              </div>

              <div class="d-flex align-items-center mb-3">
                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                  <i class="fas fa-calendar-alt text-primary"></i>
                </div>
                <div>
                  <small class="text-muted">Fecha de asignación</small>
                  <div class="fw-semibold"><?= date('d/m/Y') ?></div>
                </div>
              </div>

              <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                  <i class="fas fa-exclamation-circle text-primary"></i>
                </div>
                <div>
                  <small class="text-muted">Estado actual</small>
                  <div class="fw-semibold"><?= $ticketCerrado ? 'Cerrado' : 'Pendiente' ?></div>
                </div>
              </div>
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