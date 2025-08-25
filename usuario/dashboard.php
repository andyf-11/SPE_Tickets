<?php
session_start();
require_once("../dbconnection.php");
require("checklogin.php");
check_login("usuario");

$user_email = $_SESSION['login'] ?? '';

// Total tickets del usuario
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE email_id = ?");
$stmt->execute([$user_email]);
$total_tickets = $stmt->fetchColumn();

if (empty($user_email)) {
  echo "<div class='alert alert-danger'>Error: no hay email de usuario en sesión.</div>";
  exit;
}

// Tickets de hoy
$stmt = $pdo->prepare("SELECT id, subject, posting_date FROM ticket WHERE email_id = ? AND DATE(posting_date) = CURDATE() ORDER BY posting_date DESC LIMIT 5");
$stmt->execute([$user_email]);
$tickets_today = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tickets abiertos
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE email_id = ? AND status != 'Cerrado'");
$stmt->execute([$user_email]);
$open_tickets = $stmt->fetchColumn();

$page = 'dashboard'; // para el leftbar
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #6240d4;
      --secondary-color: #6c757d;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      overflow-x: hidden;
      margin: 0;
    }

    #clock {
      color: var(--primary-color);
      font-weight: 600;
    }

    /* Sidebar fijo debajo del header */
    #leftbar {
      position: fixed;
      top: 43px;
      /* Altura del header */
      left: 0;
      width: 250px;
      height: calc(100vh - 43px);
      background-color: #fff;
      overflow-y: auto;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
    }

    /* Contenido principal */
    .main-content {
      margin-left: 250px;
      padding: 2rem;
      padding-top: 2.5rem;
      min-height: 100vh;
      background-color: #f8f9fa;
    }

    .card-stat {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-stat:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .ticket-item {
      transition: background-color 0.2s ease;
      border-left: 3px solid transparent;
    }

    .ticket-item:hover {
      background-color: #f8f9fa;
      border-left-color: var(--primary-color);
    }

    @media (max-width: 767px) {
      #leftbar {
        position: relative;
        top: 0;
        width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 1px solid #dee2e6;
      }

      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

  <?php include('header.php'); ?>
  <?php include('leftbar.php'); ?>

  <main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
      <h2 class="mb-0 fw-bold">Panel de Usuario</h2>
      <div class="d-flex align-items-center">
        <small class="text-muted me-2"><?= date('d/m/Y') ?></small>
        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
          <i class="fas fa-sync-alt"></i>
        </button>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <!-- Reloj -->
      <div class="col-12 col-md-4">
        <div class="card card-stat h-100 text-center">
          <div class="card-body">
            <div id="clock" class="display-4 fw-bold mb-2"></div>
            <small class="text-muted">Hora actual del servidor</small>
          </div>
        </div>
      </div>

      <!-- Total Tickets -->
      <div class="col-12 col-md-4">
        <div class="card card-stat bg-primary text-white h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3">
              <i class="fas fa-ticket-alt fa-2x"></i>
            </div>
            <div>
              <h6 class="card-subtitle mb-1">Total de Tickets</h6>
              <h3 class="mb-0"><?= $total_tickets ?></h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Tickets Abiertos -->
      <div class="col-12 col-md-4">
        <div class="card card-stat bg-warning text-dark h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3">
              <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div>
              <h6 class="card-subtitle mb-1">Tickets Abiertos</h6>
              <h3 class="mb-0"><?= $open_tickets ?></h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tickets de hoy -->
    <div class="card card-stat">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Tickets recientes</h5>
        <a href="view-tickets.php" class="btn btn-sm btn-outline-primary">
          Ver todos <i class="fas fa-arrow-right ms-1"></i>
        </a>
      </div>
      <div class="card-body p-0">
        <?php if (count($tickets_today) === 0): ?>
          <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">No hay tickets creados hoy</p>
          </div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($tickets_today as $ticket): ?>
              <li class="list-group-item ticket-item d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><?= htmlspecialchars($ticket['subject']) ?></h6>
                  <small class="text-muted">ID: <?= $ticket['id'] ?></small>
                </div>
                <div class="text-end">
                  <small class="d-block text-muted"><?= date('H:i', strtotime($ticket['posting_date'])) ?></small>
                  <span class="badge bg-primary">Nuevo</span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function updateClock() {
      const clock = document.getElementById('clock');
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const seconds = now.getSeconds().toString().padStart(2, '0');
      clock.textContent = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>
  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="chat-server/notification.js"></script>

</body>

</html>