<?php
session_start();
require("checklogin.php");
require_once("dbconnection.php");
check_login("tecnico");

$user_id = $_SESSION['user_id'] ?? 0; // Id del técnico

// Tickets asignados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE assigned_to = ?");
$stmt->execute([$user_id]);
$tickets_abiertos = $stmt->fetchColumn();

// Tickets en proceso
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE assigned_to = ? AND status = 'En Proceso'");
$stmt->execute([$user_id]);
$tickets_proceso = $stmt->fetchColumn();

// Tickets cerrados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE assigned_to = ? AND status = 'Cerrado'");
$stmt->execute([$user_id]);
$tickets_cerrados = $stmt->fetchColumn();

// Tickets asignados nuevos
$stmt = $pdo->prepare("SELECT id, subject, posting_date FROM ticket WHERE assigned_to = ? AND status = 'En proceso' ORDER BY posting_date DESC LIMIT 10");
$stmt->execute([$user_id]);
$tickets_asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tickets cerrados hoy
$stmt = $pdo->prepare("SELECT id, subject, fecha_cierre FROM ticket WHERE assigned_to = ? AND status = 'Cerrado' AND DATE(fecha_cierre) = CURDATE() ORDER BY fecha_cierre DESC");
$stmt->execute([$user_id]);
$tickets_cerrados_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Técnico</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="../styles/dashboard/t_dashboard.css" rel="stylesheet">
</head>

<body>

  <?php include('header.php'); ?>
  <?php include('leftbar.php'); ?>

  <main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
      <h2 class="mb-0 fw-bold">Panel Técnico</h2>
      <div class="d-flex align-items-center">
        <small class="text-muted me-2"><?= date('d/m/Y') ?></small>
        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
          <i class="fas fa-sync-alt"></i>
        </button>
      </div>
    </div>

    <!-- Estadísticas de tickets -->
    <div class="row g-4 mb-4">
      <div class="col-12 col-md-4">
        <div class="card card-stat bg-primary text-white h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3">
              <i class="fas fa-exclamation-circle fa-2x"></i>
            </div>
            <div>
              <h6 class="card-subtitle mb-1">Tickets Asignados</h6>
              <h3 class="mb-0"><?= $tickets_abiertos ?? 0 ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card card-stat bg-warning text-dark h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3">
              <i class="fas fa-spinner fa-2x"></i>
            </div>
            <div>
              <h6 class="card-subtitle mb-1">Tickets en Proceso</h6>
              <h3 class="mb-0"><?= $tickets_proceso ?? 0 ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card card-stat bg-success text-white h-100">
          <div class="card-body d-flex align-items-center">
            <div class="me-3">
              <i class="fas fa-check-circle fa-2x"></i>
            </div>
            <div>
              <h6 class="card-subtitle mb-1">Tickets Cerrados</h6>
              <h3 class="mb-0"><?= $tickets_cerrados ?? 0 ?></h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de tickets nuevos asignados -->
    <div class="card card-stat mb-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Tickets Asignados Nuevos</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($tickets_asignados)): ?>
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">No hay tickets nuevos asignados</td>
                </tr>
              <?php else: ?>
                <?php foreach ($tickets_asignados as $ticket): ?>
                  <tr>
                    <td><?= $ticket['id'] ?></td>
                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($ticket['posting_date'])) ?></td>
                    <td>
                      <a href="manage-tickets.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-reply"></i> Responder
                      </a>
                      <a href="chat-tech-admin.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-comments"></i> Aprobación
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tabla de tickets cerrados hoy -->
    <div class="card card-stat">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Tickets Cerrados Hoy</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Hora de Cierre</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($tickets_cerrados_hoy)): ?>
                <tr>
                  <td colspan="3" class="text-center py-4 text-muted">No hay tickets cerrados hoy</td>
                </tr>
              <?php else: ?>
                <?php foreach ($tickets_cerrados_hoy as $ticket): ?>
                  <tr>
                    <td><?= $ticket['id'] ?></td>
                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                    <td><?= date('H:i', strtotime($ticket['fecha_cierre'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="../chat-server/notifications.js"></script>

</body>

</html>