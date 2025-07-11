<?php $page = ''?>
<!-- Botón de menú para móviles -->
<button class="btn btn-outline-primary d-md-none m-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar"
  aria-controls="leftbar">
  <i class="fas fa-bars me-2"></i> Menú
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div class="offcanvas-md offcanvas-start bg-white shadow-sm" tabindex="-1" id="leftbar" style="width: 280px;">
  <div class="offcanvas-header d-md-none border-bottom">
    <h5 class="offcanvas-title fw-bold">Menú Principal</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0 h-100">
    <!-- Perfil -->
    <div class="text-center p-4 border-bottom">
      <img src="assets/img/user.png" alt="Usuario" class="rounded-circle mb-3 shadow-sm" width="80" height="80">
      <h6 class="mb-1 fw-bold"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Usuario'; ?></h6>
      <div class="d-flex justify-content-center align-items-center">
        <span class="badge bg-success rounded-pill px-3 py-1">
          <i class="fas fa-circle me-1" style="font-size: 8px;"></i> Conectado
        </span>
      </div>
    </div>

    <!-- Navegación -->
    <div class="flex-grow-1 overflow-auto py-3">
      <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
        <span class="text-uppercase fw-bold small text-muted">Menú de Navegación</span>
        <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary" title="Actualizar">
          <i class="fas fa-sync-alt"></i>
        </button>
      </div>

      <ul class="nav flex-column px-2">
        <li class="nav-item">
          <a href="dashboard.php"
            class="nav-link rounded-3 mx-2 py-3 <?= ($page == 'dashboard') ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'; ?>">
            <i class="fas fa-home me-3 fs-5 <?= ($page == 'dashboard') ? 'text-primary' : 'text-secondary'; ?>"></i>
            <span>Dashboard</span>
            <?php if($page == 'dashboard'): ?>
              <i class="fas fa-chevron-right ms-auto text-primary"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="create-ticket.php"
            class="nav-link rounded-3 mx-2 py-3 <?= ($page == 'create-ticket') ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'; ?>">
            <i class="fas fa-plus-circle me-3 fs-5 <?= ($page == 'create-ticket') ? 'text-primary' : 'text-secondary'; ?>"></i>
            <span>Crear Ticket</span>
            <?php if($page == 'create-ticket'): ?>
              <i class="fas fa-chevron-right ms-auto text-primary"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="view-tickets.php"
            class="nav-link rounded-3 mx-2 py-3 <?= ($page == 'view-tickets') ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'; ?>">
            <i class="fas fa-ticket-alt me-3 fs-5 <?= ($page == 'view-tickets') ? 'text-primary' : 'text-secondary'; ?>"></i>
            <span>Ver Tickets</span>
            <?php if($page == 'view-tickets'): ?>
              <i class="fas fa-chevron-right ms-auto text-primary"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="chat-list-tech.php"
            class="nav-link rounded-3 mx-2 py-3 <?= ($page == 'chat-list-tech') ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'; ?>">
            <i class="fas fa-comments me-3 fs-5 <?= ($page == 'chat-list-tech') ? 'text-primary' : 'text-secondary'; ?>"></i>
            <span>Soporte Técnico</span>
            <?php if($page == 'chat-list-tech'): ?>
              <i class="fas fa-chevron-right ms-auto text-primary"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="profile.php"
            class="nav-link rounded-3 mx-2 py-3 <?= ($page == 'profile') ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'; ?>">
            <i class="fas fa-user me-3 fs-5 <?= ($page == 'profile') ? 'text-primary' : 'text-secondary'; ?>"></i>
            <span>Perfil</span>
            <?php if($page == 'profile'): ?>
              <i class="fas fa-chevron-right ms-auto text-primary"></i>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="change-password.php"
            class="nav-link rounded-3 mx-2 py-3 <?= ($page == 'change-password') ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'; ?>">
            <i class="fas fa-lock me-3 fs-5 <?= ($page == 'change-password') ? 'text-primary' : 'text-secondary'; ?>"></i>
            <span>Cambiar Contraseña</span>
            <?php if($page == 'change-password'): ?>
              <i class="fas fa-chevron-right ms-auto text-primary"></i>
            <?php endif; ?>
          </a>
        </li>
      </ul>
    </div>

    <!-- Footer del sidebar -->
    <div class="p-3 border-top">
      <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">v1.0.0</small>
      </div>
    </div>
  </div>
</div>

<style>
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
      top: 56px;
    }
     
    .offcanvas-backdrop {
      display: none !important;
    }
  }
  
  .nav-link {
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
  }
  
  .nav-link:hover {
    background-color: rgba(98, 64, 212, 0.05);
  }
  
  .nav-link.active {
    border-left: 3px solid #6240d4;
  }
</style>