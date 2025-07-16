<?php
session_start();
require_once("dbconnection.php");
include("checklogin.php");
check_login("supervisor");

// Obtener técnicos disponibles
$stmt = $pdo->prepare("SELECT id, name FROM user WHERE role = 'tecnico'");
$stmt->execute();
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se accede con un ID de ticket (para asignar uno en particular)
$ticketId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tecnico']) && $ticketId > 0) {
  $tecnico = $_POST['tecnico'] ?? null;

  if ($tecnico) {
    try {
      $stmt = $pdo->prepare("UPDATE ticket SET assigned_to = :tecnico, status = 'En Proceso' WHERE id = :id AND status != 'Cerrado'");
      $stmt->execute([
        ':tecnico' => $tecnico,
        ':id' => $ticketId
      ]);
      $mensaje_exito = "Ticket asignado correctamente";
    } catch (PDOException $e) {
      $error = "Error al asignar el ticket: " . $e->getMessage();
    }
  } else {
    $error = "Debes seleccionar un técnico.";
  }
}

// Filtro por edificio
$filtro = $_GET['filtro_edificio'] ?? 'todos';
$whereClause = "";
$params = [];

if ($filtro !== 'todos') {
  $whereClause = "WHERE t.edificio_id = (SELECT id FROM edificios WHERE name = :edificio)";
  $params[':edificio'] = $filtro;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Supervisor | Tickets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../styles/superv.css" rel="stylesheet">

   <style>
    body {
      font-family: 'Poppins', sans-serif;
      font-weight: 200;
    }

    @media (min-width: 768px) {
      #leftbar {
        position: fixed;
        top: 42px;
        left: 0;
        height: calc(100vh - 52px);
        z-index: 1030;
        font-weight: 300;
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
  <div class="sidebar">
    <?php include("leftbar.php"); ?>
  </div>
  <div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Gestión de Tickets</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="s_dashboard.php" class="text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tickets</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Filtro por edificio -->
    <div class="filter-card mb-4">
      <h5 class="fw-bold mb-3"><i class="fas fa-filter me-2"></i>Filtros</h5>
      <form method="get">
        <div class="row g-3 align-items-center">
          <div class="col-md-4">
            <label for="filtro_edificio" class="form-label mb-1">Edificio:</label>
            <select class="form-select" id="filtro_edificio" name="filtro_edificio" onchange="this.form.submit()">
              <option value="todos" <?= ($filtro === 'todos') ? 'selected' : '' ?>>Todos los edificios</option>
              <option value="Santa Esmeralda" <?= ($filtro === 'Santa Esmeralda') ? 'selected' : '' ?>>Santa Esmeralda</option>
              <option value="Palmira" <?= ($filtro === 'Palmira') ? 'selected' : '' ?>>Palmira</option>
            </select>
          </div>
        </div>
      </form>
    </div>

    <?php if (isset($mensaje_exito)): ?>
      <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div>
          <?= $mensaje_exito ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div>
          <?= $error ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-12">
        <?php
        try {
          $query = "
            SELECT t.*, e.name AS edificio_nombre 
            FROM ticket t 
            LEFT JOIN edificios e ON t.edificio_id = e.id
            $whereClause 
            ORDER BY t.id DESC
          ";
          $stmt = $pdo->prepare($query);
          $stmt->execute($params);
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            $ticketId = htmlspecialchars($row['ticket_id']);
            $subject = htmlspecialchars($row['subject']);
            $postingDate = htmlspecialchars($row['posting_date']);
            $status = strtolower($row['status']);
            $ticketText = htmlspecialchars($row['ticket']);
            $edificio = htmlspecialchars($row['edificio_nombre'] ?? 'Sin edificio');
            $id = (int)$row['id'];

            $badgeClass = 'secondary';
            $estadoTexto = ucfirst($status);
            if ($status === 'abierto') {
              $badgeClass = 'primary';
              $estadoTexto = 'Abierto';
            } elseif ($status === 'en proceso') {
              $badgeClass = 'warning';
              $estadoTexto = 'En Proceso';
            } elseif ($status === 'cerrado') {
              $badgeClass = 'success';
              $estadoTexto = 'Cerrado';
            }
        ?>
            <div class="card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="mb-1 fw-bold"><?= $subject ?></h5>
                  <div class="d-flex align-items-center flex-wrap">
                    <span class="text-muted me-2">#<?= $ticketId ?></span>
                    <span class="text-muted me-2"><i class="far fa-calendar me-1"></i><?= $postingDate ?></span>
                    <span class="badge bg-<?= $badgeClass ?> ticket-status-badge me-2"><?= $estadoTexto ?></span>
                    <span class="badge bg-info text-dark ticket-status-badge"><i class="fas fa-building me-1"></i><?= $edificio ?></span>
                  </div>
                </div>
                <button class="btn btn-sm btn-outline-primary ticket-details-btn" type="button" data-bs-toggle="collapse" data-bs-target="#ticket<?= $id ?>" aria-expanded="false" aria-controls="ticket<?= $id ?>">
                  <i class="fas fa-chevron-down me-1"></i> Detalles
                </button>
              </div>
              <div class="collapse" id="ticket<?= $id ?>">
                <div class="card-body">
                  <div class="d-flex align-items-start mb-3">
                    <img src="../assets/img/user.png" alt="Usuario" class="user-avatar me-3 rounded-circle" />
                    <div class="ticket-text"><?= nl2br($ticketText); ?></div>
                  </div>
                  <hr />
                  <div class="d-flex justify-content-end">
                    <?php if ($status !== 'cerrado'): ?>
                      <a href="asignar_tickets.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus me-1"></i> Asignar técnico
                      </a>
                    <?php else: ?>
                      <span class="badge bg-secondary ticket-status-badge"><i class="fas fa-lock me-1"></i> Cerrado</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
        <?php endwhile; } catch (PDOException $e) {
          echo '<div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i> Error al obtener tickets: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>