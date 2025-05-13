<?php
session_start();
setlocale(LC_TIME, 'fr_FR.UTF-8');

include('../config.php');

if (!isset($_SESSION['username']) || $_SESSION['permissions'] !== 'admin') {
    header("Location: ../login");
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : ''; 

$utilisateursParPage = 4;
$pageActuelle = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($pageActuelle - 1) * $utilisateursParPage;

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT id, name, email, created_at, permissions, profile_image, vip_expiration FROM users WHERE name LIKE :search OR email LIKE :search ORDER BY created_at DESC");
    $stmt->execute(['search' => '%' . $search . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalUtilisateurs = count($users);
    $totalPages = 1;
} else {
    $query_total = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUtilisateurs = $query_total->fetchColumn();
    $totalPages = ceil($totalUtilisateurs / $utilisateursParPage);

    $stmt = $pdo->prepare("SELECT id, name, email, created_at, permissions, profile_image, vip_expiration FROM users ORDER BY created_at DESC LIMIT :offset, :limit");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $utilisateursParPage, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$query_total = $pdo->query("SELECT COUNT(*) FROM users");
$totalUtilisateurs = $query_total->fetchColumn();
$totalPages = ceil($totalUtilisateurs / $utilisateursParPage);



if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $stmt_check = $pdo->prepare("SELECT name, email, permissions FROM users WHERE id = ?");
    $stmt_check->execute([$delete_id]);
    $user = $stmt_check->fetch();

    if ($user && $user['permissions'] !== 'admin') {

    $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt_delete->execute([$delete_id]);

    $deletedName = $user['name'];
    $deletedEmail = $user['email'];
    $deletedBy = $_SESSION['username'];

    $webhookUrl = $webhook_logs;

    $payload = [
        "username" => "Logs - Admin",
        "avatar_url" => "https://flip-gen.pw/assets/flipgen.ico",
        "embeds" => [[
            "title" => "❌ Utilisateur Supprimé",
            "color" => 16711680,
            "fields" => [
                [
                    "name" => "👤 Utilisateur supprimé",
                    "value" => "**$deletedName**\n$deletedEmail",
                    "inline" => false
                ],
                [
                    "name" => "🛠️ Supprimé par",
                    "value" => $deletedBy,
                    "inline" => true
                ]
            ],
            "footer" => [ "text" => "Panel Admin" ],
            "timestamp" => date("c")
        ]]
    ];

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    header("Location: utilisateurs");
    exit;
}

}

date_default_timezone_set('Europe/Paris');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - Admin</title>
    <link rel="icon" href="../assets/flipgen.ico" type="image/png">
    <link rel="stylesheet" href="admin.css">
    <style>
       body {
    font-family: 'Nunito Sans', sans-serif;
    background-color: #0f0f0f;
    margin: 0;
    padding: 0;
    color: #f1f1f1;
}

.container {
    width: 95%;
    max-width: 1400px;
    margin: auto;
    padding: 30px 20px;
}

h1 {
    text-align: center;
    color: #1bc29b;
    font-size: 28px;
    margin-bottom: 30px;
}

.search-container {
    text-align: center;
    margin-bottom: 30px;
}

.search-bar {
    width: 100%;
    max-width: 400px;
    padding: 12px 15px;
    border: 1px solid #1e1e1e;
    border-radius: 8px;
    background-color: #1c1c1c;
    color: white;
    font-size: 15px;
    outline: none;
}

.user-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.user-card {
    background: #161616;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #262626;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s;
    text-align: center;
    box-shadow: 0 0 12px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 320px;
    margin: auto;
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 15px rgba(139, 146, 144, 0.3);
}

.user-card h2 {
    color: #aaa;
    margin-bottom: 8px;
    font-size: 18px;
}

.user-card p {
    color: #ccc;
    font-size: 14px;
    margin: 4px 0;
}

.avatar-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid rgb(85, 85, 85);
    margin-bottom: 10px;
}

.delete-btn {
    display: inline-block;
    margin-top: 12px;
    background-color: #e74c3c;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration:none;
    color: white;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

.delete-btn:hover {
    background-color: #c0392b;
}
.vipbtn {
    background:rgb(28, 118, 192);
    color: white;
    border: none;
    padding: 12px 20px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s;
    box-shadow: 0 0 12px rgba(6, 226, 163, 0.3);
}

.vipbtn:hover {
    background-color:rgb(4, 68, 186);
    transform: scale(1.02);
}
form select[name="new_role"],
form input[type="date"] {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    border-radius: 8px;
    background-color: #1c1c1c;
    color: #f1f1f1;
    border: 1px solid #2e2e2e;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

form select[name="new_role"]:focus,
form input[type="date"]:focus {
    border-color: #ccc;
    outline: none;
    box-shadow: 0 0 5px rgba(105, 109, 108, 0.5);
}

html, body {
    overflow-x: hidden;
    max-width: 100vw;
}

.container, .user-card, .search-bar {
    max-width: 85%;
    overflow-wrap: break-word;
    word-wrap: break-word;
}
.delete-link {
    color: #e74c3c;
    text-decoration: none;
    font-weight: bold;
}

.delete-link:hover {
    text-decoration: underline;
}
@media (max-width: 768px) {
    h1 {
        font-size: 22px;
    }

    .search-bar {
        width: 40%;
        font-size: 14px;
    }

    .user-card h2 {
        font-size: 16px;
    }

    .user-container {
        grid-template-columns: 1fr;
    }

    .container {
        padding: 20px 10px;
    }

    .user-card p {
        font-size: 13px;
    }

    .delete-btn {
        padding: 8px 14px;
        font-size: 13px;
    }

    .avatar-img {
        width: 60px;
        height: 60px;
    }

    .message-table th,
    .message-table td {
        font-size: 13px;
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .user-container {
        grid-template-columns: 1fr;
    }

    .container {
        padding: 15px 10px;
    }

    .search-bar {
        width: 100%;
    }
}
.notification {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.notification.success {
    background-color:rgb(43, 125, 202);
    color: #fff;
}
.notification.error {
    background-color:rgb(240, 14, 14);
    color:#fff;
}
.admin-wrapper {
  display: flex;
  flex-direction: row;
  min-height: 100vh;
}


@media(max-width:768px) {.toggle-menu{display:block;} .sidebar{transform:translateX(-100%);} body.sidebar-open .sidebar{transform:translateX(0);} .main-content{margin-left:0;}}  
    .breadcrumb {
  font-size: 0.9rem;
  color: #aaa;
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  .breadcrumb {
    display: none;
  }
}
.main-content {
  margin-left: 250px;
  padding: 40px 30px;
  flex-grow: 1;
  box-sizing: border-box;
}

@media (max-width: 768px) {
  .sidebar {
    position: relative;
    width: 100%;
    height: auto;
  }

  .main-content {
    margin-left: 0;
    padding: 20px 15px;
  }
}

.pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 30px;
}

.pagination a,
.pagination span.dots {
    padding: 8px 14px;
    background-color: #111;
    color:rgb(219, 236, 232);
    border: 1px solid rgb(44, 46, 46);
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 14px;
}

.pagination a.active {
    background-color:rgb(75, 73, 73);
    color: #fff;
}

.pagination a:hover:not(.active):not(.disabled) {
    background-color:rgb(101, 105, 104);
    color: #fff;
    box-shadow: 0 0 8px rgba(171, 179, 177, 0.71);
}

.pagination a.disabled {
    opacity: 0.5;
    pointer-events: none;
    cursor: default;
}

.pagination span.dots {
    background: none;
    border: none;
    cursor: default;
}

@media (max-width: 500px) {
    .pagination a,
    .pagination span.dots {
        padding: 6px 10px;
        font-size: 13px;
    }
}
.search-container + .user-container {
    margin-top: 30px;
}

.user-container {
    clear: both;
    padding-top: 10px;
}

    </style>
</head>

<body>
    <?php if (isset($_SESSION['role_update_success'])): ?>
    <div class="notification success"><?= $_SESSION['role_update_success'] ?></div>
    <?php unset($_SESSION['role_update_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['role_update_error'])): ?>
        <div class="notification error"><?= $_SESSION['role_update_error'] ?></div>
        <?php unset($_SESSION['role_update_error']); ?>
    <?php endif; ?>

    <div class="main-content">
        <?php include 'navbar.php'; ?>
        <h1 style="color:#aaa;"><strong><?= htmlspecialchars($totalUtilisateurs) ?></strong> Utilisateurs</h1>
        <form method="GET" action="utilisateurs.php" class="search-container">
        <input type="text" name="search" class="search-bar" placeholder="Rechercher un utilisateur..." value="<?= htmlspecialchars($search) ?>">
        </form>

        <div class="user-container" id="userContainer">
        <?php
        foreach ($users as $user):
            $image = $user['profile_image']
                ? 'data:image/png;base64,' . base64_encode($user['profile_image'])
                : '../assets/flipflap.png'; 
        ?>
            <div class="user-card">
                <img src="<?= $image ?>" alt="Avatar" class="avatar-img">
                <h2 class="user-name"><?= htmlspecialchars($user['name']) ?></h2>
                <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Inscription :</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                <p><strong>Status :</strong> <?= htmlspecialchars($user['permissions']) ?></p>
                <?php if ($user['permissions'] === 'vip' && !empty($user['vip_expiration'])): ?>
                <p><strong>Expire le :</strong> 
                    <?php
                    $vipDate = new DateTime($user['vip_expiration']);
                    setlocale(LC_TIME, 'fr_FR.UTF-8'); 
                    echo $vipDate->format('d F Y'); 
                    ?>
                </p>
                <?php endif; ?>
                <?php if ($user['permissions'] !== 'admin'): ?>
                    <a href="utilisateurs.php?delete_id=<?= $user['id'] ?>" class="delete-btn"
                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                        Supprimer
                    </a>
                <?php endif; ?>
                <?php if ($user['permissions'] !== 'admin'): ?>
                <form action="update_role.php" method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="new_role" onchange="toggleVipDate(this, <?= $user['id'] ?>)">
                    <option value="membre" <?= $user['permissions'] === 'membre' ? 'selected' : '' ?>>Membre</option>
                    <option value="vip" <?= $user['permissions'] === 'vip' ? 'selected' : '' ?>>Premium</option>
                    <option value="fournisseur" <?= $user['permissions'] === 'fournisseur' ? 'selected' : '' ?>>Fournisseur</option>
                    </select>

                    <input type="date" name="vip_expiration" id="vip_expiration_<?= $user['id'] ?>" 
                    value="<?= ($user['permissions'] === 'vip' && !empty($user['vip_expiration'])) ? date('Y-m-d', strtotime($user['vip_expiration'])) : '' ?>" 
                    style="margin-top:5px; display: <?= $user['permissions'] === 'vip' ? 'block' : 'none' ?>;">

                    <button type="submit" class="vipbtn">Appliquer</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

        
    </div>
    </div>
    <?php if ($totalPages > 1 && $search === ''): ?>
    <div class="pagination">

        <a href="?page=<?= $pageActuelle - 1 ?>" class="prev <?= $pageActuelle <= 1 ? 'disabled' : '' ?>">
            &laquo; Précédent
        </a>

        <?php
        $range = 2; 

        for ($i = 1; $i <= $totalPages; $i++) {
            if (
                $i == 1 || $i == $totalPages || 
                ($i >= $pageActuelle - $range && $i <= $pageActuelle + $range)
            ) {
                if (isset($dots) && $dots === true) {
                    echo '<span class="dots">...</span>';
                    $dots = false;
                }

                $activeClass = $i == $pageActuelle ? 'active' : '';
                echo '<a href="?page=' . $i . '" class="' . $activeClass . '">' . $i . '</a>';
            } else {
                $dots = true;
            }
        }
        ?>

        <a href="?page=<?= $pageActuelle + 1 ?>" class="next <?= $pageActuelle >= $totalPages ? 'disabled' : '' ?>">
            Suivant &raquo;
        </a>
    </div>
    <?php endif; ?>
    </div>
      

    <script>
        function filterUsers() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.querySelectorAll('.user-card');

            cards.forEach(card => {
                let name = card.querySelector('.user-name').textContent.toLowerCase();
                if (name.includes(input)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>
    <script>
    function toggleVipDate(select, id) {
        const dateInput = document.getElementById('vip_expiration_' + id);
        if (select.value === 'vip') {
            dateInput.style.display = 'block';
        } else {
            dateInput.style.display = 'none';
        }
    }
    </script>
    <script>
    setTimeout(() => {
        const notif = document.querySelector('.notification');
        if (notif) notif.style.display = 'none';
    }, 4000);
</script>

</body>
</html>
<?php include '../footer.php'; ?>