<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}
if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
    header("Location: /accueil");
    exit;
}
if (isset($_SESSION['user_id']) && intval($_GET['user']) === intval($_SESSION['user_id'])) {
    header("Location: /compte.php");
    exit;
}

$user_id = intval($_GET['user']);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}
$last_accounts = [];
try {
    $stmt = $pdo->prepare("
        SELECT generator_name, generated_account, created_at
        FROM generations_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $last_accounts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur lors du chargement des logs : " . $e->getMessage());
}

$is_admin = (isset($_SESSION['permissions']) && $_SESSION['permissions'] === 'admin');
$permissions = $_SESSION['permissions'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/flipgen.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['name']) ?> - Flip-Gen</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <style>
        .file-upload input[type="file"] { display: none; }
        .upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .settings-tabs {
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
    }
    .settings-tabs button {
        background: none;
        border: none;
        padding: 10px 15px;
        font-weight: bold;
        cursor: pointer;
    }
    .settings-tabs button.active {
        border-bottom: 2px solid #686868;
        color: #686868;
    }
    .settings-content {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
    }
    .avatar-section {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    .avatar-wrapper {
        position: relative;
        cursor: pointer;
    }
    #profile-image {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        border: 3px solid #686868;
        object-fit: cover;
    }
    .upload-overlay {
        position: absolute;
        right: 0;
        bottom: 0;
        background: #000;
        color: #fff;
        padding: 8px;
        border-radius: 50%;
    }
    .delete-btn {
        background: #e53935;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
    }
    .toast {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px 25px;
        border-radius: 6px;
        font-weight: bold;
        z-index: 9999;
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        animation: fadein 0.5s, fadeout 0.5s 3.5s;
    }
    @keyframes shine {
        0% {
            background-position: -200%;
        }
        100% {
            background-position: 200%;
        }
    }


    .vip-crown {
        margin-left: 3px;
        cursor: help;
        font-size: 1em;
        vertical-align: middle;
        text-shadow: 0 0 4px rgba(255, 215, 0, 0.6);
        transition: transform 0.2s ease;
        background: linear-gradient(90deg, #fff099, #ffd700, #ffcc00, #fff099);
        background-size: 400%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: bold;
        animation: shine 3s linear infinite;
    }

    .vip-crown:hover {
        transform: scale(1.2) rotate(-5deg);
    }
    .toast.success { background-color: #28a745; }
    .toast.error { background-color: #dc3545; }

    @keyframes fadein {
        from { opacity: 0; top: 0px; }
        to { opacity: 1; top: 20px; }
    }

    @keyframes fadeout {
        from { opacity: 1; top: 20px; }
        to { opacity: 0; top: 0px; }
    }
    .content {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
        background-color: #0f0f0f;
        border: 1px solid #1e1e1e;
        border-radius: 12px;
        color: white;
    }

    h1 {
        font-size: 28px;
        text-align: center;
        margin-bottom: 25px;
        color: white;
        font-family: 'Nunito Sans', sans-serif;
    }

    .settings-tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #333;
    }

    .settings-tabs button {
        background: none;
        border: none;
        padding: 10px 20px;
        font-weight: bold;
        cursor: pointer;
        color: #aaa;
        font-family: 'Nunito Sans', sans-serif;
    }

    .settings-tabs button.active {
        border-bottom: 2px solid #1bc29b;
        color: #1bc29b;
    }

    .settings-content {
        background-color: #0f0f0f;
        padding: 30px;
        border-radius: 10px;
        border: 1px solid #1e1e1e;
        color: white;
    }

    .avatar-section {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .avatar-wrapper {
        position: relative;
        cursor: pointer;
    }

    #profile-image {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        border: 2px solid #1bc29b;
        object-fit: cover;
        background-color: #0f0f0f;
    }

    .upload-overlay {
        position: absolute;
        right: 0;
        bottom: 0;
        background: #1bc29b;
        color: white;
        padding: 8px;
        border-radius: 50%;
        transition: background 0.3s;
    }

    .upload-overlay:hover {
        background: #17a88a;
    }

    .delete-btn {
        background: #e74c3c;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .delete-btn:hover {
        background: #c0392b;
    }

    .vip-username {
        font-size: 1.2rem;
        font-weight: bold;
        color: #f1f1f1;
        display: inline-block;
        margin-top: 10px;
    }

    hr {
        border: 1px solid #333;
        margin: 25px 0;
    }

    .settings-content p {
        color: #ccc;
        margin-bottom: 15px;
    }
    .recent-accounts {
    margin-top: 30px;
    background-color: #0f0f0f;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #222;
}

.recent-accounts h3 {
    font-size: 1rem;
    margin-bottom: 20px;
    color: #fff;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 15px;
}

.account-card {
    background-color: #1c1c1c;
    border: 1px solid #2c2c2c;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 6px rgba(0,0,0,0.4);
    transition: transform 0.2s ease;
}

.account-card:hover {
    transform: scale(1.02);
    box-shadow: 0 0 10px rgba(27, 194, 155, 0.2);
}

.account-info h4 {
    margin: 0 0 8px;
    color: #fff;
    font-size: 1rem;
}

.account-info p {
    color: #ccc;
    font-size: 0.95rem;
    word-break: break-all;
}

.account-date {
    margin-top: 8px;
    font-size: 0.8rem;
    color: #888;
    text-align: right;
}
.admin-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.btn-action {
    padding: 8px 12px;
    background-color: #1e1e1e;
    color: white;
    border: 1px solid #444;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.btn-action:hover {
    background-color: #333;
}
.generateur-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    margin: 0 auto;
    display: block;
    border: 2px solid rgb(92, 92, 92);
}
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<?php if (isset($_SESSION['notification'])): ?>
    <div class="toast <?= $_SESSION['notif_type'] ?>">
        <?= htmlspecialchars($_SESSION['notification']) ?>
    </div>
    <script>
        setTimeout(() => {
            document.querySelector('.toast')?.remove();
        }, 4000);
    </script>
    <?php unset($_SESSION['notification'], $_SESSION['notif_type']); ?>
<?php endif; ?>
<div class="content">
<h1>Profil de <?= htmlspecialchars($user['name']) ?></h1>

<div class="settings-content">

    <div class="avatar-section">
        <label for="file-upload" class="generateur-card">
        <img src="<?= !empty($user['profile_image']) ? 'data:image/png;base64,' . base64_encode($user['profile_image']) : 'assets/flipflap.png' ?>" width="100" class="avatar">
        </label>
    </div>


        <?= htmlspecialchars($user['name']) ?>
    <p><strong>Inscrit le :</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
    <?php if ($permissions === 'admin'): ?>
    <p><i class="fa-solid fa-user"></i><strong> <?= htmlspecialchars($user['email']) ?></strong></p>
    <?php endif; ?>

    <p><strong>Comptes générés :</strong> 
    <?= htmlspecialchars($user['generations']) ?>
    </p>
</div>
<hr>
<div class="recent-accounts">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> Derniers comptes générés</h3>
    <?php if (!empty($last_accounts)): ?>
        <div class="account-grid">
            <?php foreach ($last_accounts as $account): ?>
                <div class="account-card">
                    <div class="account-info">
                        <h4><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($account['generator_name']) ?></h4>
                        <?php if ($permissions === 'admin'): ?>
                        <p><i class="fa-solid fa-user"></i> <?= htmlspecialchars($account['generated_account']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="account-date">
                        <?= date('H:i - d/m', strtotime($account['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:#aaa;">Aucun compte généré récemment.</p>
    <?php endif; ?>
</div>



</div>
</body>
</html>
<?php include 'footer.php'; ?>


