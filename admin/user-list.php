<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("admin");
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Admin | Lista de Usuarios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap y FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3a0ca3;
    }

    body {
      background-color: #f8f9fa;
    }

    .table-card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      overflow: hidden;
    }

    .table thead {
      background-color: var(--primary-color);
      color: white;
    }

    .badge-role {
      font-size: 0.75rem;
      font-weight: 500;
      border-radius: 50px;
      padding: 0.35rem 0.75rem;
      text-transform: capitalize;
    }

    .badge-admin {
      background-color: #f72585;
      color: white;
    }

    .badge-tech {
      background-color: #4cc9f0;
      color: #111;
    }

    .badge-user {
      background-color: #7209b7;
      color: white;
    }

    .avatar-sm {
      width: 35px;
      height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background-color: #dee2e6;
    }

    .breadcrumb {
      background-color: transparent;
      padding-left: 0;
    }

    .breadcrumb-item.active {
      color: var(--primary-color);
      font-weight: 500;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Botón de menú en móviles -->
      <button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar">
        <i class="fas fa-bars"></i>
      </button>

      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse offcanvas-md offcanvas-start" id="leftbar">
        <?php include("leftbar.php"); ?>
      </nav>

      <!-- Contenido principal -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 mt-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-users me-1"></i>Lista de Usuarios</li>
          </ol>
        </nav>

        <!-- Título y botón -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="h4 mb-0"><i class="fas fa-users me-2 text-primary"></i>Lista de Usuarios</h2>
            <p class="text-muted mb-0">Gestión completa de usuarios del sistema</p>
          </div>
          <a href="home.php" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left me-1"></i> Volver
          </a>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card table-card">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Todos los usuarios registrados</h5>
            <span class="badge bg-light text-dark">
              <i class="fas fa-database me-1"></i>
              <?php echo $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(); ?> registros
            </span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <?php
              $cnt = 1;
              $stmt = $pdo->query("SELECT u.id, u.name, e.name AS edificio_name, u.email, u.role, u.posting_date 
                                    FROM user u
                                    LEFT JOIN edificios e ON u.edificio_id = e.id 
                                    ORDER BY u.posting_date DESC");
              ?>
              <table id="usersTable" class="table table-hover align-middle w-100">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Edificio</th>
                    <th>Rol</th>
                    <th>Registro</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $badgeClass = match(strtolower($row['role'])) {
                      'admin' => 'badge-admin',
                      'tecnico' => 'badge-tech',
                      default => 'badge-user',
                    };
                    echo "<tr>
                            <td>{$cnt}</td>
                            <td>
                              <div class='d-flex align-items-center'>
                                <div class='avatar-sm me-2'><i class='fas fa-user-circle text-muted'></i></div>
                                <div>
                                  <div class='fw-semibold'>" . htmlspecialchars($row['name']) . "</div>
                                  <small class='text-muted'>ID: " . htmlspecialchars($row['id']) . "</small>
                                </div>
                              </div>
                            </td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . (htmlspecialchars($row['edificio_name']) ?: '<span class="text-muted">N/A</span>') . "</td>
                            <td><span class='badge $badgeClass badge-role'>" . htmlspecialchars($row['role']) . "</span></td>
                            <td>" . date('d/m/Y', strtotime($row['posting_date'])) . "</td>
                          </tr>";
                    $cnt++;
                  } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#usersTable').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        responsive: true,
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excel',
            text: '<i class="fas fa-file-excel me-1"></i> Exportar Excel',
            className: 'btn btn-success btn-sm'
          },
          {
            extend: 'print',
            text: '<i class="fas fa-print me-1"></i> Imprimir',
            className: 'btn btn-secondary btn-sm'
          }
        ]
      });
    });
  </script>
</body>
</html>
