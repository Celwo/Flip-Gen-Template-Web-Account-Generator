<?php
session_start();
include('config.php');


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/flipgen.ico">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js"></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background-color: #121212;
            font-family: 'Nunito Sans', sans-serif;
            height: 100%;
            color: #fff;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #chat {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            border-bottom: 1px solid #333;
        }

        .msg {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
        }

        .msg img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .msg-content {
            background: #1e1e1e;
            padding: 8px 12px;
            border-radius: 8px;
            max-width: 80%;
        }

        .msg-content strong {
            color:rgb(204, 218, 214);
            display: block;
            margin-bottom: 4px;
        }

        #chat-form {
            display: flex;
            padding: 10px;
            background-color: #0f0f0f;
            border-top: 1px solid #222;
        }

        #chat-form input {
            flex: 1;
            padding: 8px;
            border: none;
            background-color: #1a1a1a;
            color: white;
            border-radius: 6px;
        }

        #chat-form button {
            margin-left: 10px;
            background-color:rgb(34, 128, 165);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .emoji-picker {
        z-index: 9999 !important;
         }

    </style>
</head>
<body>
    <div class="chat-container">
    <div id="chat"></div>
    
    <?php if (isset($_SESSION['username'])): ?>
    <form id="chat-form">
    <button type="button" id="emoji-btn" style="margin-right: 8px;">🤓</button>
    <input type="text" id="message" placeholder="Écris un message..." maxlength="200" required>
    <button type="submit">Envoyer</button>
    </form>
    <?php else: ?>
        <p style="color:#fff;position:center;">Connectez vous pour discuter.</p>
    <?php endif; ?>
    <div id="emoji-picker-container"></div>
    </div>

<script>
function loadChat() {
    fetch('load_chat.php')
        .then(res => res.text())
        .then(data => {
            const chat = document.getElementById('chat');
            chat.innerHTML = data;
            chat.scrollTop = chat.scrollHeight;
        });
}

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = document.getElementById('message').value;
    fetch('send_chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    }).then(() => {
        document.getElementById('message').value = '';
        loadChat();
    });
});

setInterval(loadChat, 3000);
loadChat();
</script>
<script type="module">
  import { EmojiButton } from 'https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js';

  const button = document.querySelector('#emoji-btn');
  const input = document.querySelector('#message');

  const picker = new EmojiButton({
    theme: 'dark',
    position: 'top-end',
    emojiSize: '1.2em'
  });

  picker.on('emoji', selection => {
    input.value += selection.emoji;
    input.focus();
});


  button.addEventListener('click', () => {
    picker.togglePicker(button);
  });
</script>



</body>
</html>
