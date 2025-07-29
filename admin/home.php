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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SPE Dashboard Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/dashboard/home.css" rel="stylesheet" />
  <style>
    /* Sidebar fijo con altura completa menos header */
    #leftbar {
      position: fixed;
      top: 56px; /* altura del header fijo */
      left: 0;
      width: 250px;
      height: calc(100vh - 56px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    /* Para el contenido principal, margen izquierdo igual al sidebar para evitar superposición */
    #main-content {
      margin-left: 250px;
      padding-top: 70px; /* espacio para header */
      min-height: 100vh;
    }
  </style>
</head>

<body>
  <?php include('header.php'); ?>

  <!-- Sidebar -->
  <div id="leftbar">
    <?php include('leftbar.php'); ?>
  </div>

  <!-- Contenido principal -->
  <main id="main-content" class="px-5 py-7">
    <!-- Encabezado del Dashboard -->
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-0 fw-bold">Panel de Administración</h1>
        <p class="mb-0 text-muted">Resumen de actividades del sistema</p>
      </div>
      <div>
        <a href="gen_report.php" class="btn btn-primary btn-report">
          <i class="fas fa-file-pdf me-2"></i> Generar Reporte
        </a>
      </div>
    </div>

    <?php if ($esFinDeMes): ?>
      <div class="alert alert-warning-custom alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-circle me-3 fa-lg text-warning"></i>
          <div>
            <strong>Atención:</strong> Hoy es el último día del mes. Recuerda <a href="gen_report.php"
              class="alert-link fw-bold">generar el reporte mensual</a>.
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <!-- Cards de Estadísticas -->
    <div class="row g-4 mb-4">
      <!-- Tickets Abiertos -->
      <div class="col-md-4">
        <div class="stat-card card card-primary">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h5 class="card-title">Tickets Abiertos</h5>
                <h2 class="card-value text-primary"><?= $ticketsAbiertos ?></h2>
              </div>
              <i class="fa-solid fa-folder-open card-icon text-primary"></i>
            </div>
          </div>
          <div class="card-footer">
            <i class="far fa-calendar me-1"></i> <?= $mesActualNombre; ?>
          </div>
        </div>
      </div>

      <!-- Tickets En Proceso -->
      <div class="col-md-4">
        <div class="stat-card card card-warning">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h5 class="card-title">Tickets En Proceso</h5>
                <h2 class="card-value text-warning"><?= $ticketsEnProceso ?></h2>
              </div>
              <i class="fa-solid fa-spinner card-icon text-warning"></i>
            </div>
          </div>
          <div class="card-footer">
            <i class="far fa-calendar me-1"></i> <?= $mesActualNombre; ?>
          </div>
        </div>
      </div>

      <!-- Tickets Cerrados -->
      <div class="col-md-4">
        <div class="stat-card card card-success">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h5 class="card-title">Tickets Cerrados</h5>
                <h2 class="card-value text-success"><?= $ticketsCerrados ?></h2>
              </div>
              <i class="fa-solid fa-circle-check card-icon text-success"></i>
            </div>
          </div>
          <div class="card-footer">
            <i class="far fa-calendar me-1"></i> <?= $mesActualNombre ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Segunda Fila: Tabla y Card -->
    <div class="row g-4">
      <!-- Tabla de Técnicos -->
      <div class="col-lg-8">
        <div class="table-container">
          <h4 class="section-title">Tickets Resueltos por Técnicos</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr class="table-light">
                  <th class="fw-semibold">Técnico</th>
                  <th class="fw-semibold text-end">Tickets Resueltos</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($tecnicos) > 0): ?>
                  <?php foreach ($tecnicos as $tecnico): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm me-3">
                            <i class="fas fa-user"></i>
                          </div>
                          <?= htmlspecialchars($tecnico['name']) ?>
                        </div>
                      </td>
                      <td class="text-end fw-bold"><?= intval($tecnico['tickets_resueltos']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="2" class="text-center py-5">
                      <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <h5 class="mt-2 mb-1">No hay datos disponibles</h5>
                        <p class="small mb-0">No hay técnicos registrados o tickets resueltos este mes.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Card de Solicitudes -->
      <div class="col-lg-4">
        <div class="requests-card" data-bs-toggle="modal" data-bs-target="#solicitudesModal" style="cursor: pointer;">
          <i class="fa-solid fa-file-signature card-icon"></i>
          <h3 class="fw-bold mb-2"><?= intval($solicitudesNuevas) ?></h3>
          <h5 class="mb-0">Solicitudes Pendientes</h5>
          <small class="opacity-75 mt-2">Click para ver detalles</small>
        </div>
      </div>
    </div>

    <!-- Gráfico de Visitas -->
    <div class="chart-container mt-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title mb-0">Visitas de Usuarios - <?= $mesActualNombre; ?></h4>
        <button id="toggleChart" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-exchange-alt me-1"></i> Ver: Totales
        </button>
      </div>
      <canvas id="visitasChart" height="300"></canvas>
    </div>

    <!-- Modal Solicitudes -->
    <div class="modal fade" id="solicitudesModal" tabindex="-1" aria-labelledby="solicitudesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="solicitudesModalLabel">Solicitudes Pendientes - Técnicos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <?php if (count($tecnicosSolicitudes) > 0): ?>
              <ul class="list-group">
                <?php foreach ($tecnicosSolicitudes as $tecnicoPendiente): ?>
                  <li class="list-group-item">
                    <i class="fas fa-user-clock me-2 text-warning"></i>
                    <?= htmlspecialchars($tecnicoPendiente['name']) ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="empty-state py-5">
                <i class="fas fa-info-circle"></i>
                <h5 class="mt-2 mb-1">No hay solicitudes pendientes</h5>
                <p class="small mb-0">No existen solicitudes en estado pendiente actualmente.</p>
              </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
    const ctx = document.getElementById('visitasChart').getContext('2d');
    const labelsDias = <?= $labelsDias ?>;
    const dataUnicas = <?= $valoresUnicas ?>;
    const dataTotales = <?= $valoresTotales ?>;

    let mostrarUnicas = true;

    const data = {
      labels: labelsDias,
      datasets: [{
        label: 'Visitas Únicas',
        data: dataUnicas,
        borderColor: '#4e73df',
        backgroundColor: 'rgba(78, 115, 223, 0.1)',
        tension: 0.3,
        fill: true,
        pointRadius: 3,
      }]
    };

    const config = {
      type: 'line',
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        },
        plugins: {
          legend: {
            labels: {
              font: { size: 14 }
            }
          }
        }
      }
    };

    const visitasChart = new Chart(ctx, config);

    document.getElementById('toggleChart').addEventListener('click', function () {
      mostrarUnicas = !mostrarUnicas;
      visitasChart.data.datasets[0].label = mostrarUnicas ? 'Visitas Únicas' : 'Visitas Totales';
      visitasChart.data.datasets[0].data = mostrarUnicas ? dataUnicas : dataTotales;
      visitasChart.update();
      this.textContent = mostrarUnicas ? 'Ver: Totales' : 'Ver: Únicas';
      this.prepend(document.createElement('i')).className = 'fas fa-exchange-alt me-1';
    });
  </script>
</body>

</html>
