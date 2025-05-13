<?php

session_start(); // Démarrer la session

// Forcer HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit();
}

// Détecter si c'est un bot (Discord, Facebook, Twitter)
$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
$isBot = strpos($userAgent, 'facebookexternalhit') !== false ||
         strpos($userAgent, 'twitterbot') !== false ||
         strpos($userAgent, 'discordbot') !== false;

// Si c'est un bot, afficher les balises OG pour embed
if ($isBot) {
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Flip-Gen</title>
        <meta property="og:title" content="Flip-Gen">
        <meta property="og:description" content="Un générateur de comptes premium gratuit !">
        <meta property="og:image" content="https://media.discordapp.net/attachments/1357440748638965941/1365787355935412225/standard.gif?ex=68133158&is=6811dfd8&hm=41a2fb332f0ae586a177b28ec97f68e19c631ab92c73b485cee8284410b49f40&=">
        <meta property="og:url" content="https://flip-gen.pw">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Flip-Gen">
        <meta name="twitter:description" content="Un générateur de comptes premium gratuit !">
        <meta name="twitter:image" content="https://media.discordapp.net/attachments/1357440748638965941/1365787355935412225/standard.gif?ex=68133158&is=6811dfd8&hm=41a2fb332f0ae586a177b28ec97f68e19c631ab92c73b485cee8284410b49f40&=">
    </head>
    <body></body>
    </html>';
    exit();
}

// Sinon, rediriger l’utilisateur humain
header("Location: /accueil");
exit();
?>
<html>
    <body>
    <title>Flip-Gen | Free Premium Accounts Generator 2024</title>
    <meta name="description" content="Get free Netflix, Spotify, NordVPN accounts daily with Flip-Gen. Best premium account generator in 2024. No captcha, no survey.">

    <h1>Generate Free Premium Accounts Instantly</h1>

    <p>Welcome to Flip-Gen, the best site to generate free premium accounts for Netflix, Spotify, NordVPN, Deezer and more in 2025.</p>

    <h2>Why use Flip-Gen?</h2>
    <ul>
    <li>Fresh free accounts daily</li>
    <li>Free VPN accounts (NordVPN, ExpressVPN)</li>
    <li>Streaming accounts: Netflix, Spotify, Amazon Prime</li>
    <li>No survey, no captcha, 100% free</li>
    </ul>

    <h2>Start Generating Now</h2>
    <p>Click below to access unlimited premium accounts with our easy-to-use generator.</p>

</body>
</html>