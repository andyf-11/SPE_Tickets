<?php
session_start();
require("dbconnection.php");
require_once("checklogin.php");
check_login("admin");

function nombreMesEnEspanol($mesIngles)
{
  $traducciones = [
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre'
  ];
  return $traducciones[$mesIngles] ?? $mesIngles;
}

$mesActual = date('m');
$anioActual = date('Y');
$mesActualNombre = nombreMesEnEspanol(date('F')) . ' ' . date('Y');

function contarTicketsPorEstado($pdo, $estado, $mes, $anio)
{
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE status = :status AND MONTH(posting_date) = :mes AND YEAR(posting_date) = :anio");
  $stmt->execute([':status' => $estado, ':mes' => $mes, ':anio' => $anio]);
  return $stmt->fetchColumn();
}

$ticketsAbiertos = contarTicketsPorEstado($pdo, 'Abierto', $mesActual, $anioActual);
$ticketsEnProceso = contarTicketsPorEstado($pdo, 'En Proceso', $mesActual, $anioActual);
$ticketsCerrados = contarTicketsPorEstado($pdo, 'Cerrado', $mesActual, $anioActual);

$sqlTecnicos = "SELECT u.id, u.name, COUNT(t.ticket_id) AS tickets_resueltos
                FROM user u
                LEFT JOIN ticket t ON t.assigned_to = u.id
                    AND t.status = 'Cerrado'
                    AND MONTH(t.posting_date) = :mes
                    AND YEAR(t.posting_date) = :anio
                WHERE u.role = 'tecnico'
                GROUP BY u.id, u.name
                ORDER BY tickets_resueltos DESC";

$stmtTecnicos = $pdo->prepare($sqlTecnicos);
$stmtTecnicos->execute(['mes' => $mesActual, 'anio' => $anioActual]);
$tecnicos = $stmtTecnicos->fetchAll(PDO::FETCH_ASSOC);

$sqlSolicitudes = "SELECT COUNT(*) FROM application_approv WHERE status = 'pendiente'";
$stmtSolicitudes = $pdo->prepare($sqlSolicitudes);
$stmtSolicitudes->execute();
$solicitudesNuevas = $stmtSolicitudes->fetchColumn();

$sqlTecnicosSolicitudes = "
  SELECT DISTINCT u.name
  FROM application_approv aa
  INNER JOIN user u ON aa.tech_id = u.id
  WHERE aa.status = 'pendiente'
";
$stmtTecnicosSolicitudes = $pdo->prepare($sqlTecnicosSolicitudes);
$stmtTecnicosSolicitudes->execute();
$tecnicosSolicitudes = $stmtTecnicosSolicitudes->fetchAll(PDO::FETCH_ASSOC);

$totalDias = cal_days_in_month(CAL_GREGORIAN, $mesActual, $anioActual);
$dias = range(1, $totalDias);

// Visitas únicas
$visitasUnicas = array_fill(1, $totalDias, 0);
$sqlVisitasUnicas = "SELECT DAY(uc.logindatetime) AS dia, COUNT(DISTINCT uc.user_id) AS total
                     FROM usercheck uc
                     INNER JOIN user u ON uc.user_id = u.id
                     WHERE u.role = 'usuario' AND MONTH(uc.logindatetime) = :mes AND YEAR(uc.logindatetime) = :anio
                     GROUP BY dia";
$stmtVisitasUnicas = $pdo->prepare($sqlVisitasUnicas);
$stmtVisitasUnicas->execute(['mes' => $mesActual, 'anio' => $anioActual]);
foreach ($stmtVisitasUnicas->fetchAll(PDO::FETCH_ASSOC) as $row) {
  $visitasUnicas[intval($row['dia'])] = intval($row['total']);
}

// Inicios de sesión totales
$visitasTotales = array_fill(1, $totalDias, 0);
$sqlVisitasTotales = "SELECT DAY(logindatetime) as dia, COUNT(*) as total
                      FROM usercheck uc
                      INNER JOIN user u ON uc.user_id = u.id
                      WHERE u.role = 'usuario' AND MONTH(uc.logindatetime) = :mes AND YEAR(uc.logindatetime) = :anio
                      GROUP BY dia";
$stmtVisitasTotales = $pdo->prepare($sqlVisitasTotales);
$stmtVisitasTotales->execute(['mes' => $mesActual, 'anio' => $anioActual]);
foreach ($stmtVisitasTotales->fetchAll(PDO::FETCH_ASSOC) as $row) {
  $visitasTotales[intval($row['dia'])] = intval($row['total']);
}

$labelsDias = json_encode(array_map(fn($d) => "Día $d", $dias));
$valoresUnicas = json_encode(array_values($visitasUnicas));
$valoresTotales = json_encode(array_values($visitasTotales));

$ultimoDiaMes = date("t");
$hoy = date("j");
$esFinDeMes = ($hoy == $ultimoDiaMes);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Administrador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4e73df;
      --success-color: #1cc88a;
      --info-color: #36b9cc;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
      --secondary-color: #858796;
      --light-color: #f8f9fc;
    }

    body {
      background-color: #f8f9fc;
      font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      padding-top: 56px;
      /* Para compensar el navbar fijo */
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-container {
      display: flex;
      flex: 1;
    }

    /* Sidebar styles */
    .sidebar-wrapper {
      width: 280px;
      flex-shrink: 0;
      background: white;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      position: sticky;
      top: 56px;
      height: calc(100vh - 56px);
      overflow-y: auto;
      z-index: 100;
    }

    .sidebar-column {
      width: 280px;
      flex-shrink: 0;
    }

    .content-wrapper {
      flex-grow: 1;
      padding: 1.5rem;
      background-color: #f8f9fc;
    }

    /* Dashboard Cards */
    .card {
      border: none;
      border-radius: 0.35rem;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      margin-bottom: 1.5rem;
    }

    .card-header {
      background-color: #f8f9fc;
      border-bottom: 1px solid #e3e6f0;
      padding: 1rem 1.35rem;
      font-weight: 600;
    }

    .card-body {
      padding: 1.25rem;
    }

    .stat-card {
      color: white;
      border-left: 0.25rem solid;
    }

    .stat-card.open {
      background-color: var(--primary-color);
      border-left-color: var(--primary-color);
    }

    .stat-card.process {
      background-color: var(--warning-color);
      border-left-color: var(--warning-color);
    }

    .stat-card.closed {
      background-color: var(--success-color);
      border-left-color: var(--success-color);
    }

    .stat-card.pending {
      background-color: var(--danger-color);
      border-left-color: var(--danger-color);
    }

    .stat-card .stat-value {
      font-size: 1.5rem;
      font-weight: 700;
    }

    .stat-card .stat-label {
      font-size: 0.875rem;
      text-transform: uppercase;
      opacity: 0.9;
    }

    .stat-card .stat-icon {
      font-size: 2rem;
      opacity: 0.3;
      position: absolute;
      right: 1rem;
      top: 1rem;
    }

    .btn-report {
      background-color: var(--primary-color);
      color: white;
      font-weight: 600;
    }

    .btn-report:hover {
      background-color: #2e59d9;
      color: white;
    }

    .table-responsive {
      overflow-x: auto;
    }

    .chart-container {
      position: relative;
      height: 300px;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
      .sidebar-wrapper {
        position: fixed;
        left: -280px;
        transition: all 0.3s;
      }

      .sidebar-column {
        display: none;
      }

      .sidebar-wrapper.active {
        left: 0;
      }

      .sidebar-overlay {
        display: none;
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99;
      }

      .sidebar-overlay.active {
        display: block;
      }

      .content-wrapper {
        padding: 1rem;
      }

      .stat-card {
        margin-bottom: 1rem;
      }
    }

    /* Mobile toggle button */
    .sidebar-toggle {
      position: fixed;
      left: 10px;
      top: 70px;
      z-index: 1050;
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: none;
    }

    @media (max-width: 991.98px) {
      .sidebar-toggle {
        display: block;
      }
    }
  </style>
</head>

<body>
  <!-- Header -->
  <?php include('header.php'); ?>

  <!-- Botón para móviles -->
  <button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Overlay para móviles -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Contenedor principal -->
  <div class="main-container">
    <!-- Sidebar -->
    <div class="sidebar-wrapper" id="sidebarWrapper">
      <?php include('leftbar.php'); ?>
    </div>

    <!-- Contenido principal -->
    <div class="content-wrapper">
      <!-- Todo el contenido actual de tu dashboard -->
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Administrador</h1>
        <button class="btn btn-report d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#reportModal">
          <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
        </button>
      </div>

        <!-- Fila de Cards -->
        <div class="row">
          <!-- Tickets Abiertos -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card open h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="stat-label">Tickets Abiertos</div>
                    <div class="stat-value"><?php echo $ticketsAbiertos; ?></div>
                    <div class="text-xs">Mes Actual: <?php echo $mesActualNombre; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-folder-open stat-icon"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets en Proceso -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card process h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="stat-label">Tickets en Proceso</div>
                    <div class="stat-value"><?php echo $ticketsEnProceso; ?></div>
                    <div class="text-xs">Mes Actual: <?php echo $mesActualNombre; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-tasks stat-icon"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets Cerrados -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card closed h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="stat-label">Tickets Cerrados</div>
                    <div class="stat-value"><?php echo $ticketsCerrados; ?></div>
                    <div class="text-xs">Mes Actual: <?php echo $mesActualNombre; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-check-circle stat-icon"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Solicitudes Pendientes -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card pending h-100" data-bs-toggle="modal" data-bs-target="#pendingRequestsModal"
              style="cursor: pointer;">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="stat-label">Solicitudes Pendientes</div>
                    <div class="stat-value"><?php echo $solicitudesNuevas; ?></div>
                    <div class="text-xs">Haga clic para ver detalles</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-clock stat-icon"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Fila de Gráfico y Tabla -->
        <div class="row">
          <!-- Gráfico de Visitas -->
          <div class="col-lg-8 mb-4">
            <div class="card shadow">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Visitas del Sistema - <?php echo $mesActualNombre; ?></h6>
              </div>
              <div class="card-body">
                <div class="chart-container">
                  <canvas id="visitsChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla de Técnicos -->
          <div class="col-lg-4 mb-4">
            <div class="card shadow">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Tickets Resueltos por Técnico</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>Técnico</th>
                        <th>Resueltos</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($tecnicos as $tecnico): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($tecnico['name']); ?></td>
                          <td><?php echo $tecnico['tickets_resueltos']; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Solicitudes Pendientes -->
      <div class="modal fade" id="pendingRequestsModal" tabindex="-1" aria-labelledby="pendingRequestsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="pendingRequestsModalLabel">Solicitudes Pendientes de Aprobación</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <?php if ($solicitudesNuevas > 0): ?>
                <div class="list-group">
                  <?php foreach ($tecnicosSolicitudes as $tecnico): ?>
                    <a href="#" class="list-group-item list-group-item-action">
                      <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1"><?php echo htmlspecialchars($tecnico['name']); ?></h6>
                        <small class="text-muted">Pendiente</small>
                      </div>
                      <p class="mb-1">Solicitud de aprobación pendiente</p>
                      <small>Haga clic para revisar</small>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-info text-center">
                  <i class="fas fa-info-circle fa-2x mb-3"></i>
                  <h5>No hay solicitudes pendientes</h5>
                </div>
              <?php endif; ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Generar Reporte -->
      <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="reportModalLabel">Generar Reporte Mensual</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="reportForm">
                <div class="mb-3">
                  <label for="reportType" class="form-label">Tipo de Reporte</label>
                  <select class="form-select" id="reportType">
                    <option value="full">Reporte Completo</option>
                    <option value="tickets">Solo Tickets</option>
                    <option value="visits">Solo Visitas</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="reportFormat" class="form-label">Formato</label>
                  <select class="form-select" id="reportFormat">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                  </select>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="includeCharts" checked>
                  <label class="form-check-label" for="includeCharts">
                    Incluir gráficos
                  </label>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-primary">Generar Reporte</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Modales y scripts se mantienen igual -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Control del sidebar en móviles
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('sidebarWrapper');
      const overlay = document.getElementById('sidebarOverlay');
      const toggleBtn = document.getElementById('sidebarToggle');

      toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
      });

      overlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
      });

      // Cerrar sidebar al hacer clic en enlaces (solo móviles)
      if (window.innerWidth < 992) {
        document.querySelectorAll('.sidebar-wrapper .nav-link').forEach(link => {
          link.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
          });
        });
      }
    });

    // Tu código del gráfico se mantiene igual
  </script>

  <script>
    // Gráfico de visitas
    document.addEventListener('DOMContentLoaded', function () {
      const ctx = document.getElementById('visitsChart').getContext('2d');
      const visitsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo $labelsDias; ?>,
          datasets: [
            {
              label: 'Visitas Únicas',
              data: <?php echo $valoresUnicas; ?>,
              backgroundColor: 'rgba(78, 115, 223, 0.05)',
              borderColor: 'rgba(78, 115, 223, 1)',
              pointBackgroundColor: 'rgba(78, 115, 223, 1)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
              borderWidth: 2,
              tension: 0.3
            },
            {
              label: 'Inicios de Sesión Totales',
              data: <?php echo $valoresTotales; ?>,
              backgroundColor: 'rgba(28, 200, 138, 0.05)',
              borderColor: 'rgba(28, 200, 138, 1)',
              pointBackgroundColor: 'rgba(28, 200, 138, 1)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgba(28, 200, 138, 1)',
              borderWidth: 2,
              tension: 0.3
            }
          ]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
            },
            tooltip: {
              mode: 'index',
              intersect: false,
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });
    });
  </script>
</body>

</html>