<?php $page = ''?>
<!-- Botón de menú para móviles -->
<button class="btn btn-primary d-md-none m-3 rounded-pill shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar" aria-controls="leftbar">
  <i class="fas fa-bars me-2"></i> Menú Admin
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div class="offcanvas-md offcanvas-start bg-white shadow-sm" tabindex="-1" id="leftbar" style="--sidebar-accent: #4e73df; --sidebar-hover: #f8f9fc; width: 250px;">
  <div class="offcanvas-header d-md-none border-bottom">
    <h5 class="offcanvas-title fw-bold text-primary">Menú Administrador</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0 h-100">
    <!-- Perfil mejorado -->
    <div class="text-center py-4 border-bottom bg-gradient-light" style="background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);">
      <div class="position-relative d-inline-block">
        <img src="../assets/img/Logo-Gobierno_small.png" alt="Admin" class="rounded-circle border border-3 border-white shadow" width="90" height="90">
        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 15px; height: 15px;"></span>
      </div>
      <div class="mt-3">
        <h6 class="mb-1 fw-bold"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Administrador'; ?></h6>
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
          <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Admin
        </span>
      </div>
    </div>

    <!-- Navegación mejorada -->
    <div class="flex-grow-1 overflow-auto py-3">
      <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
        <span class="text-uppercase fw-bold small text-muted" style="letter-spacing: 1px; font-size: 0.7rem;">MENÚ ADMIN</span>
        <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary" title="Actualizar">
          <i class="fas fa-sync-alt fa-sm"></i>
        </button>
      </div>

      <ul class="nav flex-column px-2 gap-1">
        <li class="nav-item">
          <a href="home.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'home') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'home') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-chart-line fs-6"></i>
            </div>
            <span class="flex-grow-1">Dashboard</span>
            <?php if($page == 'home'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="manage-tickets.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-tickets') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'manage-tickets') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-ticket-alt fs-6"></i>
            </div>
            <span class="flex-grow-1">Gestionar Tickets</span>
            <?php if($page == 'manage-tickets'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="chat-list-admin.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-quotes') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'manage-quotes') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-tasks fs-6"></i>
            </div>
            <span class="flex-grow-1">Gestionar Servicios</span>
            <?php if($page == 'manage-quotes'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="user-access-log.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'user-access-log') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'user-access-log') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-users fs-6"></i>
            </div>
            <span class="flex-grow-1">Registro de Acceso</span>
            <?php if($page == 'user-access-log'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="manage-users.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-users') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'manage-users') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-users-cog fs-6"></i>
            </div>
            <span class="flex-grow-1">Gestionar Usuarios</span>
            <?php if($page == 'manage-users'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="user-list.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'user-list') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'user-list') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-list fs-6"></i>
            </div>
            <span class="flex-grow-1">Lista de Usuarios</span>
            <?php if($page == 'user-list'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="change-password.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'change-password') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'change-password') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-key fs-6"></i>
            </div>
            <span class="flex-grow-1">Cambiar Contraseña</span>
            <?php if($page == 'change-password'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
      </ul>
    </div>

    <!-- Footer del sidebar mejorado -->
    <div class="p-3 border-top text-center bg-light">
      <div class="d-flex justify-content-center gap-2 mb-2">
        <a href="#" class="text-secondary" title="Ayuda"><i class="fas fa-question-circle"></i></a>
        <a href="profile.php" class="text-muted" title="Configuración"><i class="fas fa-cog"></i></a>
        <a href="logout.php" class="text-muted" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i></a>
      </div>
      <small class="text-muted d-block">Versión 1.0</small>
      <small class="text-muted d-block mt-1">© <?= date('Y') ?> SPE</small>
    </div>
  </div>
</div>

<style>
  .hover-bg-light:hover {
    background-color: var(--sidebar-hover) !important;
    color: var(--sidebar-accent) !important;
  }
  .icon-wrapper {
    transition: all 0.2s ease;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .nav-link.active .icon-wrapper {
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  .nav-link:not(.active):hover .icon-wrapper {
    background-color: var(--sidebar-accent) !important;
    color: white !important;
  }
  .bg-gradient-light {
    transition: all 0.3s ease;
  }
  #leftbar {
    transition: all 0.3s ease;
  }
  .offcanvas-md {
    position: fixed;
    height: 100vh;
    top: 0;
    left: 0;
    z-index: 1040;
  }
  @media (min-width: 768px) {
    .offcanvas-md {
      position: sticky;
      transform: none !important;
      visibility: visible !important;
      height: calc(100vh - 56px);
      top: 6px;
    }
    .offcanvas-backdrop {
      display: none !important;
    }
  }
</style>