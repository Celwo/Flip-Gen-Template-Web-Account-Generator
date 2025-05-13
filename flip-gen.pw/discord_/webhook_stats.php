<?php
date_default_timezone_set('Europe/Paris');
require_once __DIR__ . '/../config.php';

// 🔐 Token du bot
$botToken = "TON_TOKEN_BOT_";
$channelID = "ID_DU_SALON_OU_TU_SOUHAITES_AFFICHER_LES_STATS";



$lastIDFile = __DIR__ . '/last_message_id.txt';
$lastID = file_exists($lastIDFile) ? trim(file_get_contents($lastIDFile)) : null;
echo "lastID = ";
var_dump($lastID);

$stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmtUsers->fetchColumn();

$stmtGenerators = $pdo->query("SELECT id FROM generateurs");
$generators = $stmtGenerators->fetchAll(PDO::FETCH_COLUMN);

$total_stock = 0;
foreach ($generators as $gen_id) {
    $table_name = "generateur_{$gen_id}_restocks";
    $stmtCheck = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmtCheck->execute([$table_name]);
    if ($stmtCheck->rowCount()) {
        $stmtCount = $pdo->query("SELECT COUNT(*) FROM $table_name");
        $count = $stmtCount->fetchColumn();
        $total_stock += $count;
    }
}

$date = date("d/m/Y H:i:s");

$embed = [
    "title" => "📊 Statistiques du site - " . SITE_NAME,
    "description" => "👥 Membres inscrits : **$total_users**\n🛠 Générateurs actifs : **" . count($generators) . "**\n📦 Comptes en stock : **$total_stock**",
    "color" => hexdec("3498db"),
    "footer" => [ "text" => "Mise à jour automatique" ],
    "timestamp" => date("c")
];

$data = [ "embeds" => [$embed] ];
$json = json_encode($data);

$ch = curl_init();

if ($lastID) {
    echo "➡️ PATCH vers le message ID : $lastID\n";
    curl_setopt($ch, CURLOPT_URL, "https://discord.com/api/v10/channels/$channelID/messages/$lastID");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
} else {
    echo "📬 Envoi d'un nouveau message...\n";
    curl_setopt($ch, CURLOPT_URL, "https://discord.com/api/v10/channels/$channelID/messages");
    curl_setopt($ch, CURLOPT_POST, true);
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bot $botToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
var_dump($responseData);

if (isset($responseData['id'])) {
    file_put_contents($lastIDFile, $responseData['id']);
    echo "🆔 ID du message sauvegardé : {$responseData['id']}\n";
} else {
    echo "⚠️ L'ID du message n’a pas été trouvé dans la réponse.\n";
}
?>
