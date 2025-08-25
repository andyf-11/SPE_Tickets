<?php
if (session_status() == PHP_SESSION_NONE)
  session_start();
require_once '../dbconnection.php';

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? ' ';

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
          <a class="dropdown-item d-flex justify-content-between align-items-center"
            href="../assets/data/notifications.php">
            <span><i class="fa fa-bell"></i>&nbsp;&nbsp;Notificaciones</span>
            <span id="noti-count" class="badge bg-danger ms-2 <?= $unreadNotifications > 0 ? '' : 'd-none' ?>">
              <?= $unreadNotifications ?>
            </span>
          </a>

        </li>

        <li><a class="dropdown-item" href="logout.php"><i class="fa fa-power-off"></i>&nbsp;&nbsp;Cerrar Sesión</a></li>
      </ul>
    </div>

  </div>
</nav>

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