<?php $page = ''?>
<link href="../styles/superv.css">

<!-- Botón de menú para móviles -->
<button class="btn btn-outline-primary d-md-none m-3 rounded-pill shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar" aria-controls="leftbar">
  <i class="fas fa-bars me-1"></i> Menú
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div id="leftbar"
     class="offcanvas offcanvas-start d-md-block bg-white border-end mt-header"
     tabindex="-1"
     style="--sidebar-accent: #4361ee; --sidebar-hover: #f8f9fc; width: 250px;">

  <!-- Header solo visible en móvil -->
  <div class="offcanvas-header d-md-none border-bottom bg-primary text-white">
    <h5 class="offcanvas-title fw-bold">Menú Supervisor</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <!-- Contenido del menú -->
  <div class="offcanvas-body d-flex flex-column p-0">
    <!-- Perfil -->
    <div class="text-center py-4 border-bottom bg-gradient-light">
      <div class="position-relative d-inline-block">
        <img src="../assets/img/Logo-Gobierno_small.png" alt="Perfil Admin" 
             class="img-fluid rounded-circle border border-3 border-white shadow"
             style="width: 90px; height: 90px; object-fit: cover;">
        <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-3 border-white"
              style="width: 15px; height: 15px;"></span>
      </div>
      <div class="mt-3">
        <div class="text-muted small">Bienvenid@</div>
        <div class="fw-bold text-primary mt-1 fs-5">SUPERVISOR</div>
      </div>
    </div>

    <!-- Navegación -->
    <div class="px-3 pt-3 flex-grow-1 overflow-auto">
      <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <span class="text-uppercase fw-bold small text-muted">NAVEGACIÓN</span>
        <a href="#" onclick="location.reload()" class="text-decoration-none text-muted" title="Actualizar">
          <i class="fas fa-sync-alt fa-sm"></i>
        </a>
      </div>

      <ul class="nav flex-column gap-2 mb-4">
        <li class="nav-item">
          <a href="s_dashboard.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'home') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <div class="icon-container bg-primary bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-chart-bar fs-5 <?= ($page == 'home') ? 'text-white' : 'text-primary'; ?>"></i>
            </div>
            <span class="fw-medium">Dashboard</span>
            <?php if($page == 'home'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="manage-tickets.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-tickets') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <div class="icon-container bg-primary bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-ticket-alt fs-5 <?= ($page == 'manage-tickets') ? 'text-white' : 'text-primary'; ?>"></i>
            </div>
            <span class="fw-medium">Gestionar Ticket</span>
            <?php if($page == 'manage-tickets'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="tech-layout.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-users') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <div class="icon-container bg-primary bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-users-cog fs-5 <?= ($page == 'manage-users') ? 'text-white' : 'text-primary'; ?>"></i>
            </div>
            <span class="fw-medium">Plantilla de Técnicos</span>
            <?php if($page == 'manage-users'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="tickets-status.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'user-list') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <div class="icon-container bg-primary bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-book fs-5 <?= ($page == 'user-list') ? 'text-white' : 'text-primary'; ?>"></i>
            </div>
            <span class="fw-medium">Estado de Tickets</span>
            <?php if($page == 'user-list'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="change-password.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'change-password') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <div class="icon-container bg-primary bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-lock fs-5 <?= ($page == 'change-password') ? 'text-white' : 'text-primary'; ?>"></i>
            </div>
            <span class="fw-medium">Cambiar Contraseña</span>
            <?php if($page == 'change-password'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>
      
      <!-- Separador -->
      <div class="px-2 mb-3">
        <hr class="text-muted opacity-25">
      </div>
      
      <!-- Configuración rápida -->
      <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <span class="text-uppercase fw-bold small text-muted">CONFIGURACIÓN</span>
      </div>
      
      <ul class="nav flex-column gap-2">
        <li class="nav-item">
          <a href="#" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center text-dark">
            <div class="icon-container bg-secondary bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-cog fs-5 text-secondary"></i>
            </div>
            <span class="fw-medium">Configuración</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="../logout.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center text-dark">
            <div class="icon-container bg-danger bg-opacity-10 rounded-2 p-2 me-3">
              <i class="fas fa-sign-out-alt fs-5 text-danger"></i>
            </div>
            <span class="fw-medium">Cerrar Sesión</span>
          </a>
        </li>
      </ul>
    </div>
    
    <!-- Footer del sidebar -->
    <div class="p-3 border-top text-center bg-light">
      <small class="text-muted d-block">&copy; 2025 SPE</small>
      <small class="text-muted">v1.0</small>
    </div>
  </div>
</div>

<style>
  /* Estilos adicionales para mejorar la apariencia */
  #leftbar {
    transition: all 0.3s ease;
  }
  
  .nav-link {
    transition: all 0.2s ease;
  }
  
  .nav-link:hover:not(.active) {
    background-color: var(--sidebar-hover) !important;
    color: var(--sidebar-accent) !important;
  }
  
  .nav-link.active {
    background-color: var(--sidebar-accent) !important;
  }
  
  .icon-container {
    transition: all 0.2s ease;
  }
  
  .nav-link:hover .icon-container {
    background-color: var(--sidebar-accent) !important;
  }
  
  .nav-link:hover .icon-container i {
    color: white !important;
  }
  
  .bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
  }
</style>