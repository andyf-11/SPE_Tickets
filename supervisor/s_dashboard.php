<?php
session_start();
require_once("checklogin.php");
check_login("supervisor");
require("dbconnection.php");

// Obtener días del mes actual para el gráfico
$totalDays = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
$monthData = array_fill(1, $totalDays, 0);

// Consulta optimizada: visitas del mes actual agrupadas por día
$stmt = $pdo->prepare("
    SELECT DAY(logindatetime) as dia, COUNT(*) as visitas
    FROM usercheck
    WHERE YEAR(logindatetime) = YEAR(CURDATE()) AND MONTH(logindatetime) = MONTH(CURDATE())
    GROUP BY dia
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Asignar resultados al array por día
foreach ($rows as $row) {
  $day = (int) $row['dia'];
  if (isset($monthData[$day])) {
    $monthData[$day] = (int) $row['visitas'];
  }
}

$categories = [];
$dataPoints = [];
for ($i = 1; $i <= $totalDays; $i++) {
  $categories[] = "Día $i";
  $dataPoints[] = $monthData[$i];
}

// Consultas para las cards

// Visitantes generales
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usercheck");
$stmt->execute();
$visitantes_general = $stmt->fetchColumn();

// Visitantes hoy
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usercheck WHERE DATE(logindatetime) = CURDATE()");
$stmt->execute();
$visitantes_hoy = $stmt->fetchColumn();

// Usuarios registrados total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user");
$stmt->execute();
$usuarios_total = $stmt->fetchColumn();

// Usuarios registrados hoy
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE DATE(posting_date) = CURDATE()");
$stmt->execute();
$usuarios_hoy = $stmt->fetchColumn();

// Solicitudes generales (tickets)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket");
$stmt->execute();
$solicitudes_general = $stmt->fetchColumn();

// Solicitudes nuevas (status = 'Abierto')
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE status = 'Abierto'");
$stmt->execute();
$solicitudes_nuevas = $stmt->fetchColumn();

// Solicitudes en progreso (status = 'En proceso')
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE status = 'En proceso'");
$stmt->execute();
$solicitudes_enprogreso = $stmt->fetchColumn();

// Tickets totales
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket");
$stmt->execute();
$tickets_todos = $stmt->fetchColumn();

// Tickets pendientes hoy (status 'Abierto' y fecha actual)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE status = 'Abierto' AND DATE(posting_date) = CURDATE()");
$stmt->execute();
$tickets_pendientes_hoy = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Supervisor</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://code.highcharts.com/highcharts.js"></script>
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

<body class="bg-light">
  <?php include("header.php"); ?>
  <?php include("leftbar.php"); ?>

  <main class="flex-grow-1 px-4 py-3 mt-header">
    <!-- Encabezado -->
    <div class="page-header d-flex justify-content-between align-items-center pb-3 mb-4">
      <div>
        <h1 class="h2 mb-0"><i class="fas fa-tachometer-alt text-primary me-2"></i> Panel de Supervisor</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>
      <div class="text-end">
        <small class="text-muted">Actualizado: <?= date('d/m/Y H:i') ?></small>
      </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row g-4 mb-4">
      <!-- Visitantes -->
      <div class="col-md-6 col-lg-3">
        <div class="dashboard-card text-white bg-green p-4">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <div class="card-title">Visitantes</div>
              <div class="card-subtitle">Total del sistema</div>
            </div>
            <i class="fas fa-users card-icon"></i>
          </div>
          <div class="card-value mb-1"><?= number_format($visitantes_general) ?></div>
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <div class="card-subtitle">Hoy</div>
              <div class="h5 mb-0"><?= number_format($visitantes_hoy) ?></div>
            </div>
            <div class="text-end">
              <span
                class="badge bg-white text-green"><?= round(($visitantes_hoy / $visitantes_general) * 100, 1) ?>%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Usuarios registrados -->
      <div class="col-md-6 col-lg-3">
        <div class="dashboard-card text-white bg-blue p-4">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <div class="card-title">Usuarios Registrados</div>
              <div class="card-subtitle">Total en el sistema</div>
            </div>
            <i class="fas fa-user-plus card-icon"></i>
          </div>
          <div class="card-value mb-1"><?= number_format($usuarios_total) ?></div>
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <div class="card-subtitle">Nuevos hoy</div>
              <div class="h5 mb-0"><?= number_format($usuarios_hoy) ?></div>
            </div>
            <div class="text-end">
              <span class="badge bg-white text-blue"><?= round(($usuarios_hoy / $usuarios_total) * 100, 1) ?>%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Solicitudes -->
      <div class="col-md-6 col-lg-3">
        <div class="dashboard-card text-white bg-purple p-4">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <div class="card-title">Solicitudes</div>
              <div class="card-subtitle">Total de tickets</div>
            </div>
            <i class="fas fa-ticket-alt card-icon"></i>
          </div>
          <div class="card-value mb-1"><?= number_format($solicitudes_general) ?></div>
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <div class="card-subtitle">Nuevas/En progreso</div>
              <div class="h5 mb-0">
                <?= number_format($solicitudes_nuevas) ?>/<?= number_format($solicitudes_enprogreso) ?>
              </div>
            </div>
            <div class="text-end">
              <span
                class="badge bg-white text-purple"><?= round((($solicitudes_nuevas + $solicitudes_enprogreso) / $solicitudes_general) * 100, 1) ?>%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tickets -->
      <div class="col-md-6 col-lg-3">
        <div class="dashboard-card text-white bg-red p-4">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <div class="card-title">Tickets</div>
              <div class="card-subtitle">Total registrados</div>
            </div>
            <i class="fas fa-tasks card-icon"></i>
          </div>
          <div class="card-value mb-1"><?= number_format($tickets_todos) ?></div>
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <div class="card-subtitle">Pendientes hoy</div>
              <div class="h5 mb-0"><?= number_format($tickets_pendientes_hoy) ?></div>
            </div>
            <div class="text-end">
              <span
                class="badge bg-white text-red"><?= round(($tickets_pendientes_hoy / $tickets_todos) * 100, 1) ?>%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráfico -->
    <div class="card chart-container border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i> Visitantes Diarios</h5>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartDropdown"
              data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-calendar-alt me-1"></i> <?= date('F Y') ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="chartDropdown">
              <li><a class="dropdown-item" href="#">Últimos 7 días</a></li>
              <li><a class="dropdown-item" href="#">Este mes</a></li>
              <li><a class="dropdown-item" href="#">Últimos 3 meses</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div id="container" style="min-width: 100%; height: 400px;"></div>
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i> Resumen Rápido</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6">
                <div class="p-3 border rounded text-center">
                  <div class="text-muted small mb-1">Tickets Abiertos</div>
                  <div class="h4 text-primary"><?= number_format($solicitudes_nuevas) ?></div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 border rounded text-center">
                  <div class="text-muted small mb-1">En Progreso</div>
                  <div class="h4 text-warning"><?= number_format($solicitudes_enprogreso) ?></div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 border rounded text-center">
                  <div class="text-muted small mb-1">Nuevos Usuarios</div>
                  <div class="h4 text-success"><?= number_format($usuarios_hoy) ?></div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 border rounded text-center">
                  <div class="text-muted small mb-1">Visitas Hoy</div>
                  <div class="h4 text-info"><?= number_format($visitantes_hoy) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Acciones Requeridas</h5>
          </div>
          <div class="card-body">
            <div class="alert alert-warning d-flex align-items-center mb-3">
              <i class="fas fa-ticket-alt me-3 fa-2x"></i>
              <div>
                <h6 class="alert-heading mb-1">Tickets sin asignar</h6>
                <p class="mb-0">Tienes <?= max(0, $solicitudes_nuevas - $solicitudes_enprogreso) ?> tickets nuevos que
                  requieren atención.</p>
              </div>
            </div>
            <div class="alert alert-info d-flex align-items-center">
              <i class="fas fa-user-clock me-3 fa-2x"></i>
              <div>
                <h6 class="alert-heading mb-1">Solicitudes en progreso</h6>
                <p class="mb-0">Hay <?= $solicitudes_enprogreso ?> tickets actualmente en proceso.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const myCat = <?= json_encode($categories); ?>;
    const visitorsCount = <?= json_encode($dataPoints); ?>;
    const monthNames = [
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    const currentMonth = new Date().getMonth();

    Highcharts.chart('container', {
      chart: {
        type: 'line',
        backgroundColor: 'transparent'
      },
      title: {
        text: 'Visitantes diarios - ' + monthNames[currentMonth],
        style: { color: '#495057', fontWeight: '600' }
      },
      xAxis: {
        categories: myCat,
        gridLineWidth: 1,
        gridLineColor: '#f1f1f1',
        labels: { style: { color: '#6c757d' } }
      },
      yAxis: {
        min: 0,
        title: {
          text: 'Número de visitas',
          style: { color: '#6c757d' }
        },
        gridLineWidth: 1,
        gridLineColor: '#f1f1f1',
        labels: { style: { color: '#6c757d' } }
      },
      tooltip: {
        backgroundColor: '#ffffff',
        borderColor: '#e9ecef',
        borderRadius: 8,
        shadow: true,
        style: { color: '#212529' },
        headerFormat: '<span style="font-size: 0.9rem; font-weight: 600">Día {point.key}</span><br/>',
        pointFormat: '<b>{point.y}</b> visitas'
      },
      legend: {
        itemStyle: {
          color: '#6c757d',
          fontWeight: 'normal'
        }
      },
      plotOptions: {
        line: {
          color: '#6240d4',
          lineWidth: 3,
          marker: {
            fillColor: '#ffffff',
            lineWidth: 2,
            lineColor: '#6240d4',
            radius: 6
          }
        }
      },
      series: [{
        name: 'Visitantes',
        data: visitorsCount
      }],
      credits: { enabled: false }
    });
  </script>
</body>

</html>