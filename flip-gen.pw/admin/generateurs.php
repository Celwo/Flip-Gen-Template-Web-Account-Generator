<?php
session_start();
include('../config.php');

if (!isset($_SESSION['username']) || $_SESSION['permissions'] !== 'admin') {
    header("Location: ../login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $image_url = htmlspecialchars($_POST['image_url']);

    if (!empty($nom) && !empty($description) && !empty($image_url)) {
        $stmt = $pdo->prepare("INSERT INTO generateurs (nom, description, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $description, $image_url]);
        header("Location: /admin/generateurs");
        exit;
    }
}
if (isset($_POST['modifier'])) {
    $id = $_POST['generateur_id'];
    $nom = $_POST['nom'];
    $image_url = $_POST['image_url'];
    $description = $_POST['description'];
    $update_stmt = $pdo->prepare("UPDATE generateurs SET nom = ?, image_url = ?, description = ? WHERE id = ?");
    $update_stmt->execute([$nom, $image_url, $description, $id]);
    header("Location: /admin/generateurs");
    exit;
}
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $generateur_id = $_GET['delete_id'];
    $table_name = "generateur_{$generateur_id}_restocks";
    $checkTableQuery = $pdo->prepare("SHOW TABLES LIKE ?");
    $checkTableQuery->execute([$table_name]);
    if ($checkTableQuery->rowCount() > 0) {
        $dropTableQuery = $pdo->prepare("DROP TABLE $table_name");
        $dropTableQuery->execute();
    }
    $stmt = $pdo->prepare("DELETE FROM generateurs WHERE id = ?");
    $stmt->execute([$generateur_id]);
    header("Location: /admin/generateurs");
    exit;
}
$query = $pdo->query("SELECT * FROM generateurs ORDER BY id DESC");
$generateurs = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateurs - Admin</title>
    <link rel="icon" type="image/png" href="../assets/flipgen.ico">
    <link rel="stylesheet" href="admin.css">
    <style>
body {
    background-color: #0f0f0f;
    color: white;
    font-family: 'Nunito Sans', sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
}


.open-modal-btn:hover {
    transform: scale(1.02);
    cursor:pointer;
}

#search {
    padding: 12px;
    border-radius: 8px;
    border: none;
    background-color: #1a1a1a;
    color: white;
    flex: 1;
    min-width: 250px;
    max-width: 300px;
    font-size: 14px;
    outline: none;
    border: 1px solid #333;
}

.generateur-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}

.generateur-card {
    background-color: #1a1a1a;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 0 12px rgba(0, 255, 180, 0.06);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    border: 1px solid #2e2e2e;
}

.generateur-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 0 20px rgba(142, 145, 144, 0.43);
}

.generateur-card img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 2px solid rgb(122, 126, 125);
    background-color: #0f0f0f;
}

.generateur-card h3 {
    font-size: 1.2em;
    margin: 10px 0;
    color: #ccc;
}

.generateur-card p {
    font-size: 0.9em;
    color: #ccc;
    margin-bottom: 15px;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}


.delete-btn {
    background: #0f0f0f !important;
    color: #ccc !important;
    border: 1px solid  #adb3b1 !important;
    padding: 10px 20px !important;
    border-radius: 8px !important;
    font-weight: bold;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: 10px;
}

.delete-btn:hover {
    color: rgb(247, 40, 40) !important;
}

.edit-btn {
    background: #0f0f0f !important;
    color: #ccc !important;
    border: 1px solid  #adb3b1 !important;
    padding: 10px 20px !important;
    border-radius: 8px !important;
    font-weight: bold;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: 10px;
    cursor:pointer;
}

.edit-btn:hover {
    color: rgb(221, 228, 231) !important;
}

html, body {
            overflow-x: hidden;
            max-width: 100vw;
}

.modal-content button[type="submit"],
.open-modal-btn {
    background: #0f0f0f !important;
    color: #ccc;
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 8px;
    border: 1px solid  #adb3b1;
    transition: transform 0.3s ease;
    transition: background-color 0.3s, box-shadow 0.3s;
    max-width: 250px;
    margin: 0 auto 25px auto; 
    display: block; 
}

.modal-content button[type="submit"]:hover,
.open-modal-btn:hover {
    transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
}



.modal {
    display: none; 
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1000;

    justify-content: center;
    align-items: center;
    padding: 20px;
    animation: fadeIn 0.3s ease-in-out;
}


.modal-content {
    background-color: #1f1f1f;
    padding: 30px;
    border-radius: 16px;
    max-width: 500px;
    width: 100%;
    color: white;
    position: relative;
    box-shadow: 0 8px 30px rgba(6, 226, 163, 0.15);
    border: 1px solid #2e2e2e;
    animation: slideUp 0.3s ease-out;
    transition: all 0.3s ease;
}

.modal-content h2 {
    margin-bottom: 20px;
    font-size: 24px;
    color:rgb(194, 199, 197);
    text-align: center;
}

.modal-content input {
    width: 90% !important;
    padding: 8px 10px;
    margin-bottom: 12px;
    background-color: #111;
    border: 1px solid #333;
    border-radius: 6px;
    color: white;
    font-size: 13px;
    transition: border 0.2s ease;
    resize: vertical;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.modal-content textarea {
    width: 90% !important; 
    padding: 8px 10px;
    margin-bottom: 12px;
    background-color: #111;
    border: 1px solid #333;
    border-radius: 6px;
    color: white;
    font-size: 13px;
    transition: border 0.2s ease;
    resize: vertical;
    display: block;
    margin-left: auto;
    margin-right: auto;
}


.modal-content input:focus,
.modal-content textarea:focus {
    border-color:rgb(145, 151, 150);
    outline: none;
}


.modal-content button {
    width: 100%;
    padding: 12px;
    background-color: #06E2A3;
    color: #1a1a1a;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal-content button:hover {
    background-color: #05c495;
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    color: #aaa;
    cursor: pointer;
    transition: color 0.2s ease;
}

.close:hover {
    color: white;
}

/* Responsive */
@media (max-width: 600px) {
    .modal-content {
        padding: 20px;
        border-radius: 12px;
    }

    .modal-content h2 {
        font-size: 20px;
    }

    .modal-content input,
    .modal-content textarea {
        font-size: 14px;
    }

    .modal-content button {
        padding: 10px;
    }
}

@keyframes fadeIn {
    from { background-color: rgba(0, 0, 0, 0); }
    to { background-color: rgba(0, 0, 0, 0.7); }
}

@keyframes slideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}


</style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
    <button class="open-modal-btn" onclick="openModal()">Créer un Générateur</button>
        <div class="header">
            <input type="text" id="search" placeholder="Rechercher un générateur..." onkeyup="filterGenerateurs()">
            <button class="toggle-menu"><i class="fas fa-bars"></i></button>
        </div>

        <div class="generateur-list" id="generateurList">
            <?php foreach ($generateurs as $gen): ?>
                <div class="generateur-card">
                    <img src="<?php echo htmlspecialchars($gen['image_url']); ?>" alt="Image" style="width: 100px; height: 100px;">
                    <h3><?php echo htmlspecialchars($gen['nom']); ?></h3>
                    <p><?php echo htmlspecialchars($gen['description']); ?></p>
                    <div class="action-buttons">
                    <a href="generateurs.php?delete_id=<?php echo $gen['id']; ?>" class="delete-btn" onclick="return confirm('Supprimer ce générateur ?');">Supprimer</a>
                    <button class="edit-btn" onclick="openEditModal(<?php echo $gen['id']; ?>, '<?php echo addslashes($gen['nom']); ?>', '<?php echo addslashes($gen['image_url']); ?>', '<?php echo addslashes($gen['description']); ?>')">Modifier</button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h2>Créer un Générateur</h2>
            <form action="generateurs.php" method="POST">
                <input type="text" name="nom" placeholder="Nom" required>
                <input type="text" name="image_url" placeholder="URL du logo (.png/jpg/jpeg)" required>
                <textarea name="description" placeholder="Description" required></textarea>
                <button type="submit" name="ajouter">Créer</button>
            </form>
        </div>
    </div>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Modifier le Générateur</h2>
            <form action="generateurs.php" method="POST">
                <input type="hidden" name="generateur_id" id="generateur_id">
                <input type="text" name="nom" id="nom" placeholder="Nom" required>
                <input type="text" name="image_url" id="image_url" placeholder="URL de l'image" required>
                <textarea name="description" id="description" placeholder="Description" required></textarea>
                <button type="submit" name="modifier">Modifier</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nom, image_url, description) {
            document.getElementById('generateur_id').value = id;
            document.getElementById('nom').value = nom;
            document.getElementById('image_url').value = image_url;
            document.getElementById('description').value = description;
            document.getElementById('editModal').style.display = "flex";
        }
        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }
        function closeCreateModal() {
            document.getElementById("modal").style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById("modal")) {
                closeCreateModal();
            } else if (event.target == document.getElementById("editModal")) {
                closeEditModal();
            }
        }

        function openModal() {
            document.getElementById("modal").style.display = "flex";
        }

        function filterGenerateurs() {
            let search = document.getElementById("search").value.toLowerCase();
            let cards = document.getElementsByClassName("generateur-card");
            for (let card of cards) {
                let name = card.getElementsByTagName("h3")[0].innerText.toLowerCase();
                card.style.display = name.includes(search) ? "block" : "none";
            }
        }
    </script>
    <?php include '../footer.php'; ?>
</body>
</html>
