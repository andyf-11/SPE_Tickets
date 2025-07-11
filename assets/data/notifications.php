<?php
session_start();
require_once("../../dbconnection.php");
require_once("../../checklogin.php");
check_login();

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['user_role'] ?? '';

$dashboard = '../../dashboard.php'; // Valor por defecto

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
        $dashboard = '../../index.php'; // ruta segura por si acaso
        break;
}

// Armamos la consulta filtrando por el rol
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
        // El admin recibe todas → no se filtra por tipo
        break;

    default:
        // Si por alguna razón el rol no es reconocido → no devuelve nada
        $sql .= "AND 0";
        break;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$notificaciones = $stmt->fetchAll();


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">SPE</a>
            <span class="navbar-text text-white">Notificaciones</span>
            <a class="btn btn-outline-light" href="<?= $dashboard ?>">Volver</a>
        </div>
    </nav>

    <div class="container" style="margin-top: 80px;">
        <h3 class="mb-3">Tus notificaciones</h3>

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

    <script>
        function marcarComoLeida(id) {
            fetch('check_read.php?id=' + id)
                .then(res => res.json())
                .then(data => {
                    // Opcional: puedes recargar la página o actualizar el estilo del elemento
                    location.reload();
                });
        }
    </script>

</body>

</html>