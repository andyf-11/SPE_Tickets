<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("supervisor");

$stmt = $pdo->prepare("
  SELECT u.id, u.name, u.mobile, u.email, COUNT(t.id) AS tickets_asignados
  FROM user u
  LEFT JOIN ticket t ON u.id = t.assigned_to
  WHERE u.role = 'tecnico'
  GROUP BY u.id, u.name, u.mobile, u.email
");
$stmt->execute();
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Plantilla de Técnicos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../styles/superv.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <?php include("leftbar.php"); ?>
  </div>

  <!-- Contenido principal -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="header-section">
        <div>
          <h2 class="fw-bold mb-0"><i class="fa-solid fa-gears me-2"></i>Plantilla de Técnicos</h2>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="s_dashboard.php" class="text-decoration-none">Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Técnicos</li>
            </ol>
          </nav>
        </div>
        <button class="btn btn-dark">
          <i class="fas fa-plus me-2"></i>Nuevo Técnico
        </button>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-info">
                <tr>
                  <th>Técnico</th>
                  <th>Contacto</th>
                  <th>Tickets Asignados</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tecnicos as $tecnico): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="tech-avatar">
                          <?= strtoupper(substr($tecnico['name'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="fw-bold"><?= htmlspecialchars($tecnico['name']) ?></div>
                          <small class="text-muted"><?= htmlspecialchars($tecnico['email']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span><?= htmlspecialchars($tecnico['mobile']) ?></span>
                        <small class="text-muted">Teléfono</small>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-primary bg-opacity-10 text-primary badge-count py-2 px-2">
                        <?= htmlspecialchars($tecnico['tickets_asignados']) ?>
                      </span>
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>