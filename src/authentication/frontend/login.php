<?php
error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', 0);
session_start();

if($_SESSION['is_logged']) {
    header('location: ../../index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniBank - Login</title>
    <link rel="stylesheet" href="login.css?=<?php echo time();?>">
    <link rel="stylesheet" href="../../variables.css?=<?php echo time();?>">
</head>
<body>
    <div class="maincontainer">
        <header class="navbar">
        <div class="nbcontainer">
            <div class="logo">
            <img src="../../../assets/logo lungo7.png" alt="logo Unibank">
            </div>
            <div class="menu">
                <ul>
                    <li>
                        <a href="../../index.php">Home</a>
                    </li>
                </ul>
            </div>
        </div>
        </header>
        <div class="container">
            <div class="loginbox">
                <div class="loginheader">
                    <div class="logo2">
                        <img src="../../../assets/logo.png" alt="logo Unibank">
                    </div>
                    <h2>Bentornato!</h2>
                    <p>Accedi al tuo account Unibank</p>
                </div>
                <form action="../backend/elaborazioneLogin.php" method="POST">
                    <div class="inputbox">
                        <label for="email">Email</label>
                        <input type="text" name="email" placeholder="esempio@dominio.com" required>
                    </div>
                    <div class="inputbox">
                        <label for="password">Password</label>
                        <input type="password" name="password" placeholder="●●●●●●●●●" required>
                        <button type="button" class="togglepassword"><img src="../../../assets/showpw.png" alt="Show password"></button>
                        <a href="resetpassword.php">Password dimenticata?</a>
                    </div>
                    <button class="loginbtn" type="submit">Login</button>
                    <p class="donthaveaccount">Non hai un account? <a href="signup.php">Registrati</a></p>
                </form>
            </div>
        </div>
        <footer>
            <div class="ftcontainer">
                <div class="ftcolumn">
                    <h2>Pages</h2>
                    <ul>
                        <li>
                            <a href="login.php">Login</a>
                        </li>
                        <li>
                            <a href="signup.php">Registrazione</a>
                        </li>
                        <li>
                            <a href="../../index.php">Home</a>
                        </li>
                        <li>
                            <a href="../../../install/install.html">Installazione</a>
                        </li>
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li>
                            <a href="#">Cerca Materiale</a>
                        </li>
                        <li>
                            <a href="#">Profilo</a>
                        </li>
                    </ul>
                </div>
                <div class="ftcolumn">
                    <h2>Manca la tua università o la tua facoltà?</h2>
                    <a href="#"><button class="contactbtn">Contattaci</button></a>
                </div>
            </div>
            <p class="copyright">© 2026 UniBank™. All rights reserved.</p>
        </footer>
    </div>
    
    <div class="popup-overlay" id="popupInvalidEmail">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p>Email non valida. Controlla il formato dell'indirizzo.</p>
            <button class="popup-btn" onclick="closePopup('popupInvalidEmail')">Chiudi</button>
        </div>
    </div>

    <div class="popup-overlay" id="popupPwRequired">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p>La password è obbligatoria.</p>
            <button class="popup-btn" onclick="closePopup('popupPwRequired')">Chiudi</button>
        </div>
    </div>

    <div class="popup-overlay" id="popupWrongCreds">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p>Email o password errate.</p>
            <button class="popup-btn" onclick="closePopup('popupWrongCreds')">Chiudi</button>
        </div>
    </div>

    <div class="popup-overlay" id="popupBlocked">
        <div class="popup-box">
            <h3>Account bloccato</h3>
            <p>Il tuo account è stato bloccato dall'amministratore. Contatta il supporto per maggiori informazioni.</p>
            <button class="popup-btn" onclick="closePopup('popupBlocked')">Chiudi</button>
        </div>
    </div>

    <div class="popup-overlay" id="popupGenericError">
        <div class="popup-box">
            <h3>Errore</h3>
            <p>Si è verificato un errore durante il login. Riprova più tardi.</p>
            <button class="popup-btn" onclick="closePopup('popupGenericError')">Chiudi</button>
        </div>
    </div>
</body>
<script>
function openPopup(id){
    document.getElementById(id)?.classList.add('active');
}
function closePopup(id){
    document.getElementById(id)?.classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.togglepassword');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const img = this.querySelector('img');
            if (input.type === 'password') {
                input.type = 'text';
                img.src = '../../../assets/hidepw.png';
                img.alt = 'Hide password';
            } else {
                input.type = 'password';
                img.src = '../../../assets/showpw.png';
                img.alt = 'Show password';
            }
        });
    });

    <?php if(isset($_GET['invalid']) && $_GET['invalid'] == '1'): ?>
    openPopup('popupInvalidEmail');
    <?php endif; ?>

    <?php if(isset($_GET['pwreq']) && $_GET['pwreq'] == '1'): ?>
    openPopup('popupPwRequired');
    <?php endif; ?>

    <?php if(isset($_GET['wrong']) && $_GET['wrong'] == '1'): ?>
    openPopup('popupWrongCreds');
    <?php endif; ?>

    <?php if(isset($_GET['blocked']) && $_GET['blocked'] == '1'): ?>
    openPopup('popupBlocked');
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == '1'): ?>
    openPopup('popupGenericError');
    <?php endif; ?>
});
</script>
</html>