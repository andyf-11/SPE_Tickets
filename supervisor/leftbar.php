<?php $page = ''?>
<link href="../styles/superv.css">

<!-- Botón de menú para móviles -->
<button class="btn btn-outline-primary d-md-none m-3 rounded-pill" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar" aria-controls="leftbar">
  <i class="fas fa-bars me-1"></i> Menú
</button>

<!-- Sidebar: offcanvas en móviles, fijo en escritorio -->
<div id="leftbar"
     class="offcanvas offcanvas-start d-md-block bg-white border-end mt-header shadow-sm"
     tabindex="-1"
     style="--sidebar-accent: #4e73df; --sidebar-hover: #f8f9fc;">

  <!-- Header solo visible en móvil -->
  <div class="offcanvas-header d-md-none border-bottom">
    <h5 class="offcanvas-title fw-bold text-primary">Menú Supervisor</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>

  <!-- Contenido del menú -->
  <div class="offcanvas-body d-flex flex-column p-0">
    <!-- Perfil -->
    <div class="text-center py-4 border-bottom bg-gradient-light">
      <div class="position-relative d-inline-block">
        <img src="../assets/img/Logo-Gobierno_small.png" alt="Perfil Admin" class="img-fluid rounded-circle border border-3 border-white shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
      </div>
      <div class="mt-3">
        <div class="text-muted small">Bienvenid@</div>
        <div class="fw-bold text-primary mt-1" style="letter-spacing: 0.5px;">SUPERVISOR</div>
      </div>
    </div>

    <!-- Navegación -->
    <div class="px-3 pt-3 flex-grow-1 overflow-auto">
      <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <span class="text-uppercase fw-bold small text-muted" style="letter-spacing: 1px;">NAVEGACIÓN</span>
        <a href="#" onclick="location.reload()" class="text-decoration-none text-muted" title="Actualizar">
          <i class="fas fa-sync-alt fa-sm"></i>
        </a>
      </div>

      <ul class="nav flex-column gap-1">
        <li class="nav-item">
          <a href="s_dashboard.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'home') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <i class="fas fa-chart-bar me-3 fs-5 <?= ($page == 'home') ? 'text-white' : 'text-primary'; ?>"></i>
            <span>Dashboard</span>
            <?php if($page == 'home'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="manage-tickets.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-tickets') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <i class="fas fa-ticket-alt me-3 fs-5 <?= ($page == 'manage-tickets') ? 'text-white' : 'text-primary'; ?>"></i>
            <span>Gestionar Ticket</span>
            <?php if($page == 'manage-tickets'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="tech-layout.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'manage-users') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <i class="fas fa-users-cog me-3 fs-5 <?= ($page == 'manage-users') ? 'text-white' : 'text-primary'; ?>"></i>
            <span>Plantilla de Técnicos</span>
            <?php if($page == 'manage-users'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="tickets-status.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'user-list') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <i class="fas fa-book me-3 fs-5 <?= ($page == 'user-list') ? 'text-white' : 'text-primary'; ?>"></i>
            <span>Estado de Tickets</span>
            <?php if($page == 'user-list'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a href="change-password.php" class="nav-link rounded-3 px-3 py-2 d-flex align-items-center <?= ($page == 'change-password') ? 'active bg-primary text-white shadow-sm' : 'text-dark'; ?>">
            <i class="fas fa-lock me-3 fs-5 <?= ($page == 'change-password') ? 'text-white' : 'text-primary'; ?>"></i>
            <span>Cambiar Contraseña</span>
            <?php if($page == 'change-password'): ?>
              <span class="ms-auto"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>
    </div>
    
    <!-- Footer del sidebar -->
    <div class="p-3 border-top text-center">
      <small class="text-muted">v1.0.0</small>
    </div>
  </div>
</div>
