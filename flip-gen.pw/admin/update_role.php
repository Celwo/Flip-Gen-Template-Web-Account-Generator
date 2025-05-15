<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    $vip_expiration = !empty($_POST['vip_expiration']) ? $_POST['vip_expiration'] : null;

    // Vérifie si la session admin est valide
    if (!isset($_SESSION['username']) || $_SESSION['permissions'] !== 'admin') {
        $_SESSION['role_update_error'] = "Accès refusé.";
        header("Location: utilisateurs");
        exit;
    }

    // Vérifier que l'utilisateur ciblé n’est pas un admin
    $stmt = $pdo->prepare("SELECT name, permissions FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && $user['permissions'] !== 'admin') {
        // Si VIP mais pas de date : erreur
        if ($new_role === 'vip' && !$vip_expiration) {
            $_SESSION['role_update_error'] = "Veuillez définir une date d'expiration pour un utilisateur VIP.";
            header("Location: utilisateurs");
            exit;
        }

        // Mise à jour du rôle
        $stmt_update = $pdo->prepare("UPDATE users SET permissions = ?, vip_expiration = ? WHERE id = ?");
        $stmt_update->execute([$new_role, $new_role === 'vip' ? $vip_expiration : null, $user_id]);

        $_SESSION['role_update_success'] = "Le rôle de <strong>" . htmlspecialchars($user['name']) . "</strong> a été mis à jour en <strong>$new_role</strong>.";

        // Log Discord
        $webhookUrl = $webhook_logs;

        $adminWhoChanged = $_SESSION['username'];
        $vipExpireText = ($new_role === 'vip' && $vip_expiration) ? "\n📅 Expire le : " . date('d/m/Y', strtotime($vip_expiration)) : "";

        $embed = [
            "username" => "Flip-Gen Admin",
            "avatar_url" => "https://flip-gen.pw/assets/flipgen.ico",
            "embeds" => [[
                "title" => "🔁 Rôle attribué",
                "color" => 3447003,
                "fields" => [
                    [
                        "name" => "👤 Utilisateur",
                        "value" => $user['name'],
                        "inline" => true
                    ],
                    [
                        "name" => "🛡️ Nouveau rôle",
                        "value" => $new_role . $vipExpireText,
                        "inline" => true
                    ],
                    [
                        "name" => "🔧 Modifié par",
                        "value" => $adminWhoChanged,
                        "inline" => false
                    ]
                ],
                "footer" => [ "text" => "Panel Admin Flip-Gen" ],
                "timestamp" => date("c")
            ]]
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json",
                'content' => json_encode($embed)
            ]
        ];
        @file_get_contents($webhookUrl, false, stream_context_create($options));
    }

    header("Location: utilisateurs");
    exit;
}
?>
