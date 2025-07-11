<?php
session_start();
include("dbconnection.php");
include("checklogin.php");
check_login("admin");

// Obtener filtro desde GET
$filtro = $_GET['filtro'] ?? 'todos';


// Guardar respuesta del admin sin sobrescribir
if (isset($_POST['update'])) {
  $adminremark = $_POST['aremark'];
  $fid = $_POST['frm_id'];

  $stmt = $pdo->prepare("SELECT admin_remark FROM ticket WHERE id = :id");
  $stmt->execute(['id' => $fid]);
  $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
  $oldRemark = $ticket['admin_remark'];

  $newRemark = $oldRemark . "\n[" . date("Y-m-d H:i") . "] Admin: " . $adminremark;

  $stmt = $pdo->prepare("UPDATE ticket SET admin_remark = :remark, status = 'Cerrado' WHERE id = :id");
  $stmt->execute(['remark' => $newRemark, 'id' => $fid]);

  echo '<script>alert("Ticket ha sido actualizado correctamente"); location.replace(document.referrer)</script>';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin | Gestión de Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../assets/css/style-bootstrap5.css" rel="stylesheet" />
  <link href="../styles/admin.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>
  
  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
        <?php include("leftbar.php"); ?>
      </nav>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="mb-0 fw-bold mt-4">Gestión de Tickets</h2>
            <p class="text-muted mb-0">Administración y seguimiento de tickets del sistema</p>
          </div>
        </div>

        <!-- Filtros -->
        <div class="filter-container">
          <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
              <h5 class="mb-0">Filtrar tickets</h5>
            </div>
            <div class="col-md-6">
              <form method="GET" class="d-flex align-items-center">
                <label for="filtro" class="me-2 mb-0">Edificio:</label>
                <select name="filtro" id="filtro" class="form-select" onchange="this.form.submit()">
                  <option value="todos" <?= $filtro == 'todos' ? 'selected' : '' ?>>Todos los edificios</option>
                  <option value="Santa Esmeralda" <?= $filtro == 'Santa Esmeralda' ? 'selected' : '' ?>>Santa Esmeralda</option>
                  <option value="Palmira" <?= $filtro == 'Palmira' ? 'selected' : '' ?>>Palmira</option>
                </select>
              </form>
            </div>
          </div>
        </div>

        <!-- Lista de Tickets -->
        <div class="accordion" id="ticketsAccordion">
          <?php
          // Consulta con JOIN para incluir nombre del edificio
          if ($filtro !== 'todos') {
            $stmt = $pdo->prepare("
              SELECT t.*, e.name AS edificio_nombre
              FROM ticket t
              LEFT JOIN edificios e ON t.edificio_id = e.id
              WHERE e.name = :nombreEdificio
              ORDER BY t.id DESC
            ");
            $stmt->execute(['nombreEdificio' => $filtro]);
          } else {
            $stmt = $pdo->query("
              SELECT t.*, e.name AS edificio_nombre
              FROM ticket t
              LEFT JOIN edificios e ON t.edificio_id = e.id
              ORDER BY t.id DESC
            ");
          }

          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $estado = strtolower($row['status']);
            $badgeClass = 'status-open';
            $estadoTexto = 'Abierto';

            if ($estado === 'en proceso') {
              $badgeClass = 'status-progress';
              $estadoTexto = 'En Proceso';
            } elseif ($estado === 'cerrado') {
              $badgeClass = 'status-closed';
              $estadoTexto = 'Cerrado';
            }

            $edificio = htmlspecialchars($row['edificio_nombre'] ?? 'Sin edificio');
            $fecha = date('d/m/Y H:i', strtotime($row['posting_date']));
          ?>
            <div class="ticket-card">
              <div class="ticket-header accordion-header <?= $estado === 'cerrado' ? 'bg-light' : '' ?>" id="heading<?= $id ?>">
                <button class="accordion-button <?= $estado === 'cerrado' ? 'bg-light' : '' ?> collapsed d-flex justify-content-between align-items-center" 
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $id ?>" 
                        aria-expanded="false" aria-controls="collapse<?= $id ?>">
                  <div class="d-flex flex-column flex-md-row align-items-md-center w-100">
                    <div class="me-md-4 mb-2 mb-md-0">
                      <span class="badge building-badge me-2"><?= $edificio ?></span>
                      <span class="badge <?= $badgeClass ?>"><?= $estadoTexto ?></span>
                    </div>
                    <div class="flex-grow-1">
                      <h5 class="mb-1"><?= htmlspecialchars($row['subject']) ?></h5>
                      <div class="d-flex flex-wrap text-muted small">
                        <span class="me-3"><i class="far fa-id-card me-1"></i> #<?= htmlspecialchars($row['ticket_id']) ?></span>
                        <span><i class="far fa-clock me-1"></i> <?= $fecha ?></span>
                      </div>
                    </div>
                  </div>
                </button>
              </div>

              <div id="collapse<?= $id ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $id ?>" 
                   data-bs-parent="#ticketsAccordion">
                <div class="ticket-body">
                  <!-- Mensaje del usuario -->
                  <div class="message-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-user-circle me-2"></i>Mensaje del usuario
                      </h6>
                    </div>
                    <div class="text-muted">
                      <?= nl2br(htmlspecialchars($row['ticket'])) ?>
                    </div>
                  </div>

                  <!-- Respuesta del técnico -->
                  <?php if (!empty($row['tech_remark'])): ?>
                    <div class="message-box" style="border-left-color: var(--warning-color);">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold text-warning">
                          <i class="fas fa-user-cog me-2"></i>Respuesta del técnico
                        </h6>
                      </div>
                      <div class="text-muted">
                        <?= nl2br(htmlspecialchars($row['tech_remark'])) ?>
                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Respuesta del administrador -->
                  <?php if (!empty($row['admin_remark'])): ?>
                    <div class="message-box" style="border-left-color: var(--primary-color);">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold text-primary">
                          <i class="fas fa-user-shield me-2"></i>Respuesta del administrador
                        </h6>
                      </div>
                      <div class="text-muted">
                        <?= nl2br(htmlspecialchars($row['admin_remark'])) ?>
                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Formulario de respuesta -->
                  <form method="post" class="needs-validation mt-4" novalidate>
                    <div class="mb-3">
                      <label for="aremark<?= $id ?>" class="form-label fw-bold">
                        <i class="fas fa-reply me-1"></i>Agregar respuesta
                      </label>
                      <textarea class="form-control" id="aremark<?= $id ?>" name="aremark" rows="4" 
                                placeholder="Escribe tu respuesta aquí..." required></textarea>
                      <div class="invalid-feedback">Por favor ingrese un comentario.</div>
                    </div>
                    <input type="hidden" name="frm_id" value="<?= $id ?>" />
                    <div class="d-flex justify-content-end">
                      <button type="submit" name="update" class="btn btn-primary px-4">
                        <i class="fas fa-paper-plane me-2"></i>Enviar respuesta
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>
</body>

</html>