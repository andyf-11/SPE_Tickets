<?php
session_start();
require_once("dbconnection.php");
require("../checklogin.php");
check_login("tecnico");

$tecnico_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Técnico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="/styles/tech.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

    main {
      background-color: #f8f9fc;
      min-height: 100vh;
    }
  </style>
</head>

<body>
  <?php $page = 'home'; ?>
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar (ya no fijo, va debajo del header) -->
      <div class="col-md-3 col-lg-2 p-0 bg-white border-end">
        <?php include("leftbar.php"); ?>
      </div>

      <!-- Contenido principal -->
      <div class="col-md-9 col-lg-10">
        <main class="p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h2 class="fw-bold mb-1">Panel del Técnico</h2>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="home.php">Inicio</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
              </nav>
            </div>
            <div>
              <button onclick="location.reload()" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt me-1"></i> Actualizar
              </button>
            </div>
          </div>

          <?php
          function prioridadBadge($priority)
          {
            $priority = strtolower($priority);
            if (str_contains($priority, 'urgente')) {
              return '<span class="badge bg-danger">Urgente</span>';
            } elseif (str_contains($priority, 'importante')) {
              return '<span class="badge bg-warning text-dark">Importante</span>';
            } else {
              return '<span class="badge bg-secondary">' . htmlspecialchars($priority) . '</span>';
            }
          }

          $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE assigned_to = :id AND status = 'En Proceso'");
          $stmtCount->execute(['id' => $tecnico_id]);
          $count = $stmtCount->fetchColumn();

          $stmtCerrados = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE assigned_to = :id AND status = 'Cerrado' AND DATE(fecha_cierre) = CURDATE()");
          $stmtCerrados->execute(['id' => $tecnico_id]);
          $cerradosHoy = $stmtCerrados->fetchColumn();
          ?>

          <!-- Tarjetas resumen -->
          <div class="row mb-4">
            <div class="col-md-4">
              <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-uppercase mb-0">Tickets Nuevos</h6>
                    <h2 class="mb-0"><?= $count ?></h2>
                  </div>
                  <i class="fas fa-bolt fa-2x opacity-50"></i>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card bg-warning text-dark">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-uppercase mb-0">En Proceso</h6>
                    <h2 class="mb-0"><?= $count ?></h2>
                  </div>
                  <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-uppercase mb-0">Cerrados Hoy</h6>
                    <h2 class="mb-0"><?= $cerradosHoy ?></h2>
                  </div>
                  <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets Nuevos -->
          <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><i class="fas fa-bolt me-2"></i>Tickets Nuevos Asignados</span>
              <span class="badge bg-primary"><?= $count ?></span>
            </div>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Asunto</th>
                    <th>Prioridad</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $stmtNuevo = $pdo->prepare("
  SELECT 
    t.id AS ticket_id,
    t.subject,
    t.priority,
    a.id AS apply_id
  FROM 
    ticket t
  LEFT JOIN 
    application_approv a ON a.ticket_id = t.id
  WHERE 
    t.assigned_to = :id AND t.status = 'En Proceso'
");
                  $stmtNuevo->execute(['id' => $tecnico_id]);
                  $ticketsNuevo = $stmtNuevo->fetchAll(PDO::FETCH_ASSOC);

                  if ($ticketsNuevo) {
                    foreach ($ticketsNuevo as $row) {
                      $ticketId = $row['ticket_id'];
                      $applyId = $row['apply_id'];

                      echo "<tr>
            <td class='fw-bold'>#{$ticketId}</td>
            <td>{$row['subject']}</td>
            <td>" . prioridadBadge($row['priority']) . "</td>
            <td>
              <a href='manage-tickets.php?id={$ticketId}' class='btn btn-sm btn-success'>
                <i class='fas fa-reply me-1'></i>Responder
              </a>
            </td>
            <td>";

                      if ($applyId) {
                        echo "<a href='chat-tech-admin.php?apply_id={$applyId}' class='btn btn-sm btn-info'>
              <i class='fas fa-check me-1'></i>Aprobación
            </a>";
                      } else {
                        echo "<a href='chat-tech-admin.php?ticket_id={$ticketId}' class='btn btn-sm btn-warning'>
              <i class='fas fa-plus me-1'></i>Solicitar Aprobación
            </a>";
                      }

                      echo "</td>
          </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='5' class='text-center text-muted'>No hay tickets nuevos asignados</td></tr>";
                  }
                  ?>


                </tbody>
              </table>
            </div>
          </div>

          <!-- Tickets Cerrados Hoy -->
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><i class="fas fa-check-circle me-2"></i>Tickets Cerrados Hoy</span>
              <span class="badge bg-success"><?= $cerradosHoy ?></span>
            </div>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Asunto</th>
                    <th>Prioridad</th>
                    <th>Fecha de Cierre</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $stmtCerrado = $pdo->prepare("SELECT id, subject, priority, fecha_cierre FROM ticket WHERE assigned_to = :id AND status = 'Cerrado' AND DATE(fecha_cierre) = CURDATE()");
                  $stmtCerrado->execute(['id' => $tecnico_id]);
                  $ticketsCerrado = $stmtCerrado->fetchAll(PDO::FETCH_ASSOC);

                  if ($ticketsCerrado) {
                    foreach ($ticketsCerrado as $ticket) {
                      echo "<tr>
                              <td class='fw-bold'>#{$ticket['id']}</td>
                              <td>{$ticket['subject']}</td>
                              <td>" . prioridadBadge($ticket['priority']) . "</td>
                              <td>" . date('d/m/Y H:i', strtotime($ticket['fecha_cierre'])) . "</td>
                            </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>No hay tickets cerrados hoy</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</body>

</html>