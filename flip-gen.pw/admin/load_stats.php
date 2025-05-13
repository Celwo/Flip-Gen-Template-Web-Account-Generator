<?php
include('../config.php');

$type = $_GET['type'] ?? '';

if ($type === 'generateurs') {
    $stmt = $pdo->query("SELECT nom FROM generateurs");
    while ($row = $stmt->fetch()) echo "🧩 " . htmlspecialchars($row['nom']) . "<br>";
}
elseif ($type === 'vip') {
    $stmt = $pdo->query("SELECT name, email FROM users WHERE permissions = 'vip'");
    while ($row = $stmt->fetch()) echo "⭐ " . htmlspecialchars($row['name']) . " — " . $row['email'] . "<br>";
}
elseif ($type === 'admin') {
    $stmt = $pdo->query("SELECT name, email FROM users WHERE permissions = 'admin'");
    while ($row = $stmt->fetch()) echo "🛠️ " . htmlspecialchars($row['name']) . " — " . $row['email'] . "<br>";
}
elseif ($type === 'fournisseur') {
    $stmt = $pdo->query("SELECT name, email FROM users WHERE permissions = 'fournisseur'");
    while ($row = $stmt->fetch()) echo "📦 " . htmlspecialchars($row['name']) . " — " . $row['email'] . "<br>";
} else {
    echo "Type de statistique non valide.";
}
?>
