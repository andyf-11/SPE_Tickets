<?php
session_start();
require_once("../dbconnection.php");
require("checklogin.php");
check_login("usuario");

$page = 'chat-list-tech';

$usuario_id = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT c.id AS chat_id, t.id AS ticket_id, t.subject AS titulo, c.status_chat, u.name AS tecnico_nombre, c.init_date, c.close_date
  FROM chat_user_tech c
  JOIN ticket t ON c.ticket_id = t.id
  JOIN user u ON c.tech_id = u.id
  WHERE c.user_id = ?
  ORDER BY c.init_date DESC
");
$stmt->execute([$usuario_id]);
$chats = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mis Chats con Técnicos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="../styles/user.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      font-weight: 300;
      margin-left: 50px;
    }

    #leftbar {
      position: fixed;
      top: 41px;
      left: 0;
      width: 250px;
      height: calc(100vh - 41px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    main.main-content {
      margin-left: 250px;
      padding: 2rem;
      min-height: calc(100vh - 56px);
    }

    @media (max-width: 767px) {
      #leftbar {
        position: relative;
        top: 0;
        width: 100%;
        height: auto;
      }

      main.main-content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body class="bg-light">

  <?php include 'header.php'; ?>

  <div class="container-fluid" style="padding-top: 2.5rem;">
    <div class="row">

      <!-- Sidebar -->
      <div class="col-lg-2 p-0">
        <?php include 'leftbar.php'; ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-lg-10 py-4 px-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="mb-0"><i class="fas fa-comments text-secondary me-2"></i> Mis Chats con Técnicos</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mt-2">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chats</li>
              </ol>
            </nav>
          </div>
          <a href="view-tickets.php" class="btn btn-outline-primary">
            <i class="fas fa-ticket-alt me-1"></i> Ver mis tickets
          </a>
        </div>

        <?php if (count($chats) === 0) : ?>
          <div class="empty-state">
            <div class="mb-4">
              <i class="fas fa-comment-slash text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="text-muted mb-3">No tienes chats con técnicos</h3>
            <p class="text-muted mb-4">Cuando inicies un chat con un técnico de soporte, aparecerá listado aquí.</p>
            <a href="view-tickets.php" class="btn btn-primary">
              <i class="fas fa-ticket-alt me-1"></i> Ver mis tickets
            </a>
          </div>
        <?php else : ?>
          <div class="row g-3">
            <?php foreach ($chats as $chat) : 
              $statusClass = strtolower($chat['status_chat']) === 'abierto' ? 'abierto' : 'cerrado';
              $statusBadge = strtolower($chat['status_chat']) === 'abierto' ? 'bg-success' : 'bg-secondary';
              $initDate = date('d/m/Y H:i', strtotime($chat['init_date']));
              $closeDate = $chat['close_date'] ? date('d/m/Y H:i', strtotime($chat['close_date'])) : null;
            ?>
            <div class="col-12">
              <a href="chat-users-techs.php?chat_id=<?= $chat['chat_id']; ?>" 
                 class="card chat-card <?= $statusClass ?> text-decoration-none">
                <div class="card-body">
                  <div class="d-flex chat-content">
                    <div class="flex-shrink-0 me-3">
                      <img src="../assets/img/Logo-Gobierno_small.png" alt="Técnico" class="chat-avatar rounded-circle">
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex flex-column flex-md-row justify-content-between">
                        <h5 class="chat-title mb-1"><?= htmlspecialchars($chat['tecnico_nombre']) ?></h5>
                        <span class="badge <?= $statusBadge ?> status-badge align-self-start align-self-md-center">
                          <?= ucfirst($chat['status_chat']) ?>
                        </span>
                      </div>
                      <p class="chat-subtitle mb-2"><?= htmlspecialchars($chat['titulo']) ?></p>
                      <div class="d-flex flex-wrap chat-meta">
                        <span class="me-3"><i class="far fa-clock me-1"></i> Iniciado: <?= $initDate ?></span>
                        <?php if ($closeDate) : ?>
                          <span><i class="far fa-calendar-times me-1"></i> Cerrado: <?= $closeDate ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="flex-shrink-0 align-self-center ms-3">
                      <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </main>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>