<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("supervisor");

// Obtener filtro desde GET
$filtro = $_GET['filtro'] ?? 'todos';
$condicionFecha = '';

switch ($filtro) {
  case 'hoy':
    $condicionFecha = "AND DATE(posting_date) = CURDATE()";
    break;
  case '7dias':
    $condicionFecha = "AND posting_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    break;
  case '30dias':
    $condicionFecha = "AND posting_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    break;
  case 'todos':
  default:
    $condicionFecha = "";
    break;
}

// Consulta tickets abiertos
$stmt_abiertos = $pdo->query("SELECT * FROM ticket WHERE status = 'Abierto' $condicionFecha ORDER BY posting_date DESC");
$tickets_abiertos = $stmt_abiertos->fetchAll(PDO::FETCH_ASSOC);

// Consulta tickets en proceso con nombre de técnico
$condicionFechaProceso = str_replace('posting_date', 't.posting_date', $condicionFecha);
$stmt_proceso = $pdo->query(
  "SELECT t.*, u.name as tecnico_nombre 
    FROM ticket t 
    JOIN user u ON t.technician_id = u.id 
    WHERE t.status = 'En Proceso' $condicionFechaProceso 
    ORDER BY t.posting_date DESC"
);
$tickets_proceso = $stmt_proceso->fetchAll(PDO::FETCH_ASSOC);

// Consulta tickets cerrados
$stmt_cerrados = $pdo->query("SELECT * FROM ticket WHERE status = 'Cerrado' $condicionFecha ORDER BY posting_date DESC");
$tickets_cerrados = $stmt_cerrados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estado de Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="../styles/superv.css" rel="stylesheet">

    <style>
    body {
      font-family: 'Poppins', sans-serif;
      font-weight: 300;
    }

    @media (min-width: 768px) {
      #leftbar {
        position: fixed;
        top: 42px;
        left: 0;
        height: calc(100vh - 52px);
        z-index: 1030;
        font-weight: 400;
      }

      main {
        margin-left: 250px;
        /* Ancho del sidebar */
      }
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>
  <?php include("leftbar.php"); ?>

  <main class="main-content">
    <div class="container-fluid">
      <div class="header-section">
        <div>
          <h2 class="fw-bold mb-1"><i class="fa-solid fa-chart-pie me-2"></i>Estado de Tickets</h2>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="s_dashboard.php" class="text-decoration-none">Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Estado de Tickets</li>
            </ol>
          </nav>
        </div>
        <a href="gen_sreport.php?filtro=<?= urlencode($filtro) ?>" class="btn btn-danger">
          <i class="fas fa-file-pdf me-2"></i>Generar Reporte
        </a>
      </div>

      <!-- Filtros -->
      <div class="filter-card">
        <h5 class="fw-bold mb-3"><i class="fas fa-filter me-2"></i>Filtros</h5>
        <form method="GET" class="row g-3 align-items-center">
          <div class="col-md-4">
            <label for="filtro" class="form-label mb-1">Periodo:</label>
            <select name="filtro" id="filtro" class="form-select" onchange="this.form.submit()">
              <option value="todos" <?= $filtro == 'todos' ? 'selected' : '' ?>>Todos los tickets</option>
              <option value="30dias" <?= $filtro == '30dias' ? 'selected' : '' ?>>Últimos 30 días</option>
              <option value="7dias" <?= $filtro == '7dias' ? 'selected' : '' ?>>Últimos 7 días</option>
              <option value="hoy" <?= $filtro == 'hoy' ? 'selected' : '' ?>>Hoy</option>
            </select>
          </div>
        </form>
      </div>

      <!-- Tickets Abiertos -->
      <div class="card">
        <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
          <span><i class="fas fa-door-open me-2"></i>Tickets Abiertos</span>
          <span class="badge bg-white text-primary"><?= count($tickets_abiertos) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets_abiertos as $ticket): ?>
                <tr>
                  <td class="fw-bold">#<?= htmlspecialchars($ticket['ticket_id']) ?></td>
                  <td><?= htmlspecialchars($ticket['subject']) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($ticket['posting_date'])) ?></td>
                  <td>
                    <a href="manage-tickets.php" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($tickets_abiertos)): ?>
                <tr>
                  <td colspan="4" class="empty-state">
                    <i class="fas fa-door-open"></i>
                    <div>No hay tickets abiertos</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tickets En Proceso -->
      <div class="card">
        <div class="card-header card-header-warning d-flex justify-content-between align-items-center">
          <span><i class="fas fa-tools me-2"></i>Tickets En Proceso</span>
          <span class="badge bg-white text-warning"><?= count($tickets_proceso) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Técnico</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets_proceso as $ticket): ?>
                <tr>
                  <td class="fw-bold">#<?= htmlspecialchars($ticket['ticket_id']) ?></td>
                  <td><?= htmlspecialchars($ticket['subject']) ?></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div
                        class="me-2 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                        style="width: 24px; height: 24px; font-size: 0.7rem;">
                        <?= strtoupper(substr($ticket['tecnico_nombre'], 0, 1)) ?>
                      </div>
                      <?= htmlspecialchars($ticket['tecnico_nombre']) ?>
                    </div>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($ticket['posting_date'])) ?></td>
                  <td>
                    <a href="manage-tickets.php" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($tickets_proceso)): ?>
                <tr>
                  <td colspan="5" class="empty-state">
                    <i class="fas fa-tools"></i>
                    <div>No hay tickets en proceso</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tickets Cerrados -->
      <div class="card">
        <div class="card-header card-header-success d-flex justify-content-between align-items-center">
          <span><i class="fas fa-check-circle me-2"></i>Tickets Cerrados</span>
          <span class="badge bg-white text-success"><?= count($tickets_cerrados) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Asunto</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets_cerrados as $ticket): ?>
                <tr>
                  <td class="fw-bold">#<?= htmlspecialchars($ticket['ticket_id']) ?></td>
                  <td><?= htmlspecialchars($ticket['subject']) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($ticket['posting_date'])) ?></td>
                  <td>
                    <a href="manage-tickets.php" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($tickets_cerrados)): ?>
                <tr>
                  <td colspan="4" class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <div>No hay tickets cerrados</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>