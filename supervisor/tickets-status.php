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
        $condicionFecha = '';
        break;
}

// Consulta tickets abiertos
$sql_abiertos = "
    SELECT * 
    FROM ticket 
    WHERE TRIM(status) = 'Abierto' 
    $condicionFecha 
    ORDER BY posting_date DESC
";
$stmt_abiertos = $pdo->query($sql_abiertos);
$tickets_abiertos = $stmt_abiertos->fetchAll(PDO::FETCH_ASSOC);

// Consulta tickets en proceso con nombre de técnico
$condicionFechaProceso = str_replace('posting_date', 't.posting_date', $condicionFecha);
$sql_proceso = "
    SELECT t.*, u.name as tecnico_nombre 
    FROM ticket t 
    LEFT JOIN user u ON t.technician_id = u.id 
    WHERE TRIM(t.status) = 'En Proceso' 
    $condicionFechaProceso 
    ORDER BY t.posting_date DESC
";
$stmt_proceso = $pdo->query($sql_proceso);
$tickets_proceso = $stmt_proceso->fetchAll(PDO::FETCH_ASSOC);

// Consulta tickets cerrados
$sql_cerrados = "
    SELECT * 
    FROM ticket 
    WHERE TRIM(status) = 'Cerrado' 
    $condicionFecha 
    ORDER BY posting_date DESC
";
$stmt_cerrados = $pdo->query($sql_cerrados);
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
  
  <style>
    :root {
      --sidebar-width: 250px;
      --primary-color: #4361ee;
      --warning-color: #f8961e;
      --success-color: #4cc9f0;
      --light-bg: #f8f9fa;
      --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      --card-hover-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    body {
      font-family: 'Poppins', sans-serif;
      font-weight: 300;
      background-color: var(--light-bg);
      color: #333;
    }

    .header-section {
      background: #fff;
      padding: 1.5rem;
      border-radius: 0.75rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.5rem;
      border-left: 4px solid var(--primary-color);
    }

    .filter-card {
      background: #fff;
      padding: 1.5rem;
      border-radius: 0.75rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.5rem;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .card {
      border: none;
      border-radius: 0.75rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.5rem;
      overflow: hidden;
    }

    .card-header {
      padding: 1rem 1.5rem;
      font-weight: 500;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .card-header-primary {
      background-color: rgba(67, 97, 238, 0.1);
      color: var(--primary-color);
    }

    .card-header-warning {
      background-color: rgba(248, 150, 30, 0.1);
      color: var(--warning-color);
    }

    .card-header-success {
      background-color: rgba(76, 201, 240, 0.1);
      color: var(--success-color);
    }

    .badge-count {
      font-size: 0.8rem;
      font-weight: 500;
      padding: 0.35rem 0.65rem;
      border-radius: 50px;
    }

    .table {
      margin-bottom: 0;
    }

    .table th {
      font-weight: 500;
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #6c757d;
      background-color: #f8f9fa;
    }

    .table td {
      vertical-align: middle;
      padding: 1rem;
    }

    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 2rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-sm {
      padding: 0.35rem 0.75rem;
      font-size: 0.825rem;
    }

    .btn-danger {
      background-color: #f72585;
      border-color: #f72585;
    }

    .btn-danger:hover {
      background-color: #d91a6d;
      border-color: #d91a6d;
    }

    .user-avatar {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 500;
      background-color: var(--primary-color);
      color: white;
    }

    /* Layout styles */
    @media (min-width: 768px) {
      body > .page-wrapper {
        display: flex;
        min-height: 100vh;
        margin-top: 56px;
      }

      #leftbar {
        position: fixed;
        top: 56px;
        left: 0;
        width: var(--sidebar-width);
        height: calc(100vh - 56px);
        z-index: 1030;
        font-weight: 300;
        overflow-y: auto;
      }

      main.main-content {
        margin-left: var(--sidebar-width);
        flex-grow: 1;
        padding: 2rem;
      }
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: var(--header-height);
      z-index: 1040;
      background-color: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    /* Mejoras para mobile */
    @media (max-width: 767.98px) {
      main.main-content {
        padding: 1.25rem;
      }
      
      .header-section,
      .filter-card {
        padding: 1.25rem;
      }
      
      .card-header {
        padding: 0.75rem 1rem;
      }
      
      .table td {
        padding: 0.75rem;
      }
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>
  <?php include("leftbar.php"); ?>

  <main class="main-content mt-5">
    <div class="container-fluid">
      <div class="header-section">
        <div>
          <h2 class="fw-bold mb-1"><i class="fa-solid fa-chart-pie me-2"></i>Estado de Tickets</h2>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="s_dashboard.php" class="text-decoration-none">Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Estado de Tickets</li>
            </ol>
          </nav>
        </div>
        <a href="gen_sreport.php?filtro=<?= urlencode($filtro) ?>" class="btn btn-danger rounded-pill shadow-sm">
          <i class="fas fa-file-pdf me-2"></i>Generar Reporte
        </a>
      </div>

      <!-- Filtros -->
      <div class="filter-card">
        <h5 class="fw-bold mb-3"><i class="fas fa-filter me-2 text-primary"></i>Filtros</h5>
        <form method="GET" class="row g-3 align-items-center">
          <div class="col-md-4 col-lg-3">
            <label for="filtro" class="form-label mb-1">Periodo:</label>
            <select name="filtro" id="filtro" class="form-select shadow-sm" onchange="this.form.submit()">
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
          <span class="badge bg-white text-primary badge-count"><?= count($tickets_abiertos) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table">
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
                    <a href="manage-tickets.php" class="btn btn-sm btn-outline-primary rounded-circle">
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
          <span class="badge bg-white text-warning badge-count"><?= count($tickets_proceso) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table">
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
                      <span class="user-avatar me-2">
                        <?= strtoupper(substr($ticket['tecnico_nombre'], 0, 1)) ?>
                      </span>
                      <?= htmlspecialchars($ticket['tecnico_nombre']) ?>
                    </div>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($ticket['posting_date'])) ?></td>
                  <td>
                    <a href="manage-tickets.php" class="btn btn-sm btn-outline-primary rounded-circle">
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
          <span class="badge bg-white text-success badge-count"><?= count($tickets_cerrados) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table">
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
                    <a href="manage-tickets.php" class="btn btn-sm btn-outline-primary rounded-circle">
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