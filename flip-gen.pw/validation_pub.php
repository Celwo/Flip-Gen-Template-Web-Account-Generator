<?php
session_start();
include('config.php');

if (!isset($_GET['token'])) {
    header("Location: /accueil");
    exit;
}

$token = $_GET['token'];
$stmt = $pdo->prepare("SELECT * FROM pub_tokens WHERE token = ?");
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

if (!$tokenData) {
    header("Location: /accueil");
    exit;
}

$created = new DateTime($tokenData['created_at']);
$now = new DateTime();
$diff = $now->getTimestamp() - $created->getTimestamp();

if ($diff > 600) { 
    header("Location: /accueil");
    exit;
}

$_SESSION['generateur_id'] = $tokenData['generateur_id'];
$_SESSION['pub_validated'] = true;
$stmt = $pdo->prepare("DELETE FROM pub_tokens WHERE token = ?");
$stmt->execute([$token]);

header("Location: /resultat.php");
exit;
