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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/admin/manage-passwords.css" rel="stylesheet">
  <style>
    /* Sidebar fijo con altura completa menos header */
    #leftbar {
      position: fixed;
      top: 56px;
      /* altura del header fijo */
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
      padding-top: 70px;
      /* espacio para header */
      min-height: 100vh;
    }
  </style>
</head>

<body>

  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Botón menú para móviles -->
      <button class="btn btn-outline-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#leftbar">
        <i class="fas fa-bars me-2"></i> Menú
      </button>

      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse offcanvas-md offcanvas-start" id="leftbar">
        <?php include("leftbar.php"); ?>
      </nav>

      <!-- Contenido principal -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 mt-5">
        <div
          class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
          <div>
            <h1 class="h2 mb-0"><i class="fa-solid fa-key me-2 text-primary"></i>Solicitudes de Recuperación</h1>
            <p class="lead text-muted mb-0">Revisa y gestiona las solicitudes de recuperación de contraseña</p>
          </div>
          <div class="btn-toolbar mb-2 mb-md-0">
            <a href="home.php" class="btn btn-outline-secondary">
              <i class="fas fa-chevron-left me-1"></i> Volver
            </a>
          </div>
        </div>

        <?php if (!empty($_SESSION['msg'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= $_SESSION['msg'];
            unset($_SESSION['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
          </div>
        <?php endif; ?>

        <div class="card table-card shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Listado de Solicitudes</h5>
            <span class="badge bg-primary rounded-pill"><?= count($solicitudes) ?> solicitudes</span>
          </div>

          <?php if (count($solicitudes) === 0): ?>
            <div class="card-body text-center py-5">
              <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
              <h5 class="text-muted">No hay solicitudes pendientes</h5>
              <p class="text-muted">Cuando los usuarios soliciten recuperación de contraseña, aparecerán aquí.</p>
            </div>
          <?php else: ?>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th scope="col" class="ps-4">#</th>
                      <th scope="col">Correo</th>
                      <th scope="col">Motivo</th>
                      <th scope="col">Fecha</th>
                      <th scope="col">Estado</th>
                      <th scope="col" class="text-end pe-4">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($solicitudes as $i => $s): ?>
                      <tr>
                        <th scope="row" class="ps-4"><?= $i + 1 ?></th>
                        <td>
                          <a href="mailto:<?= htmlspecialchars($s['correo']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($s['correo']) ?>
                          </a>
                        </td>
                        <td><?= nl2br(htmlspecialchars($s['motivo'])) ?></td>
                        <td>
                          <span class="d-block"><?= date('d/m/Y', strtotime($s['soli_date'])) ?></span>
                          <small class="text-muted"><?= date('H:i', strtotime($s['soli_date'])) ?></small>
                        </td>
                        <td>
                          <span
                            class="badge rounded-pill <?= $s['status'] === 'pendiente' ? 'badge-pendiente' : 'badge-atendida' ?>">
                            <?= ucfirst($s['status']) ?>
                          </span>
                        </td>
                        <td class="text-end pe-4">
                          <?php if ($s['status'] === 'pendiente'): ?>
                            <a href="?atender=<?= $s['id'] ?>" class="btn btn-sm btn-success action-btn">
                              <i class="fas fa-check me-1"></i> Atender
                            </a>
                          <?php else: ?>
                            <span class="text-success">
                              <i class="fas fa-check-circle me-1"></i>Atendida
                            </span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer bg-light">
              <small class="text-muted">Mostrando <?= count($solicitudes) ?> solicitudes</small>
            </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    const role = <?php echo json_encode($_SESSION['user_role']); ?>;
  </script>
  <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
  <script src="../chat-server/notifications.js"></script>

</body>

</html>