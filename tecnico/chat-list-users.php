<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("tecnico");

$page = 'chat-list-users';

$tecnico_id = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT c.id AS chat_id, t.id AS ticket_id, t.subject AS titulo, c.status_chat, u.name AS user_name, c.init_date, c.close_date
  FROM chat_user_tech c
  JOIN ticket t ON c.ticket_id = t.id
  JOIN user u ON c.user_id = u.id
  WHERE c.tech_id = ?
  ORDER BY c.init_date DESC
");
$stmt->execute([$tecnico_id]);
$chats = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Chats con Usuarios - Técnico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../styles/tech.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    .fixed-header {
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1030;
    }

    .layout-wrapper {
      display: flex;
      margin-top: 6px; /* Altura del navbar */
    }

    .sidebar {
      width: 250px;
      height: calc(100vh - 56px);
      position: fixed;
      top: 56px;
      left: 0;
      overflow-y: auto;
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1020;
    }

    .main-content {
      margin-left: 250px;
      padding: 2rem;
      width: calc(100% - 250px);
      background-color: #f8f9fc;
      min-height: calc(100vh - 56px);
    }

    .chat-card {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      background: #fff;
      transition: all 0.2s ease;
    }

    .chat-card:hover {
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .chat-avatar {
      width: 45px;
      height: 45px;
      object-fit: cover;
      border-radius: 50%;
    }

    .chat-title {
      font-weight: 600;
      color: #343a40;
    }

    .chat-subject {
      font-size: 0.9rem;
      color: #6c757d;
    }

    .status-badge {
      font-size: 0.75rem;
      padding: 0.3em 0.6em;
    }

    .badge-open {
      background-color: #198754;
      color: white;
    }

    .badge-closed {
      background-color: #6c757d;
      color: white;
    }

    .chevron-icon {
      color: #adb5bd;
    }
  </style>
</head>

<body>
  <!-- HEADER -->
  <div class="fixed-header">
    <?php include 'header.php'; ?>
  </div>

  <div class="layout-wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
      <?php $page = 'chat-list-users'; ?>
      <?php include 'leftbar.php'; ?>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h4 fw-bold mb-1"><i class="fas fa-comments me-2 text-primary"></i>Chats con Usuarios</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
              <li class="breadcrumb-item"><a href="t_dashboard.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Chats</li>
            </ol>
          </nav>
        </div>
        <button onclick="location.reload()" class="btn btn-outline-primary">
          <i class="fas fa-sync-alt me-1"></i> Actualizar
        </button>
      </div>

      <?php if (count($chats) === 0) : ?>
        <div class="card py-5 text-center">
          <div class="card-body">
            <i class="fas fa-comment-slash text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="text-dark">No tienes chats con usuarios</h5>
            <p class="text-muted">Cuando tengas chats activos con usuarios, aparecerán aquí.</p>
          </div>
        </div>
      <?php else : ?>
        <div class="row">
          <div class="col-lg-8">
            <h5 class="mb-3 fw-semibold">Tus conversaciones activas</h5>
            <p class="text-muted small">Selecciona un chat para ver los mensajes y responder al usuario.</p>

            <div class="chats-list">
              <?php foreach ($chats as $chat) : ?>
                <a href="chat-users-techs.php?chat_id=<?= $chat['chat_id']; ?>" class="text-decoration-none mb-3 d-block">
                  <div class="chat-card p-3 <?= $chat['status_chat'] === 'abierto' ? 'border-primary border-2' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                        <img src="../assets/img/user.png" alt="Usuario" class="chat-avatar">
                        <div>
                          <div class="chat-title"><?= htmlspecialchars($chat['user_name']); ?></div>
                          <div class="chat-subject"><?= htmlspecialchars($chat['titulo']); ?></div>
                          <div class="d-flex align-items-center gap-2 chat-meta text-muted small">
                            <span><i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($chat['init_date'])); ?></span>
                            <span class="badge <?= $chat['status_chat'] === 'abierto' ? 'badge-open' : 'badge-closed' ?> status-badge">
                              <?= $chat['status_chat'] === 'abierto' ? 'Abierto' : 'Cerrado' ?>
                            </span>
                          </div>
                        </div>
                      </div>
                      <i class="fas fa-chevron-right chevron-icon"></i>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card shadow-sm">
              <div class="card-header bg-white fw-bold">
                <i class="fas fa-info-circle me-2 text-primary"></i>Información
              </div>
              <div class="card-body small">
                <p><i class="fas fa-circle text-success me-2"></i><strong>Chats Activos:</strong> esperando tu respuesta.</p>
                <p><i class="fas fa-circle text-secondary me-2"></i><strong>Chats Cerrados:</strong> no se pueden responder.</p>
                <hr>
                <p><i class="fas fa-question-circle me-2"></i>¿Necesitas ayuda? Contacta al administrador.</p>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>