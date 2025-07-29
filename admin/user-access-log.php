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
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/roles-layouts/user-access-log.css" rel="stylesheet">

  <style>
    /* Sidebar fijo con altura completa menos header */
    #leftbar {
      position: fixed;
      top: 56px;
      /* altura del header fijo */
      left: 0;
      width: 250px;
      height: calc(100vh - 56px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    /* Para el contenido principal, margen izquierdo igual al sidebar para evitar superposición */
    #main-content {
      margin-left: 250px;
      padding-top: 70px;
      /* espacio para header */
      min-height: 100vh;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Botón toggle para móviles -->
      <button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#leftbar" aria-controls="leftbar">
        <i class="fas fa-bars"></i>
      </button>

      <!-- Sidebar -->
      <div class="col-lg-2 p-0">
        <div class="offcanvas-md offcanvas-start bg-light" tabindex="-1" id="leftbar">
          <?php include("leftbar.php"); ?>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="col-lg-10 p-4 mt-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-sign-in-alt me-1"></i>Registro de
              Accesos</li>
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
                  </tr>
                </thead>
                <tbody>
                  <?php
                  function detectarNavegador($userAgent)
                  {
                    if (stripos($userAgent, 'Edg') !== false)
                      return ['Edge', 'fa-brands fa-edge'];
                    if (stripos($userAgent, 'OPR') !== false || stripos($userAgent, 'Opera') !== false)
                      return ['Opera', 'fa-brands fa-opera'];
                    if (stripos($userAgent, 'Chrome') !== false)
                      return ['Chrome', 'fa-brands fa-chrome'];
                    if (stripos($userAgent, 'Firefox') !== false)
                      return ['Firefox', 'fa-brands fa-firefox'];
                    if (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false)
                      return ['Safari', 'fa-brands fa-safari'];
                    if (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false)
                      return ['Internet Explorer', 'fa-solid fa-globe'];
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
                    </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div> <!-- Fin contenido -->
    </div> <!-- Fin row -->
  </div> <!-- Fin container -->

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
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

      $('#sidebarToggle').click(function () {
        $('#leftbar').toggleClass('show');
      });
    });
  </script>
</body>

</html>