<?php
session_start();
require_once("dbconnection.php");
include("checklogin.php");
check_login("tecnico");

// Obtener todos los técnicos y cantidad de tickets asignados
$stmt = $pdo->prepare("SELECT u.id, u.name, u.mobile, u.email, COUNT(t.id) AS tickets_asignados FROM user u LEFT JOIN ticket t ON u.id = t.assigned_to WHERE u.role = 'tecnico' GROUP BY u.id, u.name, u.mobile, u.email");
$stmt->execute();
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Lista de Técnicos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../styles/tech.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>

  <div class="sidebar-fixed d-none d-md-block">
    <?php include("leftbar.php"); ?>
  </div>

  <main class="px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="t_dashboard.php"><i class="fas fa-home me-2"></i>Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-users-cog me-2"></i>Técnicos</li>
        </ol>
      </nav>
      <button onclick="location.reload()" class="btn btn-outline-primary refresh-btn">
        <i class="fas fa-sync-alt me-2"></i>Actualizar
      </button>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 text-dark">
        <i class="fas fa-users-cog me-2"></i>Lista de Técnicos
      </h2>
      <span class="badge bg-success rounded-pill px-3 py-2">
        <i class="fas fa-user-tie me-1"></i> <?= count($tecnicos) ?> Técnicos
      </span>
    </div>

    <?php if (count($tecnicos) === 0): ?>
      <div class="alert alert-info d-flex align-items-center">
        <i class="fas fa-info-circle me-3 fs-4"></i>
        <div>
          <h5 class="alert-heading mb-1">No hay técnicos registrados</h5>
          <p class="mb-0">Actualmente no hay técnicos disponibles en el sistema.</p>
        </div>
      </div>
    <?php else: ?>
      <!-- Tarjetas (Mobile) -->
      <div class="d-block d-lg-none">
        <?php foreach ($tecnicos as $tecnico): ?>
          <div class="card card-technician mb-3">
            <div class="card-body">
              <div class="d-flex align-items-start gap-3 mb-3">
                <img src="../assets/img/Logo-Gobierno_small.png" alt="Técnico" class="technician-avatar">
                <div>
                  <h5 class="card-title mb-1"><?= htmlspecialchars($tecnico['name']) ?></h5>
                  <span class="badge bg-primary rounded-pill">
                    <i class="fas fa-ticket-alt me-1"></i> <?= htmlspecialchars($tecnico['tickets_asignados']) ?> tickets
                  </span>
                </div>
              </div>
              <div class="technician-info">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="fas fa-phone text-muted"></i>
                  <span><?= htmlspecialchars($tecnico['mobile']) ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <i class="fas fa-envelope text-muted"></i>
                  <span><?= htmlspecialchars($tecnico['email']) ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Tabla (Desktop) -->
      <div class="d-none d-lg-block">
        <div class="table-responsive">
          <table class="table table-technicians">
            <thead>
              <tr>
                <th class="rounded-start">Técnico</th>
                <th>Contacto</th>
                <th>Correo Electrónico</th>
                <th class="rounded-end">Tickets Asignados</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tecnicos as $tecnico): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="../assets/img/tech-avatar.png" alt="Técnico" width="40" height="40" class="rounded-circle">
                      <span><?= htmlspecialchars($tecnico['name']) ?></span>
                    </div>
                  </td>
                  <td><i class="fas fa-phone text-dark me-2"></i><?= htmlspecialchars($tecnico['mobile']) ?></td>
                  <td><i class="fas fa-envelope text-primary me-2"></i><?= htmlspecialchars($tecnico['email']) ?></td>
                  <td class="text-center">
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                      <i class="fas fa-ticket-alt me-1"></i> <?= htmlspecialchars($tecnico['tickets_asignados']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
