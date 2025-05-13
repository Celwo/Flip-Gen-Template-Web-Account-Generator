<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'], $_POST['message'])) exit;

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = trim($_POST['message']);

if ($message === '' || strlen($message) > 200) exit;


$stmt = $pdo->prepare("SELECT created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$last = $stmt->fetch();

if ($last && strtotime($last['created_at']) > time() - 5) {
    http_response_code(429); 
    exit;
}


$clean = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');


$stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, username, message) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $username, $clean]);
