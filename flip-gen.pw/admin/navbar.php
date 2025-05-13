
<!-- SIDEBAR -->
<aside class="sidebar">
  <a href="../accueil"><div class="logo"><?= SITE_NAME ?></div></a>
  <nav>
    <ul>
      <li>
        <a href="/admin/admin"><i class="fas fa-chart-line"></i> Tableau de bord</a>
      </li>
      <?php if (isset($_SESSION['permissions']) && $_SESSION['permissions'] === 'admin'): ?>
      <li>
        <a href="/admin/utilisateurs"><i class="fas fa-users"></i> Utilisateurs</a>
      </li>
      <li>
        <a href="/admin/generateurs"><i class="fas fa-cogs"></i> Générateurs</a>
      </li>
      <li>
        <a href="/admin/configuration"><i class="fas fa-sliders-h"></i> Paramètres</a>
      </li>
      <?php endif; ?>
      <li>
        <a href="/admin/restock"><i class="fas fa-box"></i> Restock</a>
      </li>
    </ul>
  </nav>
</aside>

<!-- STYLES -->
<style>
body {
  margin: 0;
  font-family: 'Nunito Sans', sans-serif;
  background: #0f0f0f;
  color: #fff;
}

/* Toggle menu bouton */
.toggle-menu {
  display: none;
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #fff;
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 1100;
}

.admin-wrapper {
  display: flex;
  flex-direction: row;
  min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
  width: 250px;
  background: #121212;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 1000;
  overflow-y: auto;
  border-right: 1px solid #1e1e1e;
  padding: 20px;
  transition: transform 0.3s ease;
}
@media (max-width: 768px) {
  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 900;
  }

  body.sidebar-open .sidebar-overlay {
    display: block;
  }
}

.sidebar .logo {
  font-size: 1.5rem;
  font-weight: bold;
  text-align: center;
  color: #fff;
  margin-bottom: 30px;
}

.sidebar nav ul {
  list-style: none;
  padding: 0;
}

.sidebar nav ul li {
  margin-bottom: 12px;
}

.sidebar nav ul li a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 15px;
  color: #ccc;
  border-radius: 6px;
  transition: background 0.3s;
}

.sidebar nav ul li a:hover,
.sidebar nav ul li a.active {
  background: #1c1c1c;
  color: #fff;
}

/* CONTENU */
.main-content {
  margin-left: 250px;
  padding: 40px 30px;
  flex-grow: 1;
  box-sizing: border-box;
  transition: margin-left 0.3s;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .toggle-menu {
    display: block;
  }

  .sidebar {
    transform: translateX(-100%);
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    background: #121212;
  }

  body.sidebar-open .sidebar {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
    padding: 80px 20px 20px;
  }

  body.sidebar-open .main-content {
    filter: blur(2px);
    pointer-events: none;
  }
}
</style>
<script>
  const toggleBtn = document.querySelector('.toggle-menu');
  const body = document.body;
  const overlay = document.querySelector('.sidebar-overlay');

  toggleBtn.addEventListener('click', () => {
    body.classList.toggle('sidebar-open');
  });

  overlay.addEventListener('click', () => {
    body.classList.remove('sidebar-open');
  });
</script>
