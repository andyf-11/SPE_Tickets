<?php
if (session_status() == PHP_SESSION_NONE)
  session_start();
require_once 'dbconnection.php';

$userId = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$unreadNotifications = $stmt->fetchColumn();
?>

<!-- HEADER -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <!-- Logo -->
    <a class="navbar-brand fw-bold" href="t_dashboard.php">SPE</a>

    <!-- Botón toggle móvil -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarQuickNav"
      aria-controls="navbarQuickNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido colapsable -->
    <div class="collapse navbar-collapse" id="navbarQuickNav">
      <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center w-100">

        <!-- Título del sistema -->
        <div class="text-white fw-semibold mb-2 mb-lg-0 me-lg-auto ps-lg-3">
          Sistema de Generación de Tickets - <span class="text-info">Ambiente Técnicos</span>
        </div>

        <!-- Menú de usuario -->
        <ul class="navbar-nav ms-lg-auto pe-lg-2">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userOptionsDropdown" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-cog"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userOptionsDropdown">
              <li>
                <a class="dropdown-item d-flex justify-content-between align-items-center"
                  href="../assets/data/notifications.php">
                  <span><i class="fa fa-bell"></i>&nbsp;&nbsp;Notificaciones</span>
                  <?php if ($unreadNotifications > 0): ?>
                    <span class="badge bg-danger ms-2"><?= $unreadNotifications ?></span>
                  <?php endif; ?>
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item text-danger" href="logout.php">
                  <i class="fa fa-power-off me-2"></i>Cerrar Sesión
                </a>
              </li>
            </ul>
          </li>
        </ul>

      </div>
    </div>
  </div>
</nav>
<!-- END HEADER -->