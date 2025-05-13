
<style>
.site-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    display: flex;
    justify-content: center;
    background-color: transparent;
    padding: 0;
    width: 100%;
    box-sizing: border-box;
}

.footer-content {
    width: 96%;
    max-width: 1400px;
    background-color: #0f0f0f;
    padding: 12px 30px;
    border-radius: 10px 10px 0 0;
    border: 1px solid #1e1e1e;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
    margin: 0 auto;
}

.footer-links a {
    color: #ccc;
    text-decoration: none;
    margin-right: 20px;
    transition: color 0.2s;
    font-size: 14px;
}

.footer-links a:last-child {
    margin-right: 0;
}

.footer-links a:hover {
    color: white;
}

.footer-text {
    font-size: 13px;
    text-align: right;
    flex: 1;
    text-align: end;
}




@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 20px 15px;
    }

    .footer-links {
        margin-bottom: 10px;
    }

    .footer-links a {
        margin: 0 10px;
        display: inline-block;
    }

    .footer-text {
        text-align: center;
        font-size: 12px;
    }

   
}
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #0f0f0f;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid #262626;
    color: white;
    max-width: 600px;
    width: 90%;
    box-shadow: 0 0 20px rgba(126, 126, 126, 0.69);
    position: relative;
    animation: fadeIn 0.3s ease;
}

.close-btn {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 24px;
    font-weight: bold;
    color: #f44336;
    cursor: pointer;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
a {
            text-decoration:none;
            color:#fff;
        }

</style>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-links">
            <a href="/discord"> Discord</a>
            <a href="javascript:void(0);" onclick="openConditionsModal()">Conditions</a>
        </div>
        <div class="footer-text">
            © Par <a href="https://github.com/Celwo">Celwo</a>. Tous droits réservés.
        </div>
    </div>
</footer>
<div id="conditionsModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeConditionsModal()">&times;</span>
    <h2>Conditions d'utilisation</h2>
    <p>
      En utilisant <?= SITE_NAME ?>, vous acceptez de ne pas abuser du service, de ne pas revendre les comptes générés, 
      et de respecter les règles définies par les administrateurs. Toute violation peut entraîner une suspension immédiate.
    </p>
    <p>
      L’équipe <?= SITE_NAME ?> se réserve le droit de modifier ces conditions à tout moment.
    </p>
  </div>
</div>
<script>
function openConditionsModal() {
    document.getElementById('conditionsModal').style.display = 'flex';
}

function closeConditionsModal() {
    document.getElementById('conditionsModal').style.display = 'none';
}


window.onclick = function(event) {
    const modal = document.getElementById('conditionsModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>

