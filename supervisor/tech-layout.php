<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("supervisor");

$stmt = $pdo->prepare("
  SELECT u.id, u.name, u.mobile, u.email, COUNT(t.id) AS tickets_asignados
  FROM user u
  LEFT JOIN ticket t ON u.id = t.assigned_to
  WHERE u.role = 'tecnico'
  GROUP BY u.id, u.name, u.mobile, u.email
");
$stmt->execute();
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Plantilla de Técnicos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      font-weight: 300;
      background-color: #f8f9fa;
    }

    .header-section {
      background: #fff;
      padding: 1.5rem;
      border-radius: 0.5rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .tech-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #0d6efd;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-weight: 500;
    }

    .table-responsive {
      border-radius: 0.5rem;
      overflow: hidden;
    }

    .table thead {
      background-color: #f8f9fa;
    }

    .table th {
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      color: #6c757d;
    }

    .badge-count {
      font-size: 0.85rem;
      font-weight: 500;
      min-width: 30px;
      display: inline-block;
      text-align: center;
    }

    .btn-action {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    @media (min-width: 768px) {
      #leftbar {
        position: fixed;
        top: 56px;
        left: 0;
        height: calc(100vh - 52px);
        z-index: 1030;
        font-weight: 400;
      }

      main {
        margin-left: 250px;
      }
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <!-- Sidebar -->
  <?php include("leftbar.php"); ?>

  <!-- Contenido principal -->
  <main class="main-content mt-5">
    <div class="container-fluid py-4">
      <div class="header-section">
        <div>
          <h2 class="fw-bold mb-1"><i class="fa-solid fa-user-gear me-2"></i>Plantilla de Técnicos</h2>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="s_dashboard.php" class="text-decoration-none">Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Técnicos</li>
            </ol>
          </nav>
        </div>
      
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Técnico</th>
                  <th>Contacto</th>
                  <th class="text-center">Tickets</th>
                  <th class="text-end pe-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tecnicos as $tecnico): ?>
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center">
                        <div class="tech-avatar">
                          <?= strtoupper(substr($tecnico['name'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="fw-bold"><?= htmlspecialchars($tecnico['name']) ?></div>
                          <small class="text-muted"><?= htmlspecialchars($tecnico['email']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span><?= htmlspecialchars($tecnico['mobile']) ?></span>
                        <small class="text-muted">Teléfono</small>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-primary bg-opacity-10 text-primary badge-count py-2 px-2">
                        <?= htmlspecialchars($tecnico['tickets_asignados']) ?>
                      </span>
                    </td>
                    <td class="text-end pe-4">
                      <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-sm btn-action btn-outline-primary rounded-circle" data-bs-toggle="modal"
                          data-bs-target="#modal-tech" data-id="<?= $tecnico_id ?>">
                          <i class="fas fa-eye fa-xs"></i>
                        </button>
                        <button class="btn btn-sm btn-action btn-outline-secondary rounded-circle">
                          <i class="fas fa-edit fa-xs"></i>
                        </button>
                        <button class="btn btn-sm btn-action btn-outline-danger rounded-circle">
                          <i class="fas fa-trash-alt fa-xs"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/SPE_Soporte_Tickets/supervisor/tech-data/tech-info.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    var modalTecnico = document.getElementById('modal-tech');

modalTecnico.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget; // botón que abrió el modal
  var tecnicoId = button.getAttribute('data-id');

  fetch('/supervisor/tech-data/get_tecnico_info.php?id=' + tecnicoId)
    .then(res => res.json())
    .then(data => {
      if(data.error){
        // Opcional: muestra mensaje o limpia campos
        modalTecnico.querySelector('#nombreTecnico').textContent = 'No disponible';
        modalTecnico.querySelector('#emailTecnico').textContent = '-';
        modalTecnico.querySelector('#telefonoTecnico').textContent = '-';
        modalTecnico.querySelector('#edificioTecnico').textContent = '-';
        return;
      }
      modalTecnico.querySelector('#nombreTecnico').textContent = data.name || '-';
      modalTecnico.querySelector('#emailTecnico').textContent = data.email || '-';
      modalTecnico.querySelector('#telefonoTecnico').textContent = data.mobile || '-';
      modalTecnico.querySelector('#edificioTecnico').textContent = data.edificio_id || '-';
    })
    .catch(err => {
      console.error('Error al cargar técnico:', err);
    });
});

</script>

</body>

</html>