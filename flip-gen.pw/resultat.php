<?php
session_start();
include('config.php');
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: /login");
    exit;
}

$permissions = $_SESSION['permissions'] ?? 'membre';

if (!in_array($permissions, ['vip', 'admin', 'restock'])) {
    if (!isset($_SESSION['generateur_id']) || !isset($_SESSION['pub_validated']) || $_SESSION['pub_validated'] !== true) {
        header("Location: /accueil");
        exit;
    }
    unset($_SESSION['pub_validated']); 
}

$email = null;
$password = null;
if (isset($_SESSION['generateur_id'])) {
    $generateur_id = $_SESSION['generateur_id'];

    $query = "SELECT * FROM generateurs WHERE id = :generateur_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':generateur_id', $generateur_id);
    $stmt->execute();
    $generateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($generateur) {
        unset($_SESSION['generateur_id']);
        $table_name = "generateur_{$generateur_id}_restocks";
        $query_stock = "SELECT * FROM $table_name LIMIT 1";
        $stmt_stock = $pdo->prepare($query_stock);
        $stmt_stock->execute();
        $stock_data = $stmt_stock->fetch(PDO::FETCH_ASSOC);

        if (!$stock_data) {
            header("Location: accueil");
            exit;
        }

        $email = $stock_data['email'];
        $password = $stock_data['password'];

        $query_delete = "DELETE FROM $table_name WHERE id = :stock_id LIMIT 1";
        $stmt_delete = $pdo->prepare($query_delete);
        $stmt_delete->bindParam(':stock_id', $stock_data['id']);
        $stmt_delete->execute();

        $generator_name = $generateur['nom'];
        $generated_account = $email . ':' . $password;

        $username = $_SESSION['username'] ?? 'inconnu';
        $stmt = $pdo->prepare("INSERT INTO generations_logs (user_id, username, generator_name, generated_account) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $username, $generator_name, $generated_account]);

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $updateGen = $pdo->prepare("UPDATE users SET last_generation = ? WHERE id = ?");
        $updateGen->execute([$now, $_SESSION['user_id']]);
        
        if (isset($_SESSION['username'])) {
            $stmt = $pdo->prepare("UPDATE users SET generations = generations + 1 WHERE name = ?");
            $stmt->execute([$_SESSION['username']]);
        }

    } else {
        header("Location: accueil");
        exit;
    }
} else {
    header("Location: accueil");
    exit;
}


?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récuperer votre compte - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/flipgen.ico">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
    background-color: #0f0f0f;
    font-family: 'Nunito Sans', sans-serif;
    margin: 0;
    padding: 0;
    color: #fff;
}

.content {
    padding: 40px 20px;
    text-align: center;
    max-width: 800px;
    margin: auto;
}

h1 {
    font-size: 36px;
    margin-bottom: 20px;
    background: linear-gradient(to right, #ffffff, #bdbdbd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.info-box {
    margin: 30px auto;
    padding: 20px;
    background-color: #1a1a1a;
    border-radius: 10px;
    border: 1px solid #333;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 0 10px rgba(238, 165, 9, 0.96)0.1);
}

.info-box h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #fff;
}

.info-box p {
    font-size: 16px;
    color: #ccc;
}

.premium-box {
    margin-top: 30px;
    background-color: #1c1c1c;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #333;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.1);
}

.premium-box h2 {
    font-size: 24px;
    color: #f39c12;
    margin-bottom: 10px;
}

.premium-box p {
    font-size: 15px;
    color: #bbb;
}

.btn-premium {
    padding: 12px 25px;
    background-color:rgb(26, 156, 216);
    color: white;
    font-size: 16px;
    text-decoration: none;
    border-radius: 8px;
    display: inline-block;
    margin-top: 20px;
    transition: background-color 0.3s ease;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(131, 150, 138, 0.5);
}

.btn-premium:hover {
    background-color:rgb(26, 113, 163);
}
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

body {
  display: flex;
  flex-direction: column;
}

.content {
  flex: 1; 
}

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="content">
       
    <?php if (isset($_SESSION['username']) && $_SESSION['permissions'] === 'membre' ): ?>
        <div class="premium-box">
            <h3>Marre des pubs ? Passez à Premium !</h3>
            <p>Profitez d'une expérience sans publicités et bénéficiez d'avantages exclusifs en devenant membre Premium.</p>
            <a href="/premium" class="btn-premium">Découvrez notre offre Premium</a>
        </div>
    <?php endif; ?>
        
    <div class="info-box" id="compteBox">
    <h3>Votre Compte</h3>
    <?php if ($email && $password): ?>
        <p id="email"><strong>Email :</strong> <?php echo htmlspecialchars($email); ?></p>
        <p id="password"><strong>Mot de passe :</strong> <?php echo htmlspecialchars($password); ?></p>
        <a href="/accueil" style="text-decoration:none;"><p>Retourner à l'accueil
        </p></a>

    <?php else: ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    </div>
        
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

