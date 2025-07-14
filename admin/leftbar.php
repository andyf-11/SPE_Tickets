<!-- Botón de menú para móviles -->
<button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar"
  aria-controls="leftbar">
  <i class="fas fa-bars"></i>
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div class="offcanvas-md offcanvas-start bg-light border-end min-vh-10 d-none d-md-block" id="leftbar" style="margin-top: 56px;">
  <div class="offcanvas-header d-md-none">
    <h5 class="offcanvas-title">Menú Administrador</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0">
    <!-- Perfil -->
    <div class="text-center py-4 border-bottom">
      <img src="../assets/img/Logo-Gobierno_small.png" alt="Perfil Admin" class="img-fluid  mb-2"
        style="width: 80px; height: 80px;">
      <div class="fw-semibold">Bienvenid@</div>
      <div class="text-primary fw-bold">Admin</div>
    </div>

    <!-- Navegación -->
    <div class="px-3 pt-3 flex-grow-1 overflow-auto">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-uppercase fw-bold small">Opciones</span>
        <a href="#" onclick="location.reload()" class="text-decoration-none text-secondary" title="Actualizar">
          <i class="fas fa-sync-alt"></i>
        </a>
      </div>

      <ul class="nav flex-column">
        <li class="nav-item mb-1">
          <a href="home.php" class="nav-link <?= ($page == 'home') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fa-solid fa-chart-column me-2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="manage-tickets.php"
            class="nav-link <?= ($page == 'manage-tickets') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fas fa-ticket-alt me-2"></i> Gestionar Ticket
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="chat-list-admin.php"
            class="nav-link <?= ($page == 'manage-quotes') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fas fa-tasks me-2"></i> Gestionar Servicios
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="user-access-log.php"
            class="nav-link <?= ($page == 'user-access-log') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fas fa-users me-2"></i> Registro de Acceso
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="manage-users.php"
            class="nav-link <?= ($page == 'manage-users') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fas fa-users-cog me-2"></i> Gestionar Usuarios
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="user-list.php"
            class="nav-link <?= ($page == 'user-list') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fas fa-book me-2"></i> Lista de Usuarios
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="manage-passwords.php"
            class="nav-link <?= ($page == 'manage-passwords') ? 'active' : 'text-dark'; ?>">
            <i class="fas fa-lock me-2"></i> Permisos Contraseñas
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="password-generator/generator.php"
            class="nav-link <?= ($page == 'change-password') ? 'active fw-bold text-primary' : 'text-dark'; ?>">
            <i class="fas fa-file-alt me-2"></i> Cambiar Contraseña
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>