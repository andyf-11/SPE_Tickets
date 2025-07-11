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

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet" />
  <link href="../styles/admin.css" rel="stylesheet"/>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3a0ca3;
      --light-bg: #f8f9fa;
    }
    
    body {
      background-color: var(--light-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .sidebar {
      height: calc(100vh - 56px);
      overflow-y: auto;
      background: white;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    
    .main-content {
      padding: 2rem;
    }
    
    .card-header-custom {
      background: white;
      border-bottom: 1px solid rgba(0,0,0,0.05);
      padding: 1.25rem 1.5rem;
    }
    
    .table-card {
      border: none;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      border-radius: 10px;
      overflow: hidden;
    }
    
    .table thead {
      background-color: var(--primary-color);
      color: white;
    }
    
    .table th {
      font-weight: 500;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    
    .table-hover tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    .badge-role {
      padding: 0.35rem 0.65rem;
      font-weight: 500;
      font-size: 0.75rem;
      border-radius: 50px;
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
    
    .btn-primary-custom {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 50px;
      padding: 0.5rem 1.25rem;
    }
    
    .btn-primary-custom:hover {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
      margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
      border-radius: 50px;
      padding: 0.25rem 0.75rem;
      border: 1px solid #dee2e6;
    }
    
    .breadcrumb {
      background-color: transparent;
      padding: 0.5rem 0;
      font-size: 0.9rem;
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
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 p-0 sidebar">
        <?php include("leftbar.php"); ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-md-9 col-lg-10 main-content">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-users me-1"></i>Lista de Usuarios</li>
          </ol>
        </nav>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="h4 mb-0"><i class="fas fa-users me-2 text-primary"></i>Lista de Usuarios</h2>
            <p class="text-muted mb-0">Gestión completa de usuarios del sistema</p>
          </div>
          <a href="home.php" class="btn btn-outline-secondary">
            <i class="fas fa-chevron-left me-1"></i> Volver
          </a>
        </div>

        <!-- Tarjeta de tabla -->
        <div class="card table-card">
          <div class="card-header card-header-custom">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Todos los usuarios registrados</h5>
              <div>
                <span class="badge bg-light text-dark">
                  <i class="fas fa-database me-1"></i>
                  <?php 
                    $count = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
                    echo $count . ' registros';
                  ?>
                </span>
              </div>
            </div>
          </div>
          
          <div class="card-body">
            <div class="table-responsive">
              <?php
              $cnt = 1;
              $query = "SELECT u.id, u.name, e.name AS edificio_name, u.email, u.role, u.posting_date 
                FROM user u
                LEFT JOIN edificios e ON u.edificio_id = e.id 
                ORDER BY u.posting_date DESC";
              $stmt = $pdo->query($query);
              ?>
              <table id="usersTable" class="table table-hover align-middle" style="width:100%">
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
                  <?php
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Clase para el badge según el rol
                    $badgeClass = '';
                    switch(strtolower($row['role'])) {
                      case 'admin': $badgeClass = 'badge-admin'; break;
                      case 'tecnico': $badgeClass = 'badge-tech'; break;
                      default: $badgeClass = 'badge-user';
                    }
                    
                    echo "<tr>
                            <td class='text-center'>{$cnt}</td>
                            <td>
                              <div class='d-flex align-items-center'>
                                <div class='avatar-sm me-2'>
                                  <i class='fas fa-user-circle fa-lg text-muted'></i>
                                </div>
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
                  }
                  ?>
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
    $(document).ready(function() {
      $('#usersTable').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        lengthMenu: [10, 25, 50, 100],
        dom: '<"top"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"bottom"<"row"<"col-md-6"i><"col-md-6"p>>>',
        buttons: [
          {
            extend: 'excel',
            text: '<i class="fas fa-file-excel me-1"></i> Excel',
            className: 'btn btn-success btn-sm'
          },
          {
            extend: 'print',
            text: '<i class="fas fa-print me-1"></i> Imprimir',
            className: 'btn btn-secondary btn-sm'
          }
        ],
        initComplete: function() {
          $('.dt-buttons').addClass('btn-group');
        }
      });
    });
  </script>
</body>

</html>