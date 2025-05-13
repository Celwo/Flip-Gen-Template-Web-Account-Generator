<?php
session_start();
include('../config.php');

if (!isset($_SESSION['username']) || !in_array($_SESSION['permissions'], ['admin', 'fournisseur'])) {
    header("Location: ../login");
    exit;
}

$generateur_id = $_POST['generateur_id'];
$emails_passwords = trim($_POST['emails_passwords']);
$table_name = 'generateur_' . $generateur_id . '_restocks';

$stmt_check_table = $pdo->prepare("SHOW TABLES LIKE ?");
$stmt_check_table->execute([$table_name]);

if ($stmt_check_table->rowCount() == 0) {
    $create_table_sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL
    )";
    $pdo->exec($create_table_sql);
}

$count = 0;
$errors = [];

if (empty($emails_passwords)) {
    $deleteQuery = $pdo->prepare("DELETE FROM $table_name");
    $deleteQuery->execute();
    $_SESSION['restock_error'] = "Tous les comptes ont été supprimés.";

    $stmt_gen = $pdo->prepare("SELECT nom, image_url FROM generateurs WHERE id = ?");
    $stmt_gen->execute([$generateur_id]);
    $gen_info = $stmt_gen->fetch(PDO::FETCH_ASSOC);

    $webhookUrl = $webhook_logs;

    $embed = [
        "title" => "🗑️ Générateur vidé",
        "description" => "Tous les comptes du générateur **{$gen_info['nom']}** ont été supprimés.",
        "color" => hexdec("FF0000"),
        "author" => [
            "name" => "Logs - Admin",
            "icon_url" => "https://dev.flip-gen.pw/assets/flipflap.png"
        ],
        "image" => [
            "url" => $gen_info['image_url']
        ],
        "footer" => [ "text" => "Par " . $_SESSION['username'] ],
        "timestamp" => date("c")
    ];

    $data = [
        "embeds" => [$embed]
    ];

    $options = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/json",
            "content" => json_encode($data)
        ]
    ];

    $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);


} else {
    $lines = explode("\n", $emails_passwords);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        $lineParts = explode(":", $line);
        if (count($lineParts) === 2) {
            $email = trim($lineParts[0]);
            $password = trim($lineParts[1]);

            $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM $table_name WHERE email = ? AND password = ?");
            $checkQuery->execute([$email, $password]);

            if ($checkQuery->fetchColumn() == 0) {
                $insertQuery = $pdo->prepare("INSERT INTO $table_name (email, password) VALUES (?, ?)");
                $insertQuery->execute([$email, $password]);
                $count++;
            }
        } else {
            $errors[] = $line;
        }
    }

    if ($count > 0) {
        $_SESSION['restock_success'] = "$count compte(s) ont été restock avec succès.";
    }

    if (!empty($errors)) {
        $_SESSION['restock_error'] = "Mauvais format (mail:pass ou user:pass uniquement)";
    }

    if ($count > 0) {
        $stmt_gen = $pdo->prepare("SELECT nom, image_url FROM generateurs WHERE id = ?");
        $stmt_gen->execute([$generateur_id]);
        $gen_info = $stmt_gen->fetch(PDO::FETCH_ASSOC);

        $webhookUrl = $webhook_restock;

        $embed = [
            "title" => "📦 Restock de __{$count}__ compte(s) **{$gen_info['nom']}**",
            "color" => hexdec("00FF00"),
            "author" => [
                "name" => "flip-gen.pw",
                "icon_url" => "https://dev.flip-gen.pw/assets/flipflap.png"
            ],
            "footer" => [ "text" => "Par : " . $_SESSION['username'] ],
            "timestamp" => date("c")
        ];
        if (!empty($gen_info['image_url']) && filter_var($gen_info['image_url'], FILTER_VALIDATE_URL)) {
            $embed["image"] = [ "url" => $gen_info['image_url'] ];
        }
        $data = [
            "content" => "<@&" . DISCORD_ROLE_ID . ">",
            "embeds" => [$embed]
        ];

        $options = [
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json",
                "content" => json_encode($data)
            ]
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);


        file_put_contents('webhook_log.txt', json_encode([
            'httpCode' => $httpCode,
            'response' => $response,
            'error' => $error,
            'data_sent' => $data
        ], JSON_PRETTY_PRINT));

    }
}

header("Location: restock");
exit;


?>
