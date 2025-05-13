<?php
session_start();
include 'config.php';

if (isset($_SESSION['username'])) {
    header("Location: /accueil");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $secret_key,
        'response' => $recaptcha_response
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $captcha_verify = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($captcha_verify);

    if (!$response || !$response->success) {
        $error_message = "Vérification reCAPTCHA échouée.";
    } else {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $error_message = '';

        if (!empty($email) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT id, name, email, password, permissions FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $stmt = $pdo->prepare("SELECT value FROM config WHERE `key` = 'connexion'");
                $stmt->execute();
                $connexion_status = $stmt->fetchColumn();

                if ($connexion_status === 'off' && !in_array($user['permissions'], ['admin', 'fournisseur'])) {
                    $error_message = "Les connexions sont désactivées pour le moment. Seuls les administrateurs peuvent se connecter.";
                } else {
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['permissions'] = $user['permissions'];
                    $_SESSION['notification'] = "Connecté avec succès !";
                    $_SESSION['notif_type'] = "success";
                    header("Location: login.php");
                    $date = date("d/m/Y H:i:s");
                    $pseudo = $user['name'];
                    $mail = $user['email'];
                    $webhookURL = $webhook_logs;

                    $data = [
                        "content" => "🔓 **Connexion**\n👤 User : **$pseudo**\n📧 Mail : $mail\n🕒 Heure : `$date`"
                    ];

                    $curl = curl_init($webhookURL);
                    curl_setopt_array($curl, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => json_encode($data),
                        CURLOPT_TIMEOUT => 5,
                    ]);

                    $response = curl_exec($curl);
                    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);
                    exit;
                }
            } else {
                $error_message = "Email ou mot de passe incorrect.";
            }
        } else {
            $error_message = "Tous les champs doivent être remplis.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="/assets/flipgen.ico">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="login-container">
    <div class="login-form">
        <h2><i class="fa fa-sign-in icon-space" aria-hidden="true"></i>Connexion</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="login-form">
            <div class="input-container">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required autocomplete="email">
            </div>
            <div class="input-container">
                <i class="fas fa-key"></i>
                <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
            </div>
            <button class="g-recaptcha" 
                data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"
                data-callback="onSubmit"
                data-action="login">
                Se connecter
            </button>
            <script>
                function onSubmit(token) {
                    document.getElementById("login-form").submit();
                }
            </script>
        </form>

        <div class="register-link">
            <p>Je n'ai pas de compte. <a href="/register">M'inscrire</a></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>