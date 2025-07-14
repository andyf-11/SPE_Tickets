<?php
session_start();
require_once("../checklogin.php");
check_login("admin");
require("../dbconnection.php");

$page = "manage-passwords";

// Marcar solicitud como atendida
if (isset($_GET['atender']) && is_numeric($_GET['atender'])) {
    $id = intval($_GET['atender']);
    $stmt = $pdo->prepare("UPDATE password_request SET status = 'atendida' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['msg'] = "Solicitud marcada como atendida.";
    header("Location: password-requests.php");
    exit();
}

// Obtener solicitudes
$stmt = $pdo->query("SELECT * FROM password_request ORDER BY soli_date DESC");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin | Solicitudes de Contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../styles/admin.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .badge-pendiente {
      background-color: #ffc107;
      color: #212529;
    }
    .badge-atendida {
      background-color: #28a745;
    }
  </style>
</head>
<body>

<?php include("header.php"); ?>

<div class="container-fluid">
  <div class="row">
    <!-- Botón menú para móviles -->
    <button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar">
      <i class="fas fa-bars me-2"></i> Menú
    </button>

    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse offcanvas-md offcanvas-start" id="leftbar">
      <?php include("leftbar.php"); ?>
    </nav>

    <!-- Contenido principal -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 mt-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h4 mb-0"><i class="fa-solid fa-key me-2 text-primary mt-4"></i>Solicitudes de Recuperación de Contraseña</h2>
          <p class="text-muted mb-0">Revisa las solicitudes enviadas por los usuarios</p>
        </div>
        <a href="home.php" class="btn btn-outline-secondary mt-4"><i class="fas fa-chevron-left me-1"></i> Volver</a>
      </div>

      <?php if (!empty($_SESSION['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
      <?php endif; ?>

      <?php if (count($solicitudes) === 0): ?>
        <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No hay solicitudes en este momento.</div>
      <?php else: ?>
        <div class="card shadow-sm">
          <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th>#</th>
                  <th>Correo</th>
                  <th>Motivo</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($solicitudes as $i => $s): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($s['correo']) ?></td>
                    <td><?= nl2br(htmlspecialchars($s['motivo'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($s['soli_date'])) ?></td>
                    <td>
                      <span class="badge <?= $s['status'] === 'pendiente' ? 'badge-pendiente' : 'badge-atendida' ?>">
                        <?= ucfirst($s['status']) ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($s['status'] === 'pendiente'): ?>
                        <a href="?atender=<?= $s['id'] ?>" class="btn btn-sm btn-outline-success">
                          <i class="fas fa-check me-1"></i> Atender
                        </a>
                      <?php else: ?>
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>Atendida</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

