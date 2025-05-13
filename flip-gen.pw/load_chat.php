<?php
include('config.php');

$stmt = $pdo->prepare("
    SELECT m.message, m.username, m.created_at, u.id AS user_id, u.profile_image
    FROM chat_messages m
    JOIN users u ON u.id = m.user_id
    ORDER BY m.created_at DESC
    LIMIT 30
");
$stmt->execute();
$messages = array_reverse($stmt->fetchAll());

foreach ($messages as $msg) {
    $img = !empty($msg['profile_image']) 
        ? 'data:image/png;base64,' . base64_encode($msg['profile_image']) 
        : 'assets/flipflap.png';

    $created_at = new DateTime($msg['created_at']);
    $now = new DateTime();
    $displayTime = $created_at->format('Y-m-d') === $now->format('Y-m-d')
        ? $created_at->format('H:i')
        : $created_at->format('d/m/Y');

    echo '<div class="msg">
            <img src="'.$img.'" alt="avatar">
            <div class="msg-content">
                <strong>
                <a href="view?user=' . intval($msg['user_id']) . '" 
                style="color:#2280A5;text-decoration:none;" 
                target="_top">'
                . htmlspecialchars($msg['username']) . '
                </a>
                </strong>

                <div>' . nl2br(htmlspecialchars($msg['message'])) . '</div>
                <small style="color: #888;">' . $displayTime . '</small>
            </div>
          </div>';
}
