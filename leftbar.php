<?php $page = ''?>
<!-- Botón de menú para móviles -->
<button class="btn btn-primary d-md-none m-3 rounded-pill shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar" aria-controls="leftbar">
  <i class="fas fa-bars me-2"></i> Menú
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div class="offcanvas-md offcanvas-start bg-white shadow-sm" tabindex="-1" id="leftbar" style="--sidebar-accent: #4e73df; --sidebar-hover: #f8f9fc; width: 250px;">
  <div class="offcanvas-header d-md-none border-bottom">
    <h5 class="offcanvas-title fw-bold text-primary">Menú Principal</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0 h-100">
    <!-- Perfil mejorado -->
    <div class="text-center py-4 border-bottom bg-gradient-light" style="background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);">
      <div class="position-relative d-inline-block">
        <img src="assets/img/user.png" alt="Usuario" class="rounded-circle border border-3 border-white shadow" width="90" height="90">
        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 15px; height: 15px;"></span>
      </div>
      <div class="mt-3">
        <h6 class="mb-1 fw-bold"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Usuario'; ?></h6>
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
          <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Conectado
        </span>
      </div>
    </div>

    <!-- Navegación mejorada -->
    <div class="flex-grow-1 overflow-auto py-3">
      <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
        <span class="text-uppercase fw-bold small text-muted" style="letter-spacing: 1px; font-size: 0.7rem;">MENÚ DE NAVEGACIÓN</span>
        <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary" title="Actualizar">
          <i class="fas fa-sync-alt fa-sm"></i>
        </button>
      </div>

      <ul class="nav flex-column px-2 gap-1">
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'dashboard') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'dashboard') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-home fs-6"></i>
            </div>
            <span class="flex-grow-1">Dashboard</span>
            <?php if($page == 'dashboard'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="create-ticket.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'create-ticket') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'create-ticket') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-plus-circle fs-6"></i>
            </div>
            <span class="flex-grow-1">Crear Ticket</span>
            <?php if($page == 'create-ticket'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="view-tickets.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'view-tickets') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'view-tickets') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-ticket-alt fs-6"></i>
            </div>
            <span class="flex-grow-1">Ver Tickets</span>
            <?php if($page == 'view-tickets'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="chat-list-tech.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'chat-list-tech') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'chat-list-tech') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-comments fs-6"></i>
            </div>
            <span class="flex-grow-1">Soporte Técnico</span>
            <?php if($page == 'chat-list-tech'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="profile.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'profile') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'profile') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-user fs-6"></i>
            </div>
            <span class="flex-grow-1">Perfil</span>
            <?php if($page == 'profile'): ?>
              <i class="fas fa-chevron-right fa-xs ms-2"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="change-password.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'change-password') ? 'active bg-primary text-white shadow-sm' : 'text-dark hover-bg-light'; ?>">
            <div class="icon-wrapper <?= ($page == 'change-password') ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'; ?> rounded-2 p-2 me-3">
              <i class="fas fa-lock fs-6"></i>
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
        <a href="ayuda/user/reglamento.php" class="text-secondary" data-bs-toggle="modal" data-bs-target="#tutorialModal" title="tutorial de uso"><i class="fas fa-question-circle"></i></a>
        <a href="#" class="text-muted"><i class="fas fa-cog"></i></a>
        <a href="logout.php" class="text-muted"><i class="fas fa-sign-out-alt"></i></a>
      </div>
      <small class="text-muted d-block">Versión 1.0</small>
      <small class="text-muted d-block mt-1">© <?= date('Y') ?> SPE</small>
    </div>
  </div>
</div>

<?php include("ayuda/user/reglamento.php");?>
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