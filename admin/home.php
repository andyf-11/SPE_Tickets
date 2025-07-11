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
  <link href="../assets/css/style-bootstrap5.css" rel="stylesheet" />
  <link href="../styles/admin.css" rel="stylesheet">
  <style>
  /* Ajuste para que el contenido principal no se esconda detrás del leftbar */
  @media (min-width: 768px) {
    #leftbar {
      top: 60px;
      width: 250px;
      position: fixed;
      height: calc(100vh - 70px);
    }

    #main-content {
      margin-left: 250px;
    }
  }

  /* Estilo opcional para que la transición del sidebar sea suave */
  #leftbar {
    transition: all 0.3s ease;
  }
</style>

  
</head>

<body>
  <?php include('header.php'); ?>

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 col-lg-2 p-0 bg-light">
        <?php include('leftbar.php'); ?>
      </div>

      <div id="main-content" class="px-4 py-4 mt-5">
        <!-- Encabezado del Dashboard -->
        <div class="dashboard-header d-flex justify-content-between align-items-center">
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
                <strong>Atención:</strong> Hoy es el último día del mes. Recuerda <a href="gen_report.php" class="alert-link fw-bold">generar el reporte mensual</a>.
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
                <i class="far fa-calendar me-1"></i> <?= $mesActualNombre; ?>
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
            <div class="requests-card" data-bs-toggle="modal" data-bs-target="#solicitudesModal">
              <i class="fa-solid fa-file-signature card-icon"></i>
              <h3 class="fw-bold mb-2"><?= intval($solicitudesNuevas) ?></h3>
              <h5 class="mb-0">Solicitudes Pendientes</h5>
              <small class="opacity-75 mt-2">Click para ver detalles</small>
            </div>
          </div>
        </div>

        <!-- Gráfico de Visitas -->
        <div class="chart-container">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="section-title mb-0">Visitas de Usuarios - <?= $mesActualNombre; ?></h4>
            <button id="toggleChart" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-exchange-alt me-1"></i> Ver: Totales
            </button>
          </div>
          <canvas id="visitasChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Modal Solicitudes -->
    <div class="modal fade" id="solicitudesModal" tabindex="-1" aria-labelledby="solicitudesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="solicitudesModalLabel">
              <i class="fas fa-file-signature me-2"></i>Solicitudes Pendientes
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <?php if (count($tecnicosSolicitudes) > 0): ?>
              <div class="list-group">
                <?php foreach ($tecnicosSolicitudes as $tec): ?>
                  <div class="list-group-item d-flex align-items-center">
                    <div class="avatar-sm me-3">
                      <i class="fas fa-user"></i>
                    </div>
                    <?= htmlspecialchars($tec['name']) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No hay solicitudes pendientes</h5>
                <p class="text-muted">Todas las solicitudes han sido procesadas</p>
              </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      const ctx = document.getElementById('visitasChart').getContext('2d');
      const labels = <?= $labelsDias ?>;
      const dataUnicas = <?= $valoresUnicas ?>;
      const dataTotales = <?= $valoresTotales ?>;

      let showingUnicas = true;

      const visitasChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Visitas Únicas',
            data: dataUnicas,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#4e73df',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 20
              }
            },
            tooltip: {
              backgroundColor: '#2a3547',
              titleColor: '#fff',
              bodyColor: '#eee',
              padding: 12,
              usePointStyle: true,
              callbacks: {
                label: function(context) {
                  return `${context.dataset.label}: ${context.raw}`;
                }
              }
            }
          },
          scales: {
            x: { 
              grid: { display: false },
              title: { 
                display: true, 
                text: 'Día del Mes',
                color: '#6c757d'
              } 
            },
            y: { 
              beginAtZero: true, 
              title: { 
                display: true, 
                text: 'Cantidad de Visitas',
                color: '#6c757d'
              },
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          }
        }
      });

      document.getElementById('toggleChart').addEventListener('click', () => {
        showingUnicas = !showingUnicas;
        visitasChart.data.datasets[0].data = showingUnicas ? dataUnicas : dataTotales;
        visitasChart.data.datasets[0].label = showingUnicas ? 'Visitas Únicas' : 'Inicios de Sesión Totales';
        document.getElementById('toggleChart').innerHTML = showingUnicas 
          ? '<i class="fas fa-exchange-alt me-1"></i> Ver: Totales' 
          : '<i class="fas fa-exchange-alt me-1"></i> Ver: Únicas';
        visitasChart.update();
      });
    </script>
</body>

</html>