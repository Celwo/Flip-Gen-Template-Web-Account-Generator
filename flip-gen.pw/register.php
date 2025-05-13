<?php
session_start();
include 'config.php';

if (isset($_SESSION['username'])) {
    header("Location: /accueil");
    exit();
}

$errors = [];
$success_message = "";

$check = $pdo->prepare("SELECT value FROM config WHERE `key` = 'inscription'");
$check->execute();
$inscriptionStatus = $check->fetchColumn();

if ($inscriptionStatus === 'off') {
    die('<div style="color: red; text-align: center; font-family: sans-serif; margin-top: 50px;">⚠️ L\'inscription est actuellement désactivée. Revenez plus tard.</div>');
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_secret = $secret_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $verify = curl_exec($ch);
    curl_close($ch);

    $captcha_success = json_decode($verify);
    if (!$captcha_success || !$captcha_success->success) {
        $errors['captcha'] = "Veuillez valider le reCAPTCHA.";
    }

    if (empty($name)) $errors['name'] = "Le nom est obligatoire.";
    if (empty($email)) $errors['email'] = "L'email est obligatoire.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "L'email n'est pas valide.";
    if (empty($password)) $errors['password'] = "Le mot de passe est obligatoire.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) $errors['email'] = "L'email est déjà utilisé.";

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) $errors['name'] = "Le pseudo est déjà pris.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password])) {
            $_SESSION['notification'] = "Votre compte a été créé avec succès !";
            $_SESSION['notif_type'] = "success";
            echo '<script>setTimeout(function() { window.location.href = "login"; }, 2500);</script>';

            $webhookURL = $webhook_logs;
            $data = [
                "content" => "🔒 **Nouvel Utilisateur !**\n👤 Pseudo : **$name**\n📧 Email : `$email`\n🕒 Heure : `" . date("d/m/Y H:i:s") . "`"
            ];
            $options = [
                "http" => [
                    "method" => "POST",
                    "header" => "Content-Type: application/json",
                    "content" => json_encode($data)
                ]
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhookURL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);

        } else {
            $errors['general'] = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Flip-Gen</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="assets/flipgen.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    body {
      background-color: #0f0f0f;
      font-family: 'Nunito Sans', sans-serif;
      color: white;
      margin: 0;
      padding: 0;
    }

    .form-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
      min-height: 100vh;
    }

    .form-box {
      background-color: #0f0f0f;
      padding: 30px;
      border-radius: 12px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 12px rgba(0,0,0,0.6);
      border: 1px solid #1e1e1e;
      box-sizing: border-box;
    }

    .form-box h2 {
      font-size: 1.6rem;
      margin-bottom: 25px;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    .form-group input {
      width: 100%;
      padding: 14px 15px 14px 45px;
      background-color: #1c1c1c;
      border: 1px solid #333;
      border-radius: 8px;
      color: white;
      font-size: 15px;
      box-sizing: border-box;
    }

    .form-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #777;
    }

    .form-box button {
      background-color:#0f0f0f;
      color: white;
      font-weight: bold;
      padding: 12px 24px;
      border-radius: 8px;
      border: 1px solid #262626;
      transition: transform 0.2s ease-in-out, box-shadow 0.2s;
      display: block;
      margin: 20px auto 0 auto;

    }

    .form-box button:hover {
      transform: scale(1.03);
            box-shadow: 0 0 20px rgba(27, 194, 155, 0.5);
            cursor:pointer;
    }

    .link-text {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .link-text a {
      color: #1bc29b;
      text-decoration: none;
      font-weight: bold;
    }

    .error-message {
      font-size: 13px;
      color: #ff4d4d;
      margin-top: 8px;
    }

    .g-recaptcha {
      margin: 10px 0;
      display: flex;
      justify-content: center;
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
.toast.success {
    background-color: #28a745;
}
.toast.error {
    background-color: #dc3545;
}
@keyframes fadein {
    from { opacity: 0; top: 0px; }
    to { opacity: 1; top: 20px; }
}
@keyframes fadeout {
    from { opacity: 1; top: 20px; }
    to { opacity: 0; top: 0px; }
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

<div class="form-wrapper">
  <div class="form-box">
    <h2><i class="fas fa-user-plus"></i> Inscription</h2>
    <form method="POST" action="register.php">
      <div class="form-group">
        <i class="fas fa-user"></i>
        <input type="text" name="name" placeholder="Pseudo" required>
        <?php if (isset($errors['name'])) echo '<div class="error-message">'.$errors['name'].'</div>'; ?>
      </div>

      <div class="form-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" required>
        <?php if (isset($errors['email'])) echo '<div class="error-message">'.$errors['email'].'</div>'; ?>
      </div>

      <div class="form-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <?php if (isset($errors['password'])) echo '<div class="error-message">'.$errors['password'].'</div>'; ?>
      </div>

      <div class="form-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
        <?php if (isset($errors['confirm_password'])) echo '<div class="error-message">'.$errors['confirm_password'].'</div>'; ?>
      </div>

      <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
      <?php if (isset($errors['captcha'])) echo '<div class="error-message">'.$errors['captcha'].'</div>'; ?>

      <button type="submit">S'inscrire</button>
    </form>
    <div class="link-text">
      Déjà un compte ? <a href="login">Se connecter</a>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>