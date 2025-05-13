<?php
// Le nom de ton générateur
define('SITE_NAME', 'Template');

// ta clé de site Google recaptcha v2 invisible
define('RECAPTCHA_SITE_KEY', 'ta_clé_de_site_recaptcha');

// ta clé secrète Google recaptcha v2 invisible
$secret_key = 'ta_clé_secrète';

// Domaine du site (à changer)
define('SITE_URL', 'https://dev.flip-gen.pw');

// ta clé API https://shrinkme.io (pour les pubs)
define('SHRINKME_API_KEY', 'ta_clé_api');

// Webhook pour les logs
$webhook_logs = "ton_webhook_logs";

// Webhook Restock
$webhook_restock = "ton_webhook_restock";

// Role Discord à mentionner lors des restock 
define('DISCORD_ROLE_ID', 'id_du_role');

// Configuration de la base de données
$host = "";  
$dbname = "";  
$username = "";  
$password = "";  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données (config.php) : " . $e->getMessage());
}
$pdo->exec("UPDATE users SET permissions = 'membre', vip_expiration = NULL WHERE permissions = 'vip' AND vip_expiration IS NOT NULL AND vip_expiration < NOW()");

$current_path = $_SERVER['REQUEST_URI'];


?>
