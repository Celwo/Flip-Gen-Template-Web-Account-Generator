<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="assets/flipgen.ico" />
  <link rel="stylesheet" href="style.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
  />
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap"
    rel="stylesheet"
  />
  <style>
   .navbar {
  background-color: #0f0f0f;
  padding: 12px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 0 0 10px 10px;
  border-bottom: 1px solid #1e1e1e;
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-sizing: border-box;
}


body {
  padding-top: 80px; 
}

    .logo-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-container img {
      height: 50px;
    }

    .logo {
      font-size: 1.4em;
      font-weight: bold;
      background: linear-gradient(90deg, white, white);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .navbar-links {
      display: flex;
      gap: 20px;
    }

    .navbar-links a,
    .navbar-links button {
      color: white;
      font-weight: 600;
      text-decoration: none;
      background-color: transparent;
      border: 1px solid transparent;
      border-radius: 6px;
      padding: 8px 14px;
      transition: all 0.3s ease;
      font-family: "Nunito Sans", sans-serif;
    }

    .navbar-links a:hover,
    .navbar-links button:hover {
      background-color: #2e2e2e;
      border-color: #3a3a3a;
    }

    .icon-space {
      margin-right: 8px;
    }

    .menu-toggle {
      display: none;
      cursor: pointer;
    }

    .burger-icon {
      font-size: 24px;
      color: white;
      margin-right: 8px;
    }

    .close-icon {
      display: none;
      font-size: 24px;
      color: white;
      cursor: pointer;
      position: absolute;
      top: 20px;
      right: 20px;
      z-index: 1002;
    }

    .overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(3px);
      background: rgba(0, 0, 0, 0.5);
      z-index: 998;
    }

    @media (max-width: 768px) {
      .menu-toggle {
        display: block;
        z-index: 1001;
      }
      .navbar {
  padding: 16px 20px;
  border-radius: 0 0 10px 10px;
  width: 100%;
  margin: 0 auto;
  box-sizing: border-box;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}


      .navbar-links {
        position: fixed;
        top: 0;
        right: -100%;
        width: 280px;
        height: 100%;
        background-color: #111;
        flex-direction: column;
        padding: 60px 20px;
        gap: 20px;
        transition: right 0.3s ease-in-out;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.5);
        z-index: 999;
      }

      .navbar-links.active {
        right: 0;
      }

      .navbar-links a,
      .navbar-links button {
        padding: 12px 0;
        width: 100%;
        text-align: left;
        font-size: 1rem;
        border-bottom: 1px solid #1f1f1f;
      }

      .overlay.active {
        display: block;
      }
    }
  </style>
</head>

<body>
  <div class="navbar">
    <div class="logo-container">
      <img src="assets/logo.png" alt="Logo" />
      <?php if (isset($_SESSION['username']) && $_SESSION['permissions'] === 'vip'): ?>
        <a style="text-decoration: none;" href="/accueil">
        <div class="logo">PREMIUM</div>
      </a>
      <?php else: ?>
      <a style="text-decoration: none;" href="/accueil">
        <div class="logo"><?= SITE_NAME ?></div>
      </a>
      <?php endif; ?>
    </div>

    <div class="menu-toggle" id="burgerToggle" onclick="toggleMenu()">
      <i class="fas fa-bars burger-icon"></i>
    </div>

    <div class="navbar-links" id="navbarLinks">
      <i class="fas fa-times close-icon" onclick="toggleMenu()"></i>
      <a href="/premium"><i class="fa-solid fa-star icon-space"></i>Premium</a>
      <?php if (isset($_SESSION['username']) && ($_SESSION['permissions'] === 'admin' || $_SESSION['permissions'] === 'fournisseur')): ?>
        <a href="/admin/admin"><i class="fa fa-lock icon-space"></i>Tableau de bord</a>
      <?php endif; ?>
      <?php if (isset($_SESSION['username'])): ?>
        <a href="/generateur"><i class="fa-solid fa-inbox icon-space"></i>Générateurs</a>
        <a href="/compte"><i class="fa fa-user icon-space"></i><?= htmlspecialchars($_SESSION['username']) ?></a>
        <a href="/logout"><i class="fa fa-sign-out icon-space"></i>Se déconnecter</a>
      <?php else: ?>
        <a href="/login"><i class="fa fa-sign-in icon-space"></i>Connexion</a>
        <a href="/discord"><i class="fa-brands fa-discord icon-space"></i>Discord</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>

  <script>
    function toggleMenu() {
      const menu = document.getElementById("navbarLinks");
      const burger = document.getElementById("burgerToggle");
      const overlay = document.getElementById("menuOverlay");
      const closeIcon = document.querySelector(".close-icon");
      const burgerIcon = document.querySelector(".burger-icon");

      menu.classList.toggle("active");
      overlay.classList.toggle("active");

      if (menu.classList.contains("active")) {
        closeIcon.style.display = "block";
        burgerIcon.style.display = "none";
      } else {
        closeIcon.style.display = "none";
        burgerIcon.style.display = "block";
      }
    }
  </script>
</body>
