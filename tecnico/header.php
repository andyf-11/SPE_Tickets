<?php
if (session_status() == PHP_SESSION_NONE)
  session_start();
require_once 'dbconnection.php';

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

// Contador de notificaciones no leídas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$unreadNotifications = $stmt->fetchColumn();
?>

<!-- HEADER -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <!-- Logo -->
    <a class="navbar-brand fw-bold" href="user_dashboard.php">SPE</a>

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
          Sistema de Generación de Tickets - <span class="text-info">Ambiente Técnico</span>
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
                  href="../chat-server/notifications.php">
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

<!-- Scripts de notificaciones -->
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<!-- Notificaciones en tiempo real -->
<script src="/socket.io/socket.io.js"></script>
<?php
if (session_status() == PHP_SESSION_NONE)
  session_start();
require_once 'dbconnection.php';

$userId = $_SESSION['user_id'] ?? 0;
$userRole = $_SESSION['user_role'] ?? '';

// Contar notificaciones no leídas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$unreadNotifications = $stmt->fetchColumn();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
  <div class="container-fluid">

    <!-- Logo -->
    <a class="navbar-brand fw-bold px-2" href="#">
      SPE
    </a>

    <!-- Botón toggle móvil -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarQuickNav"
      aria-controls="navbarQuickNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido colapsable -->
    <div class="collapse navbar-collapse" id="navbarQuickNav">
      <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center w-100 gap-3">

        <!-- Título del sistema -->
        <div class="text-white fw-semibold mb-2 mb-lg-0 ms-lg-3 flex-grow-1">
          Sistema de Gestión de Tickets - <span class="text-info"><?= ucfirst($userRole) ?></span>
        </div>

        <!-- Menú de usuario -->
        <ul class="navbar-nav ms-lg-auto pe-lg-2">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userOptionsDropdown" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user-circle me-1"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userOptionsDropdown">
              <li>
                <a class="dropdown-item d-flex justify-content-between align-items-center" href="../chat-server/notifications.php">
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

<!-- Socket.IO desde CDN -->
<script src="http://localhost:3000/socket.io/socket.io.js"></script>

<!-- Notificaciones en tiempo real -->
<script>
  const USER_ID = <?= json_encode($userId) ?>;
  const USER_ROLE = <?= json_encode($role) ?>;
  const socket = io("http://localhost:3000");

  socket.emit("joinNotificationRoom", { userId: USER_ID, role: USER_ROLE });

  socket.on("receiveNotification", (data) => {
    console.log("Nueva notificación recibida:", data);

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
      if (link) link.appendChild(newBadge);
    }

    if (Notification.permission === "granted") {
      new Notification("Nueva notificación", { body: data.mensaje });
    }
  });

  if (Notification.permission !== "granted") {
    Notification.requestPermission();
  }
</script>
