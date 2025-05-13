<?php
session_start();
include('../config.php');

// Vérifier si l'utilisateur est connecté et a la permission 'admin' ou 'restock'
if (!isset($_SESSION['username']) || !in_array($_SESSION['permissions'], ['admin', 'fournisseur'])) {
    header("Location: ../login");
    exit;
}

// Récupérer les générateurs
$query = $pdo->query("SELECT * FROM generateurs ORDER BY id DESC");
$generateurs = $query->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour récupérer les emails et mots de passe pour un générateur
function getRestockEmailsPasswords($generateur_id) {
    global $pdo;
    $table_name = 'generateur_' . $generateur_id . '_restocks';
    
    // Récupérer les emails et mots de passe depuis la table correspondante
    $query = $pdo->prepare("SELECT email, password FROM $table_name");
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer le nombre de comptes dans la table générateur_id_restocks
function getRestockCount($generateur_id) {
    global $pdo;
    $table_name = 'generateur_' . $generateur_id . '_restocks';

    // Vérifier si la table existe
    $checkTableQuery = $pdo->prepare("SHOW TABLES LIKE ?");
    $checkTableQuery->execute([$table_name]);
    
    // Si la table existe, compter les lignes
    if ($checkTableQuery->rowCount() > 0) {
        $countQuery = $pdo->prepare("SELECT COUNT(*) AS count FROM $table_name");
        $countQuery->execute();
        $result = $countQuery->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } else {
        // Si la table n'existe pas, retourner 0
        return 0;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restock - Admin</title>
  <link rel="icon" type="image/png" href="../assets/flipgen.ico">
  <link rel="stylesheet" href="admin.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Nunito Sans', sans-serif; background: #0f0f0f; color: #f1f1f1; }
    a { text-decoration: none; color: inherit; }
    button { cursor: pointer; }

    .admin-layout {
      display: flex;
      flex-direction: row;
      min-height: 100vh;
    }
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding-top: 80px; 
      }
    }

    .main-content { flex:1; margin-left: 240px; padding: 30px; transition: margin-left 0.3s ease; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .header h1 { color: #fff; }
    .main { margin-left: 240px; flex: 1; padding: 30px; }
    .sidebar.closed + .main{margin-left:0}
    #search {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #333;
    background-color: #1c1c1c;
    color: white;
    width: 100%;
    max-width: 300px;
    outline: none;
    font-size: 14px;
}

.main-header { display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; margin-bottom: 20px; }
.main-header h1 { color: #1bc29b; font-size: 1.8rem; }
.main-header input { padding: 10px; border: 1px solid #333; border-radius: 8px; background: #1c1c1c; color: #fff; }
.table-wrapper {
  margin-top: 20px;
}

.table-wrapper table {
  width: 100%;
  border-collapse: collapse;
  background: #1a1a1a;
  border-radius: 10px;
  overflow: hidden;
}
.table-wrapper th,
.table-wrapper td {
  padding: 12px;
  text-align: center;
  font-size: 0.95rem;
}
.table-wrapper th {
  background: #121212;
  color: #fff;
}
.table-wrapper tr:nth-child(even) {
  background: #171717;
}
.table-wrapper tr:hover {
  background: #2c2c2c;
}
.img-cell img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 2px solid rgb(123, 129, 128);
  object-fit: cover;
}
.action-btn {
  display: inline-block;
  padding: 8px 18px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #fff;
  background: transparent;
  border: 2px solid #262626;
  border-radius: 6px;
  transition: all 0.25s ease-in-out;
  text-align: center;
}

.action-btn:hover {
  color: #fff;
  border:1px solid rgb(80, 78, 78);
  transform: translateY(-2px);
  box-shadow: 0 0 20px rgba(71, 71, 71, 0.5);
}

@media (max-width: 767px) {
  .table-wrapper {
    margin: 0;
  }
  .table-wrapper table,
  .table-wrapper thead,
  .table-wrapper tbody,
  .table-wrapper th,
  .table-wrapper td,
  .table-wrapper tr {
    display: block;
    width: 100%;
  }
  .table-wrapper thead {
    display: none;
  }
  .table-wrapper tr {
    background: #1a1a1a;
    margin-bottom: 16px;
    border-radius: 8px;
    overflow: hidden;
    padding: 16px 0;
    text-align: center;
  }
  .table-wrapper td {
    display: block;
    padding: 8px 16px;
    border: none;
    font-size: 0.9rem;
    color: #f1f1f1;
  }
 
  .table-wrapper td.img-cell {
    padding: 0;
  }
  .table-wrapper td.img-cell img {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: block;
  }
 
  .table-wrapper td:nth-child(2) {
    font-weight: bold;
    font-size: 1.1rem;
    margin-top: 8px;
  }
 
  .table-wrapper td:nth-child(3) {
    color: #ccc;
    margin-top: 4px;
  }
  
  .table-wrapper td:nth-child(4) {
    margin-top: 12px;
  }
  .table-wrapper td:nth-child(4) .action-btn {
    width: calc(100% - 32px);
    margin: 0 auto;
    display: block;
  }
}



    /* Modal */
    @keyframes fadeIn {
      from { opacity: 0; }
      to   { opacity: 1; }
    }

    @keyframes slideUp {
      from {
        transform: translateY(20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); align-items: center; justify-content: center;animation: fadeIn 0.3s ease-in-out; }
    .modal.active { display: flex; }
    .modal-content { background: #161616; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px; position: relative;animation: slideUp 0.3s ease-out; }
    .modal-content h2 { color:rgb(121, 131, 129); margin-bottom: 15px; text-align: center; }
    .modal-content textarea {
    width: 100%;
    height: 150px;
    padding: 10px;
    border: 1px solid #333;
    background: #111;
    color: #fff;
    border-radius: 6px;
    font-family: 'Nunito Sans', sans-serif;
    transition: border 0.3s ease, box-shadow 0.3s ease;
    }

    .modal-content textarea:hover {
        color: #fff;
    }

    .modal-content textarea:focus {
        border-color: #fff;
        box-shadow: 0 0 5px rgba(119, 128, 126, 0.5);
        outline: none;
    }

    .modal-content .close { position: absolute; top: 10px; right: 15px; font-size: 1.5rem; color: #ccc; }
    .modal-content .close:hover { color: #fff; cursor:pointer;}
    .modal-content button {align: center;}
    .modal-content .save-btn { margin-top: 15px; width: 100%; background: #1bc29b; border: none; color: #121212; padding: 10px; border-radius: 6px; }
    .modal-content .save-btn:hover { background: #17a385; }
    .toggle-menu { background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer; display:none; }
    /* Responsive */
    @media (max-width: 768px) {
      .main { margin-left: 0 !important;
    padding-top: 80px; }
      .main-header { flex-direction: column; gap: 10px; }
      table, th, td { font-size: 0.85rem; }

    }
    @media(max-width:768px) {.toggle-menu{display:block;} .sidebar{transform:translateX(-100%);} body.sidebar-open .sidebar{transform:translateX(0);} .main-content{margin-left:0;}}  
    .breadcrumb {
  font-size: 0.9rem;
  color: #aaa;
  margin-bottom: 1rem;
}

/* Cacher le breadcrumb en mobile */
@media (max-width: 768px) {
  .breadcrumb {
    display: none;
  }
}

  </style>
</head>
<body>

  
  <?php include "navbar.php"; ?>

    <?php if (isset($_SESSION['restock_success'])): ?>
    <div class="toast"><?= $_SESSION['restock_success'] ?></div>
    <script>
        setTimeout(() => {
            document.querySelector('.toast')?.remove();
        }, 4000);
    </script>
    <?php unset($_SESSION['restock_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['restock_error'])): ?>
    <div class="toast"><?= $_SESSION['restock_error'] ?></div>
    <script>
        setTimeout(() => {
            document.querySelector('.toast')?.remove();
        }, 4000);
    </script>
    <?php unset($_SESSION['restock_error']); ?>
    <?php endif; ?>
    <main class="main">
    <header class="header">
    <small class="breadcrumb" style="color: #aaa;"><h3><a href="../accueil"><?= SITE_NAME ?></a> &gt; Restock</h3></small>
        <input type="text" id="search" placeholder="Rechercher..." onkeyup="filterTable()">
        <button class="toggle-menu"><i class="fas fa-bars"></i></button>
    </header>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Image</th><th>Nom</th><th>Stock</th><th>Action</th>
            </tr>
          </thead>
          <tbody id="genTable">
            <?php foreach ($generateurs as $g): $count = getRestockCount($g['id']); ?>
            <tr>
              <td class="img-cell"><img src="<?= htmlspecialchars($g['image_url']) ?>" alt=""></td>
              <td><?= htmlspecialchars($g['nom']) ?></td>
              <td><?= $count ?></td>
              <td><button class="action-btn" onclick="openViewModal(<?= $g['id'] ?>)">Restock</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  

  <!-- Modal -->
  <div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2 style="color:#fff;">Restock Générateur</h2>
        <form id="editForm" method="POST" action="update_emails_passwords.php">
            <input type="hidden" name="generateur_id" id="generateur_id">
            <textarea id="emails_passwords" name="emails_passwords" rows="10" placeholder="Les emails et mots de passe seront affichés ici..."></textarea>
            <button class="action-btn" type="submit">Ajouter</button>
        </form>
    </div>
    </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
  <script>
        // Fonction pour ouvrir la modal de voir
        function openViewModal(id) {
            fetchEmailsPasswords(id); // Charger les emails et mots de passe
            document.getElementById('generateur_id').value = id;
            document.getElementById('viewModal').classList.add('active');
        }

        // Fonction pour fermer la modal de voir
        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
        }

        // Fonction pour récupérer les emails et mots de passe via AJAX
        function fetchEmailsPasswords(id) {
            fetch('fetch_emails_passwords.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    let emailsPasswordsText = "";
                    data.forEach(item => {
                        // Ajouter l'email et le mot de passe dans le champ textarea sous la forme email:password
                        emailsPasswordsText += item.email + ":" + item.password + "\n";
                    });

                    // Remplir le champ de texte avec les données
                    document.getElementById('emails_passwords').value = emailsPasswordsText;
                })
                .catch(error => console.error('Error:', error));
        }

        // Fonction de recherche des générateurs
        function filterGenerateurs() {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('.generateur-row');

            rows.forEach(row => {
                const nameCell = row.querySelector('td:nth-child(2)');
                const name = nameCell.textContent.toLowerCase();
                row.style.display = name.includes(searchInput) ? '' : 'none';
            });
        }
    </script>
<script>
const toggle=document.querySelector('.toggle-menu');toggle.addEventListener('click',()=>document.body.classList.toggle('sidebar-open'));
</script>
</body>
</html>
<?php include '../footer.php'; ?>
