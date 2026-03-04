<?php
session_start();
require_once("../dbconnection.php");
require_once("../checklogin.php");
check_login();

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :userId ORDER BY created_at DESC LIMIT 20");
$stmt->execute(['userId' => $userId]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtUpdate = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :userId AND is_read = 0");
$stmtUpdate->execute(['userId' => $userId]);
?>

<style>
    .notification-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .notification-card.unread {
        border-left-color: #0d6efd;
        background-color: #f8f9ff;
    }

    .notification-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .notification-time {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .notification-link {
        color: #212529;
        text-decoration: none;
        display: block;
    }

    .notification-link:hover {
        color: #0d6efd;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e7f1ff;
        color: #0d6efd;
        margin-right: 12px;
    }

    .notification-content {
        flex: 1;
    }
</style>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/notifications.css">
</head>

<body>
    <!-- Header con gradiente -->
    <div class="page-header">
        <div class="header-content d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1">
                    <i class="bi bi-bell-fill me-2"></i>
                    Notificaciones
                </h4>
                <p class="mb-0">Mantente al día con tus últimas novedades</p>
            </div>
            <?php if (count($historial) > 0): ?>
                <span class="badge stats-badge">
                    <i class="bi bi-bell-fill me-1"></i>
                    <?= count($historial) ?> nuevas
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="notifications-container">
        <div class="row">
            <div class="col-12">
                <!-- Lista de notificaciones -->
                <div id="lista-notificaciones" class="notifications-list">
                    <?php if (count($historial) > 0): ?>
                        <?php foreach ($historial as $index => $noti): ?>
                            <div class="card notification-card mb-3 <?= $noti['is_read'] == 0 ? 'unread' : '' ?> notification-enter"
                                data-notification-id="<?= $noti['id'] ?? $index ?>">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start">
                                        <!-- Icono -->
                                        <div class="notification-icon">
                                            <i
                                                class="bi <?= $noti['is_read'] == 0 ? 'bi-envelope-fill' : 'bi-envelope-open' ?>"></i>
                                        </div>

                                        <!-- Contenido -->
                                        <div class="notification-content">
                                            <a href="<?= htmlspecialchars($noti['link'] ?? '#') ?>" class="notification-link">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <span class="notification-title">
                                                        <?= htmlspecialchars($noti['message']) ?>
                                                    </span>
                                                    <?php if ($noti['is_read'] == 0): ?>
                                                        <span class="badge badge-new flex-shrink-0">Nueva</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="notification-time mt-2">
                                                    <i class="bi bi-clock"></i>
                                                    <?= date('d M, H:i', strtotime($noti['created_at'])) ?>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Estado vacío -->
                        <div class="empty-state">
                            <i class="bi bi-bell-slash"></i>
                            <h5>¡Todo está al día!</h5>
                            <p>No tienes notificaciones pendientes. Te avisaremos cuando algo requiera tu atención.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>



<!-- Agregar Bootstrap Icons si no está incluido -->


<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<script>
    const userId = <?= json_encode($userId) ?>;
    const role = <?= json_encode($role) ?>;
    const socket = io("http://localhost:3000");

    socket.emit('joinNotificationRoom', { userId, role });

    // Resetear visualmente el contador del badge
    const badge = document.getElementById("noti-count");
    if (badge) {
        badge.innerText = "0";
        badge.style.display = "none";
    }

    // Configuración de toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000,
        extendedTimeOut: 1000,
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    };

    // Escuchar notificaciones nuevas mientras se está en esta pantalla
    socket.on("receiveNotification", (data) => {
        const lista = document.getElementById("lista-notificaciones");
        if (lista) {
            // Eliminar mensaje "No tienes notificaciones" si existe
            const noNoti = lista.querySelector(".text-center.py-5");
            if (noNoti) noNoti.remove();

            // Crear el nuevo elemento de la lista
            const nueva = document.createElement("div");
            nueva.className = "card notification-card mb-3 border-0 shadow-sm unread";
            nueva.innerHTML = `
                <div class="card-body p-3">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div class="notification-content">
                            <a href="${data.link || '#'}" class="notification-link">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold">${data.mensaje}</span>
                                    <span class="badge bg-primary rounded-pill ms-2">Nueva</span>
                                </div>
                                <div class="notification-time">
                                    <i class="bi bi-clock me-1"></i>
                                    Hace un momento
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            `;

            // Insertar al principio de la lista con animación
            nueva.style.opacity = "0";
            lista.prepend(nueva);

            setTimeout(() => {
                nueva.style.transition = "opacity 0.5s ease";
                nueva.style.opacity = "1";
            }, 10);

            // Mostrar toastr notification
            toastr.success(data.mensaje, 'Nueva notificación');
        }
    });

    // Agregar efecto hover con JavaScript para mejor UX
    document.querySelectorAll('.notification-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateX(5px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateX(0)';
        });
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>