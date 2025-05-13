<?php
session_start();
include('../config.php');
if (!isset($_SESSION['username']) || !in_array($_SESSION['permissions'], ['admin', 'fournisseur'])) {
    header("Location: ../login");
    exit;
}
$stats = [
    ['👥', '<small style="color: #aaa;">Utilisateurs</small>', $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()],
    ['🔄', '<small style="color: #aaa;">Générations</small>', $pdo->query("SELECT SUM(generations) FROM users")->fetchColumn()],
    ['⚙️', '<small style="color: #aaa;">Générateurs</small>', $pdo->query("SELECT COUNT(*) FROM generateurs")->fetchColumn()],
    ['⭐', '<small style="color: #aaa;">VIP</small>', $pdo->query("SELECT COUNT(*) FROM users WHERE permissions = 'vip'")->fetchColumn()],
    ['🛠️', '<small style="color: #aaa;">Admins</small>', $pdo->query("SELECT COUNT(*) FROM users WHERE permissions = 'admin'")->fetchColumn()],
    ['📦', '<small style="color: #aaa;">Fournisseurs</small>', $pdo->query("SELECT COUNT(*) FROM users WHERE permissions = 'fournisseur'")->fetchColumn()],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Admin</title>
    <link rel="icon" type="image/png" href="../assets/flipgen.ico">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

  <?php include "navbar.php"; ?>
  <div class="sidebar-overlay"></div>
  <main class="main-content">
    <header class="header">
    <small style="color: #aaa;"><h3><a href="../accueil"><?= SITE_NAME ?></a> > Tableau de bord</h3></small>
      <button class="toggle-menu"><i class="fas fa-bars"></i></button>
    </header>
    <section class="stats-grid">
      <?php foreach ($stats as [$icon,$label,$value]): ?>
      <div class="stat-card">
        <div class="icon"><?= $icon ?></div>
        <div class="value"><?= $value ?></div>
        <div class="label"><?= $label ?></div>
      </div>
      <?php endforeach; ?>
    </section><br>
    <section>
      <h2>Actions rapides</h2>
      <div class="actions-grid">
        <a href="/admin/utilisateurs" class="action-card <?= $_SESSION['permissions']==='admin'?'':'disabled' ?>"><i class="fas fa-user-cog"></i><span> Utilisateurs</span></a>
        <a href="/admin/generateurs" class="action-card <?= $_SESSION['permissions']==='admin'?'':'disabled' ?>"><i class="fas fa-cogs"></i><span> Générateurs</span></a>
        <a href="/admin/configuration" class="action-card <?= $_SESSION['permissions']==='admin'?'':'disabled' ?>"><i class="fas fa-sliders-h"></i><span> Paramètres</span></a>
        <a href="/admin/restock" class="action-card <?= in_array($_SESSION['permissions'],['admin','fournisseur'])?'':'disabled' ?>"><i class="fas fa-box"></i><span> Restock</span></a>
      </div>
    </section>
  </main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
const toggle=document.querySelector('.toggle-menu');toggle.addEventListener('click',()=>document.body.classList.toggle('sidebar-open'));
</script>
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
</body>
</html>
<?php include '../footer.php'; ?>
