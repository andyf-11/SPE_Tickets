<?php
if (session_status() == PHP_SESSION_NONE)
  session_start();
require_once 'dbconnection.php';

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

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
                  <span id="noti-count" class="badge bg-danger ms-2 <?= $unreadNotifications > 0 ? '' : 'd-none' ?>">
                    <?= $unreadNotifications ?>
                  </span>
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

<!-- Script de notificaciones Socket.IO -->
<script>
  const userId = <?= json_encode($userId) ?>;
  const role = <?= json_encode($userRole) ?>;
</script>
<script src="../chat-server/notifications.js"></script>

<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
  const USER_ID = <?= json_encode($userId) ?>;
  const USER_ROLE = <?= json_encode($userRole) ?>;
  const socket = io("http://localhost:3000");

  socket.emit("joinRoom", {
    userId: USER_ID,
    role: USER_ROLE
  });

  socket.on("nuevaNotificacion", (data) => {
    const badge = document.getElementById("noti-count");

    if (badge) {
      let current = parseInt(badge.innerText || 0);
      badge.innerText = current + 1;
      badge.classList.remove("d-none");
    } else {
      const newBadge = document.createElement("span");
      newBadge.id = "noti-count";
      newBadge.className = "badge bg-danger ms-2";
      newBadge.innerText = "1";

      const link = document.querySelector("a[href*='notifications.php']");
      link.appendChild(newBadge);
    }
  });
</script>