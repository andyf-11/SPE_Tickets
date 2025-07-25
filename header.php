<?php
if (session_status() == PHP_SESSION_NONE)
  session_start();
require_once 'dbconnection.php';

$userId = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$unreadNotifications = $stmt->fetchColumn();
?>

<nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container-fluid d-flex justify-content-between align-items-center">

    <!-- Logo e icono de menú -->
    <div class="d-flex align-items-center">
      <button class="navbar-toggler me-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <h2 class="mb-0 fs-5">
        <a href="dashboard.php" class="text-white text-decoration-none"><strong>SPE</strong></a>
      </h2>
    </div>

    <!-- Título del sistema -->
    <div class="text-center flex-grow-1 d-none d-md-block">
      <h5 class="mb-0 text-white text-truncate px-2">
        Sistema de Generación de Tickets - Acceso Empleados
      </h5>
    </div>

    <!-- Menú de usuario -->
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="user-options"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-cog me-2"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-options">
        <li>
          <a class="dropdown-item d-flex justify-content-between align-items-center" href="assets/data/notifications.php">
            <span><i class="fa fa-bell"></i>&nbsp;&nbsp;Notificaciones</span>
            <?php if ($unreadNotifications > 0): ?>
              <span class="badge bg-danger ms-2"><?= $unreadNotifications ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li><a class="dropdown-item" href="logout.php"><i class="fa fa-power-off"></i>&nbsp;&nbsp;Cerrar Sesión</a></li>
      </ul>
    </div>

  </div>
</nav>