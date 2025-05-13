<?php
include('../config.php');

if (isset($_GET['id'])) {
    $generateur_id = (int)$_GET['id'];
    $table_name = 'generateur_' . $generateur_id . '_restocks';
    $query = $pdo->prepare("SELECT email, password FROM $table_name");
    $query->execute();
    $data = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} else {
    echo json_encode([]);
}
?>
