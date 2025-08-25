<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SPE Soporte Técnico Informática</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Catamaran:100,200,300,400,500,600,700,800,900" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900" rel="stylesheet">

  <!-- Estilos personalizados -->
   <link href="assets/css/index.css" rel="stylesheet">
   <style>
     header.masthead {
      background: url('assets/img/index.jpg') no-repeat center center;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
    }
    
    /* Mejoras específicas para los botones del navbar */
    .navbar .btn-outline-light {
      border-width: 2px;
      font-weight: 500;
      transition: all 0.3s ease;
      /* Cambiamos el color de la letra a azul oscuro */
      color: #0d6efd !important;
      background-color: white;
    }
    
    .navbar .btn-outline-light:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      /* Color de letra al hacer hover */
      color: black !important;
    }
    
    .navbar .btn-primary {
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .navbar .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    /* Separación entre botones */
    .navbar .btn {
      margin-left: 0.5rem;
    }
   </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-info fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">INFORMÁTICA</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ms-auto">
          <?php if (!$isLoggedIn): ?>
            <li class="nav-item">
              <a class="nav-link btn btn-outline-light" href="registration.php">Regístrate</a>
            </li>
            <li class="nav-item">
              <a class="nav-link btn btn-primary text-white" href="login1.php">Acceder</a>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login1.php">Ir al Panel</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Encabezado principal -->
  <header class="masthead text-white text-center">
    <div class="container masthead-content">
      <h1 class="masthead-heading fw-bold">Sistema de Soporte de Tickets</h1>
      <?php if (!$isLoggedIn): ?>
        <a href="registration.php" class="btn btn-primary btn-lg rounded-pill mt-4">Registro de Usuario</a>
      <?php else: ?>
        <a href="login1.php" class="btn btn-success btn-lg rounded-pill mt-4">Ir al Panel</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Footer -->
  <footer class="py-4 bg-dark">
    <div class="container text-center">
      <p class="text-white small m-0">&copy; Secretaría de Planificación Estratégica 2025</p>
    </div>
  </footer>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>