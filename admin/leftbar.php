<!-- Botón de menú para móviles -->
<button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar"
  aria-controls="leftbar">
  <i class="fas fa-bars me-1"></i> Menú
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div class="offcanvas-md offcanvas-start bg-white shadow-sm position-fixed h-100" tabindex="-1" id="leftbar" style="width: 280px; z-index: 1040;">
  <div class="offcanvas-header border-bottom d-md-none">
    <h5 class="offcanvas-title fw-bold">Menú Administrador</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0">
    <!-- Perfil -->
    <div class="text-center py-4 border-bottom bg-light">
      <img src="../assets/img/Logo-Gobierno_small.png" alt="Perfil Admin" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: contain;">
      <div class="fw-semibold text-muted">Bienvenid@</div>
      <div class="text-primary fw-bold">Admin</div>
    </div>

    <!-- Navegación -->
    <div class="flex-grow-1 overflow-auto">
      <div class="px-3 pt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-uppercase fw-bold small text-muted">Navegación</span>
          <a href="#" onclick="location.reload()" class="text-decoration-none text-secondary" title="Actualizar">
            <i class="fas fa-sync-alt fa-sm"></i>
          </a>
        </div>

        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <a href="home.php" class="nav-link rounded-2 <?= ($page == 'home') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fa-solid fa-chart-column me-2"></i> Dashboard
            </a>
          </li>
          
          <li class="nav-item">
            <a href="manage-tickets.php" class="nav-link rounded-2 <?= ($page == 'manage-tickets') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-ticket-alt me-2"></i> Gestionar Tickets
            </a>
          </li>
          
          <li class="nav-item">
            <a href="chat-list-admin.php" class="nav-link rounded-2 <?= ($page == 'manage-quotes') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-tasks me-2"></i> Gestionar Servicios
            </a>
          </li>
          
          <li class="nav-item">
            <a href="user-access-log.php" class="nav-link rounded-2 <?= ($page == 'user-access-log') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-users me-2"></i> Registro de Acceso
            </a>
          </li>
        </ul>
      </div>
      
      <div class="px-3 pt-3 mt-2 border-top">
        <div class="text-uppercase fw-bold small text-muted mb-3">Administración</div>
        
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <a href="manage-users.php" class="nav-link rounded-2 <?= ($page == 'manage-users') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-users-cog me-2"></i> Gestionar Usuarios
            </a>
          </li>
          
          <li class="nav-item">
            <a href="user-list.php" class="nav-link rounded-2 <?= ($page == 'user-list') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-book me-2"></i> Lista de Usuarios
            </a>
          </li>
          
          <li class="nav-item">
            <a href="manage-passwords.php" class="nav-link rounded-2 <?= ($page == 'manage-passwords') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-lock me-2"></i> Permisos Contraseñas
            </a>
          </li>
          
          <li class="nav-item">
            <a href="password-generator/generator.php" class="nav-link rounded-2 <?= ($page == 'change-password') ? 'active bg-primary text-white' : 'text-dark'; ?>">
              <i class="fas fa-file-alt me-2"></i> Cambiar Contraseña
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Footer del sidebar -->
    <div class="p-3 border-top bg-light">
      <div class="text-center small text-muted">
        Sistema Administrativo v1.0
      </div>
    </div>
  </div>
</div>