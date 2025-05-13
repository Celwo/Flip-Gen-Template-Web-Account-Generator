<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: /login");
    exit();
}
$user = null;
$expiration_vip = null;

$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['permissions'] === 'vip') {
    $expiration_vip = $user['vip_expiration'];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium - Flip-Gen</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/assets/flipgen.ico">
    <style>
   
    body {
        background-color: #0f0f0f;
        color: #fff;
        font-family: 'Nunito Sans', sans-serif;
        margin: 0;
        padding: 0;
    }

    .subscription-card {
        background: linear-gradient(145deg, #141414, #1f1f1f);
        border: 1px solid #2c2c2c;
        border-radius: 16px;
        padding: 40px 30px;
        max-width: 600px;
        margin: 60px auto;
        box-shadow: 0 0 20px rgba(101, 107, 109, 0.48);
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }


    .subscription-card .title {
        font-size: 2rem;
        color: rgb(196, 148, 43);
        margin-bottom: 10px;
        font-weight: 900;
        letter-spacing: 1px;
    }

    .subscription-card .price {
        font-size: 1.4rem;
        color: #ccc;
        margin-bottom: 25px;
        font-weight: 600;
    }

    .subscription-card .benefits {
        list-style: none;
        padding: 0;
        margin-bottom: 30px;
        color: #ddd;
        text-align: left;
        max-width: 400px;
        margin-inline: auto;
    }

    .subscription-card .benefits li {
        margin-bottom: 10px;
        font-size: 1rem;
        position: relative;
        padding-left: 24px;
    }

    .subscription-card .benefits li::before {
        content: "✔";
        position: absolute;
        left: 0;
        color: #1bc29b;
    }

    .subscription-card .expiration {
        color: #ccc;
        font-size: 0.95rem;
        margin-top: 15px;
    }

    .subscribe-button {
        background-color: rgb(219, 143, 43);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: bold;
        text-decoration: none;
        transition: background 0.3s ease, transform 0.2s ease;
        display: inline-block;
        box-shadow: 0 0 15px rgba(255, 219, 61, 0.4);
    }

    .subscribe-button:hover {
        background-color: rgb(206, 160, 35);
        transform: scale(1.05);
    }

    @media (max-width: 768px) {
        .subscription-card {
            margin: 40px 20px;
            padding: 30px 20px;
        }

        .subscription-card .title {
            font-size: 1.5rem;
        }

        .subscription-card .price {
            font-size: 1.2rem;
        }

        .subscription-card .benefits {
            font-size: 0.95rem;
        }

        .subscribe-button {
            font-size: 0.95rem;
            padding: 10px 20px;
        }
    }

    @media (max-width: 480px) {
        .subscription-card {
            padding: 25px 15px;
        }

        .subscription-card .title {
            font-size: 1.3rem;
        }

        .subscription-card .price {
            font-size: 1.1rem;
        }

        .subscription-card .benefits li {
            font-size: 0.9rem;
        }

        .subscribe-button {
            font-size: 0.9rem;
            padding: 10px 18px;
        }
    }
</style>


 

</head>
<body>
<?php include 'navbar.php'; ?>

<div class="subscription-card">
    <?php if ($expiration_vip): ?>
    <h1 class="title">✨ Vous êtes Premium !</h1>
    <?php else: ?>
    <h1 class="title">✨ Passez à Premium !</h1>
    <?php endif; ?>
    <?php if ($user['permissions'] === 'membre'): ?>
    <p class="price">8,50€ / mois</p>
    <?php endif; ?>
    <ul class="benefits">
        <li>✅ Accès aux générateurs exclusifs</li>
        <li>⚡ Cooldown réduit à 5 minutes</li>
        <li>🚫 Aucune publicité</li>
        <li>🎖️ Badge Premium sur votre profil</li>
    </ul>

    <?php if ($expiration_vip): ?>
    <p class="expiration">⏳ Expire le <strong><?= date('d/m/Y à H:i', strtotime($expiration_vip)) ?></strong></p>
    <?php elseif ($user['permissions'] === 'membre'): ?>
    <a href="/discord" class="subscribe-button">✨ Passer à Premium</a>
    <?php endif; ?>
</div>


<?php include 'footer.php'; ?>
</body>
</html>
