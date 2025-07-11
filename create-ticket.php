<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("usuario");

$page = 'create-ticket';

// Opciones de prioridad
$prioridades = ['Importante', 'Urgente-(Problema Funcional)', 'No-Urgente', 'Pregunta'];

// Obtener lista de edificios
$stmt_edificios = $pdo->query("SELECT id, name FROM edificios ORDER BY name ASC");
$edificios = $stmt_edificios->fetchAll(PDO::FETCH_ASSOC);

$show_success_toast = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $count_my_page = "hitcounter.txt";
  $hits = file($count_my_page);
  $hits[0]++;
  file_put_contents($count_my_page, $hits[0]);
  $tid = $hits[0];

  $email = $_SESSION['login'];
  $subject = trim($_POST['subject']);
  $priority = $_POST['priority'];
  $ticket = trim($_POST['description']);
  $edificio_id = $_POST['edificio_id'] ?? null; // ✅ Capturar el edificio
  $st = "Open";
  $pdate = date('Y-m-d H:i:s');

  if (!$subject || !$priority || !$ticket || !$edificio_id) {
    $error = "Por favor completa todos los campos.";
  } else {
    // ✅ Insertar ticket con edificio_id
    $stmt = $pdo->prepare("
      INSERT INTO ticket(ticket_id, email_id, subject, priority, ticket, status, posting_date, edificio_id) 
      VALUES(:tid, :email, :subject, :priority, :ticket, :status, :pdate, :edificio_id)
    ");

    if ($stmt->execute([
      ':tid' => $tid,
      ':email' => $email,
      ':subject' => $subject,
      ':priority' => $priority,
      ':ticket' => $ticket,
      ':status' => $st,
      ':pdate' => $pdate,
      ':edificio_id' => $edificio_id
    ])) {
      // Notificar supervisores y admins
      $stmt_sup = $pdo->prepare("SELECT id FROM user WHERE role = 'supervisor'");
      $stmt_sup->execute();
      $supervisores = $stmt_sup->fetchAll(PDO::FETCH_COLUMN);

      $stmt_admin = $pdo->prepare("SELECT id FROM user WHERE role = 'admin'");
      $stmt_admin->execute();
      $admins = $stmt_admin->fetchAll(PDO::FETCH_COLUMN);

      $mensaje = "Nuevo ticket #$tid creado por el usuario $email.";
      $noti = $pdo->prepare("INSERT INTO notifications (user_id, ticket_id, type, message) VALUES (?, ?, 'nuevo_ticket', ?)");

      foreach ($supervisores as $sup_id) {
        $noti->execute([$sup_id, $tid, $mensaje]);
      }
      foreach ($admins as $admin_id) {
        $noti->execute([$admin_id, $tid, $mensaje]);
      }

      $show_success_toast = true;
      $_POST = [];
    } else {
      $error = "Error al registrar el ticket. Intente nuevamente.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crear Ticket</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <!-- Estilos personalizados -->
   <link href="styles/user.css" rel="stylesheet">
</head>
<body>
<?php include('header.php'); ?>

<div class="d-flex" style="margin-top: 56px;">
  <aside class="offcanvas-md offcanvas-start bg-light sticky-top" tabindex="-1" id="leftbar">
    <?php include('leftbar.php'); ?>
  </aside>

  <main class="flex-grow-1 p-4" style="min-height: calc(100vh - 56px);">
    <div class="form-container">
      <div class="form-header">
        <h2 class="mb-0"><i class="fas fa-ticket me-2 text-primary"></i> Crear Nuevo Ticket</h2>
        <p class="text-muted mb-0">Complete todos los campos para registrar un nuevo ticket</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center">
          <i class="fas fa-exclamation-circle me-2"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="mb-4">
          <label for="subject" class="form-label fw-bold">Asunto del Ticket</label>
          <input type="text" class="form-control form-control-lg" id="subject" name="subject" required
                 placeholder="Ej: Problema con el sistema de aire acondicionado"
                 value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>" />
          <div class="form-text">Describa brevemente el problema o solicitud</div>
        </div>

        <div class="mb-4">
          <label for="description" class="form-label fw-bold">Descripción Detallada</label>
          <textarea class="form-control" id="description" name="description" rows="5" required
                    placeholder="Describa el problema con el mayor detalle posible..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
          <div class="form-text">Incluya pasos para reproducir el problema si aplica</div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label for="priority" class="form-label fw-bold">Prioridad</label>
            <select class="form-select form-select-lg" id="priority" name="priority" required>
              <option value="" disabled <?= empty($_POST['priority']) ? 'selected' : '' ?>>Seleccione prioridad</option>
              <?php foreach ($prioridades as $prio): ?>
                <option value="<?= htmlspecialchars($prio) ?>" <?= (isset($_POST['priority']) && $_POST['priority'] === $prio) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($prio) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Seleccione la urgencia de su solicitud</div>
          </div>
          
          <div class="col-md-6">
            <label for="edificio_id" class="form-label fw-bold">Edificio/Localización</label>
            <select class="form-select form-select-lg" id="edificio_id" name="edificio_id" required>
              <option value="" disabled <?= empty($_POST['edificio_id']) ? 'selected' : '' ?>>Seleccione un edificio</option>
              <?php foreach ($edificios as $edificio): ?>
                <option value="<?= $edificio['id'] ?>" <?= (isset($_POST['edificio_id']) && $_POST['edificio_id'] == $edificio['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($edificio['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Ubicación donde se presenta el problema</div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
          <button type="reset" class="btn btn-outline-secondary">
            <i class="fas fa-eraser me-1"></i> Limpiar
          </button>
          <button type="submit" class="btn btn-submit text-white">
            <i class="fas fa-paper-plane me-1"></i> Enviar Ticket
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<!-- Toast de éxito -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
  <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive"
       aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i>
        <span>Ticket creado exitosamente. Redirigiendo...</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($show_success_toast): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toastElement = document.getElementById('successToast');
      const toast = new bootstrap.Toast(toastElement, {
        animation: true,
        autohide: true,
        delay: 3000
      });
      toast.show();

      setTimeout(() => {
        window.location.href = 'dashboard.php';
      }, 3000);
    });
  </script>
<?php endif; ?>
</body>
</html>
