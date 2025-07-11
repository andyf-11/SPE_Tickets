<?php
session_start();
require_once("dbconnection.php");
require("checklogin.php");
check_login("admin");
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Admin | Registro de Acceso</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <!-- Estilos personalizados -->
  <link href="../assets/css/style-bootstrap5.css" rel="stylesheet">

  <style>
    :root {
      --sidebar-width: 280px;
      --header-height: 56px;
    }
    
    body {
      background-color: #f8f9fa;
    }
    
    #leftbar {
      height: calc(100vh - var(--header-height));
      overflow-y: auto;
      width: var(--sidebar-width);
      position: fixed;
      background: #2c3e50;
      color: white;
      transition: all 0.3s;
    }
    
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 20px;
      min-height: calc(100vh - var(--header-height));
    }
    
    .browser-icon {
      font-size: 1.1rem;
      margin-right: 5px;
    }
    
    .card-shadow {
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      border: none;
      border-radius: 0.35rem;
    }
    
    .table-card {
      padding: 1.25rem;
    }
    
    .table-header {
      background-color: #4e73df;
      color: white;
    }
    
    .table th {
      white-space: nowrap;
      position: relative;
    }
    
    .table-responsive {
      border-radius: 0.35rem;
      overflow: hidden;
    }
    
    .breadcrumb {
      background-color: transparent;
      padding: 0.75rem 0;
      margin-bottom: 1.5rem;
    }
    
    .page-title {
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }
    
    @media (max-width: 992px) {
      #leftbar {
        margin-left: -280px;
      }
      
      .main-content {
        margin-left: 0;
      }
      
      #leftbar.active {
        margin-left: 0;
      }
      
      .main-content.active {
        margin-left: 280px;
      }
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="d-flex">
    <!-- Leftbar -->
    <div id="leftbar">
      <?php include("leftbar.php"); ?>
    </div>

    <!-- Contenido principal -->
    <main class="main-content flex-grow-1">
      <div class="container-fluid">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-sign-in-alt me-1"></i>Registro de Accesos</li>
          </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="page-title"><i class="fas fa-sign-in-alt me-2"></i>Registro de Accesos de Usuario</h1>
          <button class="btn btn-primary d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
          </button>
        </div>

        <div class="card card-shadow">
          <div class="card-body table-card">
            <div class="table-responsive">
              <table id="accessLogTable" class="table table-hover align-middle" style="width:100%">
                <thead class="table-header">
                  <tr>
                    <th>#ID</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Fecha y Hora</th>
                    <th>IP</th>
                    <th>SO</th>
                    <th>Navegador</th>
                    <th>Ciudad</th>
                    <th>País</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  function detectarNavegador($userAgent)
                  {
                    if (stripos($userAgent, 'Edg') !== false) return ['Edge', 'fa-brands fa-edge'];
                    if (stripos($userAgent, 'OPR') !== false || stripos($userAgent, 'Opera') !== false) return ['Opera', 'fa-brands fa-opera'];
                    if (stripos($userAgent, 'Chrome') !== false) return ['Chrome', 'fa-brands fa-chrome'];
                    if (stripos($userAgent, 'Firefox') !== false) return ['Firefox', 'fa-brands fa-firefox'];
                    if (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) return ['Safari', 'fa-brands fa-safari'];
                    if (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false) return ['Internet Explorer', 'fa-solid fa-globe'];
                    return ['Desconocido', 'fa-solid fa-question'];
                  }

                  $stmt = $pdo->query("SELECT * FROM usercheck ORDER BY logindatetime DESC");
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    [$navegadorNombre, $navegadorIcono] = detectarNavegador($row['user_agent'] ?? '');

                    echo "<tr>
                      <td><span class='badge bg-secondary'>{$row['user_id']}</span></td>
                      <td><strong>" . htmlspecialchars($row['username']) . "</strong></td>
                      <td><a href='mailto:" . htmlspecialchars($row['email']) . "' class='text-primary'>" . htmlspecialchars($row['email']) . "</a></td>
                      <td><span class='text-muted small'>" . date('d/m/Y H:i', strtotime($row['logindatetime'])) . "</span></td>
                      <td><span class='badge bg-light text-dark'>" . htmlspecialchars($row['ip']) . "</span></td>
                      <td><span class='text-muted'>" . htmlspecialchars($row['os']) . "</span></td>
                      <td><i class='browser-icon $navegadorIcono'></i>" . htmlspecialchars($navegadorNombre) . "</td>
                      <td>" . htmlspecialchars($row['city']) . "</td>
                      <td><span class='badge bg-info text-dark'>" . htmlspecialchars($row['country']) . "</span></td>
                    </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
      // Inicializar DataTable
      $('#accessLogTable').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        responsive: true,
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 25,
        order: [[3, 'desc']]
      });
      
      // Toggle sidebar en móvil
      $('#sidebarToggle').click(function() {
        $('#leftbar').toggleClass('active');
        $('.main-content').toggleClass('active');
      });
    });
  </script>
</body>

</html>
