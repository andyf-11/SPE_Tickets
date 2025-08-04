<?php
session_start();
require_once("dbconnection.php");
include("checklogin.php");
require_once '../assets/data/notifications_helper.php'; // ✅ Importamos el helper
check_login("supervisor");

// Obtener técnicos disponibles
$stmt = $pdo->prepare("SELECT id, name FROM user WHERE role = 'tecnico'");
$stmt->execute();
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se accede con un ID de ticket (para asignar uno en particular)
$ticketId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tecnico']) && $ticketId > 0) {
    $tecnico = $_POST['tecnico'] ?? null;

    if ($tecnico) {
        try {
            $stmt = $pdo->prepare("UPDATE ticket SET assigned_to = :tecnico, status = 'En Proceso' WHERE id = :id AND status != 'Cerrado'");
            $stmt->execute([
                ':tecnico' => $tecnico,
                ':id' => $ticketId
            ]);

            // 🔔 Notificar al técnico y admin
            notificarAsignacionTicket($ticketId, $tecnico);

            $mensaje_exito = "Ticket asignado correctamente";
        } catch (PDOException $e) {
            $error = "Error al asignar el ticket: " . $e->getMessage();
        }
    } else {
        $error = "Debes seleccionar un técnico.";
    }
}

// Filtro por edificio
$filtro = $_GET['filtro_edificio'] ?? 'todos';
$whereClause = "";
$params = [];

if ($filtro !== 'todos') {
    $whereClause = "WHERE t.edificio_id = (SELECT id FROM edificios WHERE name = :edificio)";
    $params[':edificio'] = $filtro;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Supervisor | Tickets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --header-height: 56px;
            --sidebar-width: 250px;
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f72585;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --card-hover-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            background-color: var(--light-bg);
            color: #333;
        }

        .header-section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }

        .filter-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .ticket-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.25rem;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
        }

        .ticket-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .ticket-card .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            cursor: pointer;
        }

        .ticket-status-badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.4rem 0.75rem;
            border-radius: 50px;
            letter-spacing: 0.5px;
        }

        .badge-primary {
            background-color: rgba(67, 97, 238, 0.1) !important;
            color: var(--primary-color) !important;
        }

        .badge-warning {
            background-color: rgba(248, 150, 30, 0.1) !important;
            color: var(--warning-color) !important;
        }

        .badge-success {
            background-color: rgba(76, 201, 240, 0.1) !important;
            color: var(--success-color) !important;
        }

        .badge-secondary {
            background-color: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
        }

        .badge-info {
            background-color: rgba(23, 162, 184, 0.1) !important;
            color: #17a2b8 !important;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .ticket-text {
            font-size: 0.925rem;
            line-height: 1.7;
            color: #555;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.825rem;
        }

        .rounded-pill {
            border-radius: 50px !important;
        }

        /* Layout styles */
        @media (min-width: 768px) {
            body>.page-wrapper {
                display: flex;
                min-height: 100vh;
                margin-top: var(--header-height);
            }

            #leftbar {
                position: fixed;
                top: var(--header-height);
                left: 0;
                width: var(--sidebar-width);
                height: calc(100vh - var(--header-height));
                z-index: 1030;
                font-weight: 300;
                overflow-y: auto;
            }

            main.main-content {
                margin-left: var(--sidebar-width);
                flex-grow: 1;
                padding: 2rem;
            }
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            z-index: 1040;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        /* Animaciones */
        .collapse:not(.show) {
            display: none;
        }

        .collapsing {
            transition: height 0.3s ease;
        }

        /* Mejoras para mobile */
        @media (max-width: 767.98px) {
            main.main-content {
                padding: 1.25rem;
            }

            .header-section,
            .filter-card {
                padding: 1.25rem;
            }

            .ticket-card .card-header {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>

    <div class="page-wrapper">
        <?php include("leftbar.php"); ?>

        <main class="main-content">
            <div class="header-section">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fa-solid fa-ticket-alt me-2"></i>Gestión de Tickets</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="s_dashboard.php"
                                    class="text-decoration-none">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tickets</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Filtro por edificio -->
            <div class="filter-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-filter me-2 text-primary"></i>Filtros</h5>
                <form method="get">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4 col-lg-3">
                            <label for="filtro_edificio" class="form-label mb-1">Edificio:</label>
                            <select class="form-select shadow-sm" id="filtro_edificio" name="filtro_edificio"
                                onchange="this.form.submit()">
                                <option value="todos" <?= ($filtro === 'todos') ? 'selected' : '' ?>>Todos los edificios
                                </option>
                                <option value="Santa Esmeralda" <?= ($filtro === 'Santa Esmeralda') ? 'selected' : '' ?>>
                                    Santa Esmeralda</option>
                                <option value="Palmira" <?= ($filtro === 'Palmira') ? 'selected' : '' ?>>Palmira</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (isset($mensaje_exito)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-check-circle me-2 fs-4"></i>
                    <div class="flex-grow-1"><?= $mensaje_exito ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2 fs-4"></i>
                    <div class="flex-grow-1"><?= $error ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <?php
                    try {
                        $query = "
              SELECT t.*, e.name AS edificio_nombre 
              FROM ticket t 
              LEFT JOIN edificios e ON t.edificio_id = e.id
              $whereClause 
              ORDER BY t.id DESC
            ";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute($params);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            $ticketId = htmlspecialchars($row['ticket_id']);
                            $subject = htmlspecialchars($row['subject']);
                            $postingDate = htmlspecialchars($row['posting_date']);
                            $status = strtolower($row['status']);
                            $ticketText = htmlspecialchars($row['ticket']);
                            $edificio = htmlspecialchars($row['edificio_nombre'] ?? 'Sin edificio');
                            $id = (int) $row['id'];

                            $badgeClass = 'secondary';
                            $estadoTexto = ucfirst($status);
                            if ($status === 'abierto') {
                                $badgeClass = 'primary';
                                $estadoTexto = 'Abierto';
                            } elseif ($status === 'en proceso') {
                                $badgeClass = 'warning';
                                $estadoTexto = 'En Proceso';
                            } elseif ($status === 'cerrado') {
                                $badgeClass = 'success';
                                $estadoTexto = 'Cerrado';
                            }
                            ?>
                            <div class="card ticket-card mb-3">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1 fw-bold"><?= $subject ?></h5>
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <span class="text-muted">#<?= $ticketId ?></span>
                                                <span class="text-muted"><i
                                                        class="far fa-calendar me-1"></i><?= $postingDate ?></span>
                                                <span
                                                    class="badge bg-<?= $badgeClass ?> ticket-status-badge"><?= $estadoTexto ?></span>
                                                <span class="badge bg-info text-dark ticket-status-badge"><i
                                                        class="fas fa-building me-1"></i><?= $edificio ?></span>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#ticket<?= $id ?>" aria-expanded="false"
                                            aria-controls="ticket<?= $id ?>">
                                            <i class="fas fa-chevron-down me-1"></i> Detalles
                                        </button>
                                    </div>
                                </div>
                                <div class="collapse" id="ticket<?= $id ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <img src="../assets/img/user.png" alt="Usuario"
                                                class="user-avatar me-3 rounded-circle" />
                                            <div class="ticket-text"><?= nl2br($ticketText); ?></div>
                                        </div>
                                        <hr />
                                        <div class="d-flex justify-content-end">
                                            <?php if ($status !== 'cerrado'): ?>
                                                <a href="asignar_tickets.php?id=<?= $row['id'] ?>"
                                                    class="btn btn-primary btn-sm rounded-pill">
                                                    <i class="fas fa-user-plus me-1"></i> Asignar técnico
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary ticket-status-badge"><i
                                                        class="fas fa-lock me-1"></i> Cerrado</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile;
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2 fs-4"></i> Error al obtener tickets: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const role = <?php echo json_encode($_SESSION['user_role']); ?>;
    </script>
    <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
    <script src="../chat-server/notifications.js"></script>
</body>

</html>