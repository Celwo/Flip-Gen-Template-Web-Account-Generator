<?php
session_start();
include('../config.php');

if (!isset($_SESSION['username']) || $_SESSION['permissions'] !== 'admin') {
    header("Location: ../login");
    exit;
}

$stmt = $pdo->query("SELECT * FROM config");
$configs = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $configs[$row['key']] = $row['value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inscription = $_POST['inscription'] === 'on' ? 'on' : 'off';
    $connexion = $_POST['connexion'] === 'on' ? 'on' : 'off';
    $generateurs = $_POST['generateurs'] === 'on' ? 'on' : 'off';
    $limit_membre = (int) $_POST['limit_membre_per_day'];
    $limit_vip = (int) $_POST['limit_vip_per_day'];
    $cooldown_membre = (int) $_POST['cooldown_membre_minutes'];
    $cooldown_vip = (int) $_POST['cooldown_vip_minutes'];

    $updateStmt = $pdo->prepare("UPDATE config SET value = ? WHERE `key` = ?");
    $updateStmt->execute([$inscription, 'inscription']);
    $updateStmt->execute([$connexion, 'connexion']);
    $updateStmt->execute([$generateurs, 'generateurs']);
    $updateStmt->execute([$limit_membre, 'limit_membre_per_day']);
    $updateStmt->execute([$limit_vip, 'limit_vip_per_day']);
    $updateStmt->execute([$cooldown_membre, 'cooldown_membre_minutes']);
    $updateStmt->execute([$cooldown_vip, 'cooldown_vip_minutes']);
    $_SESSION['config_success'] = "Configuration mise à jour avec succès.";
    header("Location: configuration");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <title>Configuration - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" href="../assets/flipgen.ico">
    <style>
body {
    margin: 0;
    padding: 0;
    background-color: #0f0f0f;
    color: #f1f1f1;
    font-family: 'Nunito Sans', sans-serif;
}

.container {
    padding: 40px 20px;
    display: flex;
    justify-content: center;
    align-items: center; 
    min-height: calc(100vh - 100px); 
}


.config-card {
    background-color: #161616;
    border: 1px solid #2c2c2c;
    padding: 30px;
    width: 100%;
    max-width: 600px;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0,0,0,0.4);
}

.config-card h2 {
    text-align: center;
    color: #ccc;
    margin-bottom: 25px;
    font-size: 1.6rem;
}

.config-form {
    display: flex;
    flex-direction: column;
}

.config-form label {
    font-weight: bold;
    margin: 16px 0 6px;
    color: rgb(211, 215, 219) !important;
}

.config-form select,
.config-form input[type="number"] {
    width: 100%;
    padding: 10px 14px;
    background-color: #1c1c1c;
    color: #f1f1f1;
    border-radius: 8px;
    border: 1px solid #2e2e2e;
    font-size: 14px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}

.config-form select:focus,
.config-form input[type="number"]:focus {
    border-color: #ccc;
    outline: none;
    box-shadow: 0 0 5px rgba(119, 121, 120, 0.5);
}

.config-form button {
    background-color:rgb(37, 127, 201);
    border: none;
    color: #fff;
    padding: 12px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 25px;
    transition: background-color 0.3s ease;
}

.config-form button:hover {
    background-color: rgb(31, 106, 167);
}

.notification {
    padding: 15px;
    background-color:rgb(37, 127, 201);
    color: #fff;
    text-align: center;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: bold;
    font-size: 0.95rem;
}
.config-form label {
    font-weight: bold;
    margin: 16px 0 6px;
    color: #ddd;
    display: block;
}

@media (max-width: 480px) {
    .config-card {
        padding: 20px;
    }

    .config-card h2 {
        font-size: 1.4rem;
    }

    .config-form label {
        font-size: 0.9rem;
    }

    .config-form input,
    .config-form select {
        font-size: 0.9rem;
    }

    .config-form button {
        padding: 10px;
        font-size: 0.95rem;
    }
}

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <?php if (isset($_SESSION['config_success'])): ?>
    <div class="toast"><?= $_SESSION['config_success'] ?></div>
    <script>
        setTimeout(() => {
            document.querySelector('.toast')?.remove();
        }, 4000);
    </script>
    <?php unset($_SESSION['config_success']); ?>
    <?php endif; ?>
    <div class="config-card">
        <h2>Configuration de <?= SITE_NAME ?></h2>

        <form method="POST" class="config-form">
            <label for="inscription"><i class="fas fa-user-plus"></i> Inscription</label>
            <select name="inscription" id="inscription">
                <option value="on" <?= $configs['inscription'] === 'on' ? 'selected' : '' ?>>Activée</option>
                <option value="off" <?= $configs['inscription'] === 'off' ? 'selected' : '' ?>>Désactivée</option>
            </select>

            <label for="connexion"><i class="fa fa-sign-in icon-space" aria-hidden="true"></i> Connexion</label>
            <select name="connexion" id="connexion">
                <option value="on" <?= $configs['connexion'] === 'on' ? 'selected' : '' ?>>Activée</option>
                <option value="off" <?= $configs['connexion'] === 'off' ? 'selected' : '' ?>>Désactivée</option>
            </select>
            <label for="generateurs"><i class="fa-solid fa-inbox icon-space"></i> Accès aux générateurs</label>
            <select name="generateurs" id="generateurs">
                <option value="on" <?= $configs['generateurs'] === 'on' ? 'selected' : '' ?>>Activé</option>
                <option value="off" <?= $configs['generateurs'] === 'off' ? 'selected' : '' ?>>Désactivé</option>
            </select>

            <label for="limit_membre_per_day"><i class="fa-solid fa-gauge-high"></i> Limite générations membre / jour</label>
            <input type="number" name="limit_membre_per_day" id="limit_membre_per_day" value="<?= htmlspecialchars($configs['limit_membre_per_day'] ?? 2) ?>" min="0" required>

            <label for="limit_vip_per_day"><i class="fa-solid fa-gauge-high"></i><i class="fa-solid fa-star"></i> Limite générations Premium / jour</label>
            <input type="number" name="limit_vip_per_day" id="limit_vip_per_day" value="<?= htmlspecialchars($configs['limit_vip_per_day'] ?? 10) ?>" min="0" required>

            <label for="cooldown_membre_minutes"><i class="fa-solid fa-hourglass-half"></i> Cooldown génération membre (en minutes)</label>
            <input type="number" name="cooldown_membre_minutes" id="cooldown_membre_minutes" value="<?= htmlspecialchars($configs['cooldown_membre_minutes'] ?? 20) ?>" min="0" required>

            <label for="cooldown_vip_minutes"><i class="fa-solid fa-hourglass-half"></i><i class="fa-solid fa-star"></i> Cooldown génération Premium (en minutes)</label>
            <input type="number" name="cooldown_vip_minutes" id="cooldown_vip_minutes" value="<?= htmlspecialchars($configs['cooldown_vip_minutes'] ?? 2) ?>" min="0" required>

            <button type="submit">Enregistrer</button>
        </form>
    </div>
</div>
<?php include '../footer.php'; ?>
</body>
</html>
