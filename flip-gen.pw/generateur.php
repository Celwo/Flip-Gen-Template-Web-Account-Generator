<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

include('config.php');
if (isset($_SESSION['generateur_id'])) {
    unset($_SESSION['generateur_id']);
    $_SESSION['notif_discord'] = "Besoin d'aide pour générer un compte ? Rejoins notre Discord pour avoir un coup de main.";
}

$generation_error = null;

$user_id = $_SESSION['user_id'];
$permissions = $_SESSION['permissions'];


$limitKey = $permissions === 'vip' ? 'limit_vip_per_day' : 'limit_membre_per_day';
$getLimit = $pdo->prepare("SELECT `value` FROM config WHERE `key` = ?");
$getLimit->execute([$limitKey]);
$dailyLimit = (int) $getLimit->fetchColumn();


$countGen = $pdo->prepare("SELECT COUNT(*) FROM generations_logs WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$countGen->execute([$user_id]);
$genToday = (int) $countGen->fetchColumn();

$limitReached = ($permissions !== 'admin' && $genToday >= $dailyLimit);
if (isset($_GET['generateur_id']) && !$limitReached) {
  	$_SESSION['generateur_id'] = $_GET['generateur_id'];
    if (in_array($permissions, ['admin', 'vip', 'restock'])) {
        header("Location: /resultat.php");
        exit;
    } else {
      $token = bin2hex(random_bytes(32));
      $now = (new DateTime())->format('Y-m-d H:i:s');

      $stmt = $pdo->prepare("INSERT INTO pub_tokens (token, user_id, generateur_id, created_at) VALUES (?, ?, ?, ?)");
      $stmt->execute([$token, $user_id, $_GET['generateur_id'], $now]);

      $final_url = SITE_URL . '/validation_pub.php?token=' . $token;
      $shrink_api = 'https://shrinkme.io/api?api=' . urlencode(SHRINKME_API_KEY) . '&url=' . urlencode($final_url);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $shrink_api);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      $response = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($response !== false && $http_status == 200) {
          $data = json_decode($response, true);

          if (isset($data['shortenedUrl'])) {
              ob_end_clean();
              header("Location: " . $data['shortenedUrl']);
              exit;
          }
      }

      ob_end_clean();
      echo "<h1 style='color:white; text-align:center; margin-top:50px;'>Erreur de génération de lien publicitaire. Merci de réessayer plus tard.</h1>";
      exit;
    }
}

$query = "SELECT * FROM generateurs";
$stmt = $pdo->prepare($query);
$stmt->execute();
$generateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$checkGen = $pdo->prepare("SELECT `value` FROM config WHERE `key` = 'generateurs'");
$checkGen->execute();
$generateurs_active = $checkGen->fetchColumn() === 'on';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Générateurs - <?= SITE_NAME ?></title>
  <link rel="icon" href="assets/flipgen.ico">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap" rel="stylesheet">
  <script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
 <style>
  body {
    background-color: #0f0f0f;
    color: white;
    font-family: 'Nunito Sans', sans-serif;
    margin: 0;
    padding: 0 20px;
  }

  .content {
    max-width: 1200px;
    margin: auto;
    padding-top: 30px;
  }

  h2 {
    font-size: 2rem;
    margin-bottom: 25px;
    text-align: left;
  }

  .playlist-style {
    display: grid;
    grid-template-columns: repeat(3, 1fr); 
    gap: 20px;
    justify-items: stretch; 
  }

  .generateur-card {
  background-color:#0f0f0f;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #262626;
  text-align: left;
  transition: transform 0.3s;
  display: flex;
  flex-direction: column;
  position: relative;
  gap: 12px;
  width: 100%; 
  box-sizing: border-box; 
  justify-content: space-between;
}

  .generateur-card:hover {
    transform: scale(1.01);
    box-shadow: 0 0 20px rgba(71, 71, 71, 0.5);
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


  .generateur-card h3 {
    margin: 0;
    font-size: 1.1rem;
    color: white;
  }

  .generateur-card p {
    font-size: 0.9rem;
    color: #aaa;
    margin: 0;
    text-align:center;
  }

  .generateur-card a,
  .generateur-card button {
    display: block;
    width: 90%; 
    margin: 10px auto 0 auto; 
    padding: 10px;
    font-size: 0.9rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    color: white;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.3s ease;
    line-height: 1.2;
  }


  .btn-obtenir {
    background-color:rgb(48, 128, 194);
    border:1px solid rgb(80, 78, 78);
  }

  .btn-obtenir:hover {
    background-color:rgb(11, 110, 190);
  }

  .btn-disabled {
    background-color: #333;
    cursor: not-allowed;
  }

  .btn-impossible {
    background-color: #5c5c5c;
    cursor: not-allowed;
  }

  @media (max-width: 768px) {
    .content {
      padding-top: 20px;
    }

    h2 {
      font-size: 1.5rem;
      text-align: center;
    }

    .generateur-card {
      padding: 15px;
    }

    .generateur-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    margin: 0 auto;
    display: block;
    }


    .generateur-card h3 {
      font-size: 1rem;
      text-align: center;
    }

    .generateur-card p {
      text-align: center;
    }
    .playlist-style {
    grid-template-columns: 1fr; 
    }
  }
  .hero-section {
            background-color: #0f0f0f;
            color: white;
            text-align: center;
            padding: 60px 20px;
            margin: 25px auto; 
            border-radius: 12px;
            
           
            width: 96%;
            max-width: 1400px;
            border: 1px solid #1e1e1e;
        }

    .hero-section h3 {
        font-size: 1.875em;
        margin-bottom: 10px;
    }

    .hero-section .highlight {
        color:rgb(229, 241, 237);
    }

    .hero-section p {
        font-size: 1.1em;
        margin-bottom: 25px;
        color: white;
    }

    .hero-button {
        animation: pulse 2s infinite;
        background: #1bc29b;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        text-decoration: none;
        transition: 0.3s ease;
        vertical-align: middle;
    }
    @keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(28, 207, 134, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(28, 207, 134, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(28, 207, 134, 0);
    }
    }
        .hero-button:hover {
        background: #17a88a;
    }
    @media (max-width: 768px) {
    .hero-section {
        padding: 40px 15px;
        margin: 20px auto;
    }

    .hero-section h3 {
        font-size: 1.5em;
    }

    .hero-section p {
        font-size: 1em;
    }

    .hero-button {
        padding: 10px 20px;
        font-size: 0.95em;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding: 30px 10px;
        border-radius: 8px;
    }

    .hero-section h3 {
        font-size: 1.25em;
    }

    .hero-section p {
        font-size: 0.95em;
    }

    .hero-button {
        padding: 8px 16px;
        font-size: 0.9em;
    }
}
.premium-button {
    position: relative;
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    font-weight: bold;
    font-size: 1em;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    background: #111;
    z-index: 1;
    overflow: hidden;
}

.premium-button::before {
    content: '';
    position: absolute;
    inset: 0;
    padding: 2px;
    border-radius: 10px;
    background: conic-gradient(
        from 45deg,
        #fff099,
        #ffd700,
        #ebd47a,
        #ffffff,
        #fff099
    );
    z-index: -1;

    
    mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    mask-composite: exclude;
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;

    box-sizing: border-box;
    pointer-events: none;
}
.premium-button:hover {
    transform: scale(1.05);
    
}
.avis-confirmation {
    margin-top: 10px;
    text-align: center;
}

.avis-link {
    color: #1bc29b;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: color 0.3s ease;
}

.avis-link:hover {
    color: #17a88a;
    text-decoration: underline;
}
.vip-notice {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #262626;
  color: #fff;
  font-weight: bold;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  cursor: help;
  box-shadow: 0 0 6px rgba(27,194,155,0.5);
}
.stock-badge {
  background: #262626;
  color: #fff;
  font-weight: 700;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: help;
  box-shadow: 0 0 10px rgba(110, 110, 110, 0.5);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stock-badge.out-of-stock {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
}

.image-container {
  position: relative;
  width: fit-content;
  margin: 0 auto;
}

.image-container img {
  display: block;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 2px solid rgb(92, 92, 92);
  object-fit: cover;
}
.ads-badge {
  background: linear-gradient(135deg, #ffcc00, #ff9900);
  color: #000;
  font-weight: bold;
  font-size: 0.8rem;
  border-radius: 8px;
  padding: 4px 10px;
  display: inline-block;
  box-shadow: 0 0 8px rgba(255, 204, 0, 0.4);
  font-family: 'Nunito Sans', sans-serif;
  letter-spacing: 1px;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
  cursor: default;
}

.modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  z-index: 9999;
  background-color: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(5px);
  animation: fadeIn 0.3s ease;
}

.modal.show {
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #1f1f1f;
  padding: 20px;
  border-radius: 12px;
  color: white;
  text-align: center;
  max-width: 90%;
  width: 360px;
  animation: scaleIn 0.3s ease;
  position: relative;
}

.modal-content button {
  background: rgb(27, 161, 194);
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  color: #fff;
  margin-top: 15px;
}

.close-btn {
  position: absolute;
  top: 10px; right: 14px;
  font-size: 24px;
  cursor: pointer;
  color: #aaa;
}

.close-btn:hover {
  color: #fff;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.discord-button {
  background-color: #5865F2;
  color: white;
  font-weight: bold;
  padding: 12px 25px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  transition: background-color 0.3s ease, transform 0.2s ease;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.discord-button:hover {
  background-color: #4854c7;
  transform: scale(1.05);
}

</style>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const adTest = document.createElement("script");
    adTest.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js";
    adTest.async = true;
    adTest.onerror = function () {
      const blocker = document.createElement("div");
      blocker.style.position = "fixed";
      blocker.style.top = "0";
      blocker.style.left = "0";
      blocker.style.width = "100%";
      blocker.style.height = "100%";
      blocker.style.backgroundColor = "rgba(0, 0, 0, 0.95)";
      blocker.style.color = "white";
      blocker.style.zIndex = "9999";
      blocker.style.display = "flex";
      blocker.style.flexDirection = "column";
      blocker.style.alignItems = "center";
      blocker.style.justifyContent = "center";
      blocker.innerHTML = `
        <h1 style="font-size: 2rem; margin-bottom: 20px;">🚫 AdBlock détecté</h1>
        <p style="font-size: 1.2rem; max-width: 600px; text-align: center;">Merci de désactiver votre bloqueur de publicité pour accéder à ce contenu. Votre soutien nous aide à maintenir ce service gratuit ❤️</p>
      `;
      document.body.appendChild(blocker);
    };
    document.head.appendChild(adTest);
  });
</script>



</head>
<?php if (isset($_SESSION['notif_discord'])): ?>
    <div style="background-color:rgb(27, 141, 194); color: #fff; padding: 12px; text-align: center; border-radius: 8px; margin: 20px auto; max-width: 600px;">
        <?= htmlspecialchars($_SESSION['notif_discord']); ?>
        <br><a href="/discord.php" style="color: #fff; text-decoration: underline; font-weight: bold;">Rejoindre le Discord</a>
    </div>
    <?php unset($_SESSION['notif_discord']); ?>
<?php endif; ?>
<body>



<?php include 'navbar.php'; ?>

<?php if ($generation_error): ?>
    <div style="
        background-color: #e74c3c;
        color: #fff;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        margin-bottom: 20px;
        border-radius: 8px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;">
        <?= $generation_error ?>
    </div>
<?php endif; ?>


<div class="content">

<div class="hero-section">
<h3>Rejoignez nous sur Discord !</h3>
<p><i class="fa-solid fa-box"></i> Soyez au courant des prochains restock etc..</p>

<div>
     <a href="/discord" target="_blank" class="discord-button">
    <i class="fab fa-discord"></i> Rejoindre notre Discord
    </a>
</div>


</div>
<?php if ($generateurs_active): ?>
  <div class="playlist-style">

    <?php
    foreach ($generateurs as $generateur) {

      $generateur_id = $generateur['id'];
      $table_name = "generateur_{$generateur_id}_restocks";
      $checkTableQuery = $pdo->prepare("SHOW TABLES LIKE ?");
      $checkTableQuery->execute([$table_name]);
      $table_exists = $checkTableQuery->rowCount() > 0;
      $stock_count = 0;

      if ($table_exists) {
          $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM $table_name");
          $stmt_count->execute();
          $stock_count = $stmt_count->fetchColumn();
      }

      $user_id = $_SESSION['user_id'];
      $permissions = $_SESSION['permissions'];
      $stmt = $pdo->prepare("SELECT last_generation FROM users WHERE id = ?");
      $stmt->execute([$user_id]);
      $last_generation = $stmt->fetchColumn();
      $now = new DateTime();
      $last_gen_time = new DateTime($last_generation ?? '2000-01-01');
      if ($permissions === 'admin') {
      $delay = 0;
      } else {
          $delayKey = ($permissions === 'vip') ? 'cooldown_vip_minutes' : 'cooldown_membre_minutes';
          $stmt = $pdo->prepare("SELECT `value` FROM config WHERE `key` = ?");
          $stmt->execute([$delayKey]);
          $delay = (int) $stmt->fetchColumn() ?: 0; 
      }

      $next_allowed = clone $last_gen_time;
      $next_allowed->modify("+{$delay} minutes");
      $can_generate = $now >= $next_allowed;
      if (!$last_generation) {
        $stmt = $pdo->prepare("UPDATE users SET last_generation = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
      }
    ?>

      <div class="generateur-card">
        <?php if ($permissions !== 'vip'): ?> <a href="/premium"><div class="vip-notice" title="Passez à Premium pour supprimer les publicités"><i class="fa fa-info-circle" aria-hidden="true"></i></div></a> <?php endif; ?>
          <img src="<?= $generateur['image_url'] ?>" alt="<?= htmlspecialchars($generateur['nom']) ?>">

        <a style="cursor:default;" href=""><h3><?= htmlspecialchars($generateur['nom']) ?></h3></a>

        <p>
        <?= $stock_count > 0 
        ? '<span class="stock-badge top-right" title="Comptes disponibles">' . $stock_count . '</span>' 
        : '<span class="stock-badge top-right" title="Aucun compte disponible">0</span>' ?>

        </p>

        <?php

        if (!$table_exists || $stock_count <= 0) {
          echo '<a href="/discord.php"><button class="btn-impossible"><i class="fa-solid fa-xmark"></i> Indisponible</button></a>';
        } elseif ($limitReached) {
          if ($permissions === 'membre') {
              echo '<a href="/premium"><button class="btn-impossible">Limite atteinte - Devenir Premium</button></a>';
          } else {
              echo '<button class="btn-impossible" disabled>Limite atteinte</button>';
          }
        } elseif ($can_generate) {
          echo '<a class="btn-obtenir" href="?generateur_id=' . $generateur_id . '"><i class="fa-solid fa-arrow-up-from-bracket"></i> Générer</a>';
        } else {
          $remaining = $now->diff($next_allowed);
          $remaining_seconds = ($remaining->i * 60) + $remaining->s;
          echo '<button class="btn-impossible" disabled data-remaining="' . $remaining_seconds . '"></button>';
        }

        ?>

      </div>

    <?php } ?>

  </div>
  <?php else: ?>
      <div class="hero-section">
    <h3>🛠 Maintenance en cours</h3>
    <p>Les générateurs sont temporairement désactivés. Revenez plus tard ou rejoignez notre Discord pour suivre l’actualité.</p>
    <a href="/discord.php" class="hero-button">Accéder au Discord</a>
  </div>
  <?php endif; ?>

 
    
</div>
<?php if ($permissions !== 'vip'): ?>
<div id="popupModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeModal">&times;</span>
    <h3>📺 Publicité Incluses</h3>
    <p>Pour accéder aux comptes, vous devez regarder une publicité et suivre les étapes jusqu'à la fin. Ne fermez pas la page trop tôt.</p>
    <p>Marre des pub ? <strong>Passer à <a style="text-decoration:none;color:#fff;" href="/premium">Premium</a></strong></p>
    <p>Merci de nous soutenir <3</p>
  </div>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("popupModal");
  const closeBtn = document.getElementById("closeModal");

  if (!sessionStorage.getItem("modal_seen")) {
    modal.classList.add("show");
    sessionStorage.setItem("modal_seen", "1");
  }

  closeBtn.addEventListener("click", function () {
    modal.classList.remove("show");
  });

  window.addEventListener("click", function (event) {
    if (event.target === modal) {
      modal.classList.remove("show");
    }
  });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
  const timers = document.querySelectorAll(".btn-impossible[data-remaining]");

  timers.forEach(button => {
    let remaining = parseInt(button.getAttribute("data-remaining"));

    const update = () => {
      if (remaining <= 0) {
        button.innerText = "Prêt !";
        button.classList.remove("btn-disabled");
        button.classList.add("btn-obtenir");
        button.disabled = false;
        return;
      }

      const minutes = Math.floor(remaining / 60);
      const seconds = remaining % 60;
      button.innerText = `${minutes}m ${seconds.toString().padStart(2, "0")}s`;

      remaining--;
      setTimeout(update, 1000);
    };

    update();
  });
});

</script>
</body>
</html>
<?php include 'footer.php'; ?>