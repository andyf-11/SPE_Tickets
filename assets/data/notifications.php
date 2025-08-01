<?php
session_start();
require_once("../../dbconnection.php");
require_once("../../checklogin.php");
check_login();

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

$dashboard = '../../dashboard.php';

switch ($role) {
    case 'admin':
        $dashboard = '../../admin/home.php';
        break;
    case 'supervisor':
        $dashboard = '../../supervisor/s_dashboard.php';
        break;
    case 'tecnico':
        $dashboard = '../../tecnico/t_dashboard.php';
        break;
    case 'usuario':
        $dashboard = '../../dashboard.php';
        break;
    default:
        $dashboard = '../../index.php';
        break;
}

// Notificaciones a mostrar
$sql = "SELECT * FROM notifications WHERE user_id = ? ";
switch ($role) {
    case 'usuario':
        $sql .= "AND type IN ('respuesta_ticket', 'nuevo_mensaje_chat')";
        break;
    case 'tecnico':
        $sql .= "AND type IN ('asignacion_ticket', 'nuevo_mensaje_chat', 'respuesta_aprobacion')";
        break;
    case 'supervisor':
        $sql .= "AND type = 'nuevo_ticket'";
        break;
    case 'admin':
        break;
    default:
        $sql .= "AND 0";
        break;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$notificaciones = $stmt->fetchAll();

// Contador de no leídas
$sqlCount = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute([$userId]);
$notiCount = (int)$stmtCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">SPE</a>
            <span class="navbar-text text-white">Notificaciones</span>
            <a class="btn btn-outline-light position-relative" href="<?= $dashboard ?>">
                Volver
                <?php if ($notiCount > 0): ?>
                    <span id="noti-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $notiCount ?>
                    </span>
                <?php else: ?>
                    <span id="noti-count" class="d-none"></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <div class="container" style="margin-top: 80px;">
        <h3 class="mb-3">Tus notificaciones</h3>

        <div id="lista-notificaciones">
            <?php if (empty($notificaciones)): ?>
                <div class="alert alert-info">No tienes notificaciones.</div>
            <?php endif; ?>

            <?php foreach ($notificaciones as $n): ?>
                <div class="card mb-2 <?= $n['is_read'] ? 'text-muted' : 'fw-bold' ?>">
                    <div class="card-body">
                        <a href="<?= $n['link'] ?>" class="text-decoration-none" onclick="marcarComoLeida(<?= $n['id'] ?>)">
                            <?= htmlspecialchars($n['message']) ?>
                        </a>
                        <div><small class="text-muted"><?= $n['created_at'] ?></small></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        const userId = <?= (int)$userId ?>;
        const role = "<?= $role ?>";
        const socket = io("http://localhost:3000");

        socket.emit('joinNotificationRoom', { userId: userId, rol: role });

        socket.on('receiveNotification', (data) => {
            console.log("🔔 Nueva notificación:", data);
            toastr.info(data.mensaje);

            // Actualizar contador
            const badge = document.getElementById("noti-count");
            let current = parseInt(badge?.innerText || 0);
            badge.innerText = current + 1;
            badge.classList.remove("d-none");

            // Insertar en el DOM
            const lista = document.getElementById('lista-notificaciones');
            const nueva = document.createElement('div');
            nueva.classList.add('card', 'mb-2', 'fw-bold');
            nueva.innerHTML = `
                <div class="card-body">
                    <a href="#" class="text-decoration-none">${data.mensaje}</a>
                    <div><small class="text-muted">Ahora</small></div>
                </div>`;
            lista.prepend(nueva);
        });

        function marcarComoLeida(id) {
            fetch('check_read.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById("noti-count");
                        let current = parseInt(badge?.innerText || 0);
                        if (current > 1) {
                            badge.innerText = current - 1;
                        } else {
                            badge.innerText = '';
                            badge.classList.add("d-none");
                        }
                        location.reload();
                    }
                });
        }

        // Al abrir la página, se puede marcar todo como leído (opcional)
        fetch('check_read.php?all=1')
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById("noti-count");
                badge.innerText = '';
                badge.classList.add("d-none");
            });
    </script>
</body>
</html>
