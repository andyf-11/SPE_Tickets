<!-- Sidebar Estilizado -->
<div class="offcanvas offcanvas-start d-lg-block bg-white shadow-sm" tabindex="-1" id="sidebar" style="width: 280px;">
  <div class="d-flex flex-column h-100">
    <!-- Header del Sidebar -->
    <div class="offcanvas-header d-lg-none border-bottom bg-primary text-white">
      <h5 class="offcanvas-title fw-bold">Menú Administrador</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    
    <!-- Contenido del Sidebar -->
    <div class="offcanvas-body p-3 flex-grow-1">
      <!-- Perfil con mejor estilo -->
      <div class="text-center py-4 border-bottom">
        <img src="../assets/img/Logo-Gobierno_small.png" alt="Perfil Admin" 
             class="rounded-circle mb-2 border border-primary" width="80" height="80" style="object-fit: cover;">
        <div class="fw-semibold text-muted">Bienvenid@</div>
        <h5 class="text-primary fw-bold mb-0">Admin</h5>
      </div>
      
      <!-- Menú Principal mejorado -->
      <ul class="nav nav-pills flex-column mb-3 mt-3">
        <li class="nav-item mb-2">
          <a href="home.php" class="nav-link <?= ($page == 'home') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-chart-line me-2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="manage-tickets.php" class="nav-link <?= ($page == 'manage-tickets') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-ticket-alt me-2"></i> Gestionar Tickets
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="chat-list-admin.php" class="nav-link <?= ($page == 'manage-quotes') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-tasks me-2"></i> Gestionar Servicios
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="user-access-log.php" class="nav-link <?= ($page == 'user-access-log') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-users me-2"></i> Registro de Acceso
          </a>
        </li>
      </ul>
      
      <hr class="my-2">
      
      <!-- Menú Administración -->
      <div class="small text-uppercase fw-bold text-muted mb-2 ps-3">Administración</div>
      <ul class="nav nav-pills flex-column mb-3">
        <li class="nav-item mb-2">
          <a href="manage-users.php" class="nav-link <?= ($page == 'manage-users') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-users-cog me-2"></i> Gestionar Usuarios
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="user-list.php" class="nav-link <?= ($page == 'user-list') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-book me-2"></i> Lista de Usuarios
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="manage-passwords.php" class="nav-link <?= ($page == 'manage-passwords') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-lock me-2"></i> Permisos Contraseñas
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="password-generator/generator.php" class="nav-link <?= ($page == 'change-password') ? 'active bg-primary' : 'text-dark'; ?> rounded-pill">
            <i class="fas fa-file-alt me-2"></i> Cambiar Contraseña
          </a>
        </li>
      </ul>
    </div>
    
    <!-- Footer del Sidebar -->
    <div class="p-3 border-top text-center small text-muted bg-light">
      Sistema Administrativo v1.0
    </div>
  </div>
</div>

<!-- Botón para móviles mejorado -->
<button class="btn btn-primary d-lg-none position-fixed rounded-circle shadow" 
        style="bottom: 20px; left: 20px; z-index: 1050; width: 50px; height: 50px;"
        data-bs-toggle="offcanvas" data-bs-target="#sidebar">
  <i class="fas fa-bars"></i>
</button>