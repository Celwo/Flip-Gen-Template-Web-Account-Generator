<?php
session_start(); 
include('config.php');

if (!$pdo) {
    die("Erreur de connexion à la base de données.");
}

try {
    $query_users_count = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $users_count = $query_users_count->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Erreur de requête pour le nombre d'inscrits : " . $e->getMessage());
}

try {
    $top_stmt = $pdo->prepare("SELECT id AS user_id, name, generations, profile_image FROM users ORDER BY generations DESC LIMIT 5");
    $top_stmt->execute();
    $top_users = $top_stmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur de requête pour le top generateurs : " . $e->getMessage();
    exit();
}

try {
    $stmt = $pdo->prepare("
    SELECT l.username, l.generator_name, l.created_at, u.id AS user_id, u.profile_image
    FROM generations_logs l
    JOIN users u ON u.name = l.username
    ORDER BY l.created_at DESC 
    LIMIT 5


");
$stmt->execute();
$last_generations = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT SUM(generations) AS total_generations FROM users");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_generations = $result['total_generations'] ? $result['total_generations'] : 0;
} catch (PDOException $e) {
    echo "Erreur de requête pour le total de generations: " . $e->getMessage();
    exit();
}
if (isset($_SESSION['user_id'])) {
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
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO : " . $e->getMessage());
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/flipgen.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <style>
.connected-dashboard {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
  margin-bottom: 30px;
}
.generateur-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    margin: 0 auto;
    display: block;
    border: 2px solid rgb(48, 128, 194);
}
.block {
  flex: 1 1 300px;
  max-width: 400px;
  background-color: #121212;
  border-radius: 12px;
  padding: 20px;
  color: white;
  text-align: center;
}
.block-content {
    height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: stretch;
}

.last-gen-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.last-gen-list li {
  padding: 8px 0;
  border-bottom: 1px solid #333;
  font-size: 14px;
  line-height: 1.4;
  list-style: none;
}


a {
    text-decoration:underline;
    color:#fff;
}
.chatbox-frame {
    width: 100%;
    height: 100%;
    min-height: 280px; 
    border: none;
    border-radius: 10px;
    display: block;
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
    
    <div class="hero-section">
            <?php if (!isset($_SESSION['username'])): ?>
                <div class="avatar-section">
                <label class="generateur-card">
                    <img src="assets/flipflap.png" alt="Avatar" id="profile-image">
                </label>
                 </div>
            <h3>Bienvenue sur <span class="highlight"><?= SITE_NAME ?></span> 👋</h3>
            <p><span id="typed-flip"></span><span class="blinking-cursor">|</span></p>
            <a href="/generateur" class="hero-button">Commencer maintenant</a>
            <?php else: ?>
                <div class="avatar-section">
                <label class="generateur-card">
                    <img src="<?= htmlspecialchars($profile_image); ?>" alt="Avatar" id="profile-image">
                </label>
                 </div>
            <h3>Salut <span class="highlight"><?= htmlspecialchars($_SESSION['username']) ?> !</span> 👋</h3>
            <p><span id="typed-text"></span><span class="blinking-cursor">|</span></p>
            <?php endif; ?>
        </div>

        <div class="connected-dashboard">
        
        <div class="block">
            <div class="corner-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div class="block-header"><h3>Dernières générations</h3></div>
            <div class="block-content">
            <ul class="last-gen-list">
            <?php foreach ($last_generations as $gen): ?>
                <li style="display: flex; align-items: center; gap: 10px;">
                <img src="<?= !empty($gen['profile_image']) ? 'data:image/png;base64,' . base64_encode($gen['profile_image']) : 'assets/flipflap.png'; ?>"
                    alt="avatar"
                    style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                <div style="text-align: left;">
                    <strong><a href="view?user=<?= $gen['user_id'] ?>">
                        <?= htmlspecialchars($gen['username']) ?>
                    </a></strong> a généré 
                    <em><?= htmlspecialchars($gen['generator_name']) ?></em>
                    <br><small style="color: #aaa;">
                        <?= date('H:i - d/m', strtotime($gen['created_at'])) ?>
                    </small>
                </div>
                </li>

            <?php endforeach; ?>
            </ul>

            </div>
        </div>

        <div class="block">
            <div class="corner-icon"><i class="fa-solid fa-comments"></i></div>
            <div class="block-header"><h3>Discussion</h3></div>
            <div class="block-content">
            <iframe src="chatbox.php" class="chatbox-frame"></iframe>
            </div>
        </div>
        <div class="block">
            <div class="corner-icon"><i class="fa-solid fa-star"></i></div>
            <div class="block-header"><h3>Classement</h3></div>
            <ul class="last-gen-list">
            <?php $rank = 1; foreach ($top_users as $user): ?>
            <li style="display: flex; align-items: center; gap: 10px;">
                <img src="<?= !empty($user['profile_image']) ? 'data:image/png;base64,' . base64_encode($user['profile_image']) : 'assets/flipflap.png'; ?>"
                    alt="avatar"
                    style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                <div style="text-align: left;">
                    <strong>
                        <a href="view?user=<?= $user['user_id'] ?>">
                            <?= htmlspecialchars($user['name']) ?>
                        </a>
                    </strong>
                    <br><small style="color: #aaa;">
                        <?= intval($user['generations']) ?> générations
                    </small>
                </div>
                </li>
                <?php $rank++; endforeach; ?>
            </ul>


        </div>
    </div>
      
    
    
</div> 



 
    
</div>
<script>
const phrasesFlip = [
    "Générateur de comptes #1 depuis 2021",
    "Créer ton compte maintenant !",
    "Tu attends quoi pour nous rejoindre ?",
    "Accède à des dizaines de services premium gratuitement !",
    "On t'attend !"
];

let flipPhrase = 0;
let flipChar = 0;
let flipDeleting = false;
const flipSpeed = 70;
const flipPause = 1300;
const typedFlip = document.getElementById('typed-flip');

function typeFlip() {
    const fullText = phrasesFlip[flipPhrase];
    typedFlip.textContent = fullText.substring(0, flipChar);

    if (!flipDeleting && flipChar < fullText.length) {
        flipChar++;
        setTimeout(typeFlip, flipSpeed);
    } else if (flipDeleting && flipChar > 0) {
        flipChar--;
        setTimeout(typeFlip, flipSpeed / 2);
    } else {
        flipDeleting = !flipDeleting;
        if (!flipDeleting) flipPhrase = (flipPhrase + 1) % phrasesFlip.length;
        setTimeout(typeFlip, flipPause);
    }
}

if (typedFlip) document.addEventListener('DOMContentLoaded', typeFlip);
</script>

<script>
const phrases = [
    "Quoi de neuf <?= htmlspecialchars($_SESSION['username']) ?> ?",
    "Un nouveau compte à générer ?",
    "Besoin d’un accès premium ?",
    "Toujours pas sur notre Discord ?",
    "Pourquoi pas nous laisser un avis sur Trustpilot ?"
];

let currentPhrase = 0;
let currentChar = 0;
let isDeleting = false;
const speed = 70;
const pause = 1300;
const typedText = document.getElementById('typed-text');

function type() {
    const fullText = phrases[currentPhrase];
    typedText.textContent = fullText.substring(0, currentChar);

    if (!isDeleting && currentChar < fullText.length) {
        currentChar++;
        setTimeout(type, speed);
    } else if (isDeleting && currentChar > 0) {
        currentChar--;
        setTimeout(type, speed / 2);
    } else {
        isDeleting = !isDeleting;
        if (!isDeleting) currentPhrase = (currentPhrase + 1) % phrases.length;
        setTimeout(type, pause);
    }
}

document.addEventListener('DOMContentLoaded', type);
</script>

    
</body>


</html>
<?php include 'footer.php'; ?>