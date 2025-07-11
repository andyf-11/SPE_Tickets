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
  <link href="../styles/tech.css" rel="stylesheet">
</head>

<body>

  <!-- HEADER -->
  <?php include 'header.php'; ?>

  <div class="container-fluid">
    <div class="row">

      <!-- LEFTBAR -->
      <div class="col-md-2 p-0">
        <?php include 'leftbar.php'; ?>
      </div>

      <!-- CONTENIDO -->
      <main class="col-md-10 pt-4 px-4">
        <div class="d-flex justify-content-between align-items-center page-header">
          <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-comments me-2 text-primary"></i>Chats con Usuarios</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb bg-transparent p-0 mt-2">
                <li class="breadcrumb-item"><a href="t_dashboard.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-comments me-1"></i>Chats</li>
              </ol>
            </nav>
          </div>
          <button onclick="location.reload()" class="btn btn-outline-primary">
            <i class="fas fa-sync-alt me-1"></i> Actualizar
          </button>
        </div>

        <?php if (count($chats) === 0) : ?>
          <div class="card no-chats-card py-5">
            <div class="card-body text-center">
              <div class="mb-4">
                <i class="fas fa-comment-slash text-muted" style="font-size: 3rem;"></i>
              </div>
              <h5 class="card-title text-gray-800">No tienes chats con usuarios</h5>
              <p class="card-text text-muted">Cuando tengas chats activos con usuarios, aparecerán listados aquí.</p>
            </div>
          </div>
        <?php else : ?>
          <div class="row">
            <div class="col-lg-8">
              <div class="mb-4">
                <h5 class="text-gray-800 mb-3">Tus conversaciones activas</h5>
                <p class="text-muted small">Selecciona un chat para ver los mensajes y responder al usuario.</p>
              </div>
              
              <div class="chats-list">
                <?php foreach ($chats as $chat) : ?>
                  <a href="chat-users-techs.php?chat_id=<?= $chat['chat_id']; ?>" 
                     class="d-block text-decoration-none mb-3 <?= $chat['status_chat'] === 'abierto' ? 'active' : '' ?>">
                    <div class="chat-card p-3">
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                          <img src="../assets/img/user.png" alt="Usuario" class="chat-avatar">
                          <div>
                            <div class="chat-title"><?= htmlspecialchars($chat['user_name']); ?></div>
                            <div class="chat-subject"><?= htmlspecialchars($chat['titulo']); ?></div>
                            <div class="d-flex align-items-center gap-2 chat-meta">
                              <span><i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($chat['init_date'])); ?></span>
                              <span class="badge <?= $chat['status_chat'] === 'abierto' ? 'badge-open' : 'badge-closed' ?> status-badge">
                                <?= $chat['status_chat'] === 'abierto' ? 'Abierto' : 'Cerrado' ?>
                              </span>
                            </div>
                          </div>
                        </div>
                        <div>
                          <i class="fas fa-chevron-right chevron-icon"></i>
                        </div>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
            
            <div class="col-lg-4">
              <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                  <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle me-2"></i>Información de Chats</h6>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <h6 class="small text-gray-800 mb-2"><i class="fas fa-circle text-success me-2"></i>Chats Activos</h6>
                    <p class="small text-muted">Los chats marcados como "Abierto" están esperando tu respuesta.</p>
                  </div>
                  <div class="mb-3">
                    <h6 class="small text-gray-800 mb-2"><i class="fas fa-circle text-secondary me-2"></i>Chats Cerrados</h6>
                    <p class="small text-muted">Puedes revisar chats cerrados pero no podrás responder.</p>
                  </div>
                  <hr>
                  <div>
                    <h6 class="small text-gray-800 mb-2"><i class="fas fa-question-circle me-2"></i>¿Necesitas ayuda?</h6>
                    <p class="small text-muted">Si tienes problemas con los chats, contacta al administrador.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </main>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>