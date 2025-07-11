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
  <style>
    body {
      min-height: 100vh;
    }

    .sidebar {
      height: 100vh;
      position: fixed;
      top: 56px;
      left: 0;
      width: 250px;
      background-color: #f8f9fa;
      padding-top: 1rem;
    }

    .main-content {
      margin-left: 250px;
      padding: 1.5rem;
      margin-top: 56px;
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <button class="btn btn-outline-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar"
        aria-controls="leftbar">
        <i class="fas fa-bars"></i>
      </button>
      <a class="navbar-brand ms-3" href="#">Sistema de Tickets</a>
    </div>
  </nav>

  <div class="sidebar d-none d-md-block border-end" id="leftbar">
    <div class="text-center mb-3">
      <img src="../assets/img/Logo-Gobierno_small.png" alt="Perfil" class="img-fluid"
        style="width: 80px; height: 80px;">
      <div class="fw-semibold">Bienvenid@</div>
      <div class="text-primary fw-bold">Supervisor</div>
    </div>
    <ul class="nav flex-column px-3">
      <li class="nav-item mb-1"><a href="home.php" class="nav-link text-dark"><i
            class="bi bi-house-door-fill me-2"></i>Dashboard</a></li>
      <li class="nav-item mb-1"><a href="manage-tickets.php" class="nav-link text-dark"><i
            class="fas fa-ticket-alt me-2"></i>Gestionar Ticket</a></li>
      <li class="nav-item mb-1"><a href="asignar_tickets.php" class="nav-link active fw-bold text-primary"><i
            class="fas fa-users me-2"></i>Asignar Tickets</a></li>
    </ul>
  </div>

  <div class="offcanvas offcanvas-start d-md-none bg-light" tabindex="-1" id="leftbar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menú Supervisor</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column">
        <li class="nav-item mb-1"><a href="home.php" class="nav-link text-dark"><i
              class="bi bi-house-door-fill me-2"></i>Dashboard</a></li>
        <li class="nav-item mb-1"><a href="manage-tickets.php" class="nav-link text-dark"><i
              class="fas fa-ticket-alt me-2"></i>Gestionar Ticket</a></li>
        <li class="nav-item mb-1"><a href="asignar_tickets.php" class="nav-link active fw-bold text-primary"><i
              class="fas fa-users me-2"></i>Asignar Tickets</a></li>
      </ul>
    </div>
  </div>

  <div class="main-content">
    <div class="container-fluid">
      <h3 class="mb-4">Asignar Ticket</h3>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['mensaje_exito'])): ?>
        <div id="mensajeExito" class="alert alert-success" role="alert">
          ✅ <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
        </div>
        <script>
          // Redirigir al dashboard después de 2 segundos
          setTimeout(() => {
            window.location.href = "manage-tickets.php";
          }, 2000);
        </script>
        <?php unset($_SESSION['mensaje_exito']); // Limpiar el mensaje para no mostrarlo otra vez ?>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="post" class="needs-validation" novalidate>
            <div class="mb-3">
              <label for="tecnico" class="form-label">Seleccionar Técnico</label>
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

            <button type="submit" class="btn btn-primary">Asignar Ticket</button>
          </form>
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
</body>

</html>