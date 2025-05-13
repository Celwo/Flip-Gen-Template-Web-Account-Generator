<?php 
include 'config.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT name, permissions, profile_image FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $username = $user['name'];
        $permissions = $user['permissions'];
        $profile_image = $user['profile_image'] ? 'data:image/png;base64,' . base64_encode($user['profile_image']) : "https://i.imgur.com/b43kH9C.png";
    } else {
        echo "Utilisateur non trouvé.";
        exit();
    }
} catch (PDOException $e) {
    
    error_log("Erreur PDO : " . $e->getMessage());
    echo "Une erreur est survenue.";
    exit();
}


function resizeImage($source, $maxWidth, $maxHeight) {
    list($width, $height) = getimagesize($source);
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    $src = @imagecreatefromstring(file_get_contents($source));
    if (!$src) return false; 
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    $tempFile = tempnam(sys_get_temp_dir(), 'img_');
    imagepng($dst, $tempFile);
    imagedestroy($src);
    imagedestroy($dst);
    return $tempFile;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_image"])) {
    $uploadOk = 1;
    $image = $_FILES["profile_image"];

   
    if ($image["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($image["tmp_name"])) {
        $_SESSION['notification'] = "Erreur lors du téléchargement du fichier.";
        $_SESSION['notif_type'] = "error";
        $uploadOk = 0;
    }

    
    $check = getimagesize($image["tmp_name"]);
    if ($check === false) {
        $_SESSION['notification'] = "Le fichier n'est pas une image valide.";
        $_SESSION['notif_type'] = "error";
        $uploadOk = 0;
    }

    // ✅ Limite la taille selon rôle
    $maxFileSize = ($permissions == 'admin' || $permissions == 'restock' || $permissions == 'vip') ? 20000000 : 2000000;
    if ($image["size"] > $maxFileSize) {
        $_SESSION['notification'] = "Image trop volumineuse. Taille max : " . ($maxFileSize / 1000000) . " Mo.";
        $_SESSION['notif_type'] = "error";
        $uploadOk = 0;
    }

    
    $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png"];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['notification'] = "Format non autorisé. Utilisez jpg, jpeg ou png.";
        $_SESSION['notif_type'] = "error";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        $resizedImage = resizeImage($image["tmp_name"], 200, 200);
        if (!$resizedImage || filesize($resizedImage) > $maxFileSize) {
            $_SESSION['notification'] = "Image invalide ou trop volumineuse après traitement.";
            $_SESSION['notif_type'] = "error";
            header("Location: /compte");
            exit();
        }

        $imageData = file_get_contents($resizedImage);

        $stmt_update = $pdo->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :id");
        $stmt_update->bindParam(':profile_image', $imageData, PDO::PARAM_LOB);
        $stmt_update->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt_update->execute();

        $_SESSION['notification'] = "Image mise à jour avec succès !";
        $_SESSION['notif_type'] = "success";
    }

    header("Location: /compte");
    exit();
}



$is_admin = ($user['permissions'] == 'admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/flipgen.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - <?= SITE_NAME ?></title>
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
        border-bottom: 2px solid #aaaaaa;
        color: #aaaaaa;
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
        border: 2px solid rgb(48, 128, 194);
        object-fit: cover;
        background-color: #0f0f0f;
    }

    .upload-overlay {
        position: absolute;
        right: 0;
        bottom: 0;
        background:rgb(48, 128, 194);
        color: white;
        padding: 8px;
        border-radius: 40%;
        transition: background 0.3s;
    }

    .upload-overlay:hover {
        background: rgb(82, 161, 226);
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
    box-shadow: 0 0 10px rgba(116, 116, 116, 0.65);
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
<h1>Mon Compte</h1>

<div class="settings-tabs">
    <button class="active">Paramètres</button>
    <button disabled>Notifications</button> 
</div>

<div class="settings-content">
<form class="upload-form" action="" method="POST" enctype="multipart/form-data" id="image-upload-form">
    <div class="avatar-section">
        <label for="file-upload" class="avatar-wrapper">
            <img src="<?= htmlspecialchars($profile_image); ?>" alt="Avatar" id="profile-image">
            <span class="upload-overlay"><i class="fas fa-camera"></i></span>
        </label>
        <input type="file" name="profile_image" id="file-upload" accept="image/*" onchange="document.getElementById('image-upload-form').submit();">
    </div>
</form>
    <?php if ($permissions === 'vip'): ?>
        <span class="vip-username">
            <?= htmlspecialchars($username) ?>
            <span class="vip-crown" title="Premium">👑</span>
        </span>
    <?php else: ?>
        <?= htmlspecialchars($username) ?>
    <?php endif; ?>
    <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($_SESSION['email']) ?></p>

    <p><strong>Comptes générés :</strong> 
    <?php
        $stmt = $pdo->prepare("SELECT generations FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        echo intval($stmt->fetchColumn());
    ?>
    </p>
</div>
<hr>
<div class="recent-accounts">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> Vos 5 derniers comptes générés</h3>
    <?php
        $stmt_logs = $pdo->prepare("SELECT generator_name, generated_account, created_at FROM generations_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt_logs->execute([$user_id]);
        $logs = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <?php if ($logs): ?>
        <div class="account-grid">
            <?php foreach ($logs as $log): ?>
                <div class="account-card">
                    <div class="account-info">
                        <h4><i class="fa-solid fa-tag"></i></i> <?= htmlspecialchars($log['generator_name']) ?></h4>
                        <p><i class="fa-solid fa-user"></i></i> <?= htmlspecialchars($log['generated_account']) ?></p>
                    </div>
                    <div class="account-date">
                        <?= date('d/m/Y', strtotime($log['created_at'])) ?>
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
