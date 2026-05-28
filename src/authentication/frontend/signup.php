<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniBank - Registrati</title>
    <link rel="stylesheet" href="signup.css?=<?php echo time();?>">
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
                    <h2>Crea il tuo account</h2>
                    <p>Inizia a condividere e acquistare dispense</p>
                </div>
                <form action="../backend/inserimentoDBcredenziali.php" method="POST">
                    <div class="formgrid">
                        <div class="inputbox">
                            <label for="username">Username</label>
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="inputbox">
                            <label for="email">Email</label>
                            <input type="email" name="email" placeholder="esempio@dominio.com" required>
                        </div>
                        <div class="inputbox">
                            <label for="password">Password</label>
                            <input type="password" class="pwinput1" name="password" placeholder="●●●●●●●●●" required>
                            <div class="pwpopup1">
                                <h4 style="display: flex; justify-content: flex-start; align-items: center; gap: 10px; color: var(--color-yellow-primary); margin-bottom: 15px; font-size: 18px;">
                                    <img src="../../../assets/warning.png" alt="!" style="width: 20px"> Attenzione</h4>
                                <p class="infop" style="margin-bottom: 10px">La password che inserisci deve avere le seguenti caratteristiche:</p>
                                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px;">
                                    <li class="li">
                                    <span style="color: var(--color-yellow-primary)">•</span>
                                    <p class="infop">Minimo 8 caratteri</p>
                                    </li>
                                    <li class="li">
                                        <span style="color: var(--color-yellow-primary)">•</span>
                                        <p class="infop">Almeno una lettera maiuscola e una minuscola</p>
                                    </li>
                                    <li class="li">
                                        <span style="color: var(--color-yellow-primary)">•</span>
                                        <p class="infop">Almeno un carattere speciale</p>
                                    </li>
                                    <li class="li">
                                        <span style="color: var(--color-yellow-primary)">•</span>
                                        <p class="infop">Minimo un numero</p>
                                    </li>
                                </ul>
                            </div>
                            <button type="button" class="togglepassword"><img src="../../../assets/showpw.png" alt="Show password"></button>
                        </div>
                        <div class="inputbox">
                            <label for="confermapassword">Conferma Password</label>
                            <input type="password" class="pwinput2" name="confermapassword" placeholder="●●●●●●●●●" required>
                            <div class="pwpopup2">
                                <h4 style="display: flex; justify-content: flex-start; align-items: center; gap: 10px; color: var(--color-yellow-primary); margin-bottom: 15px; font-size: 18px;">
                                    <img src="../../../assets/warning.png" alt="!" style="width: 20px"> Attenzione</h4>
                                <p class="infop" style="margin-bottom: 10px">La password che inserisci deve avere le seguenti caratteristiche:</p>
                                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px;">
                                    <li class="li"">
                                    <span style="color: var(--color-yellow-primary)">•</span>
                                    <p class="infop">Minimo 8 caratteri</p>
                                    </li>
                                    <li class="li">
                                        <span style="color: var(--color-yellow-primary)">•</span>
                                        <p class="infop">Almeno una lettera maiuscola e una minuscola</p>
                                    </li>
                                    <li class="li"">
                                    <span style="color: var(--color-yellow-primary)">•</span>
                                    <p class="infop">Almeno un carattere speciale</p>
                                    </li>
                                    <li class="li">
                                        <span style="color: var(--color-yellow-primary)">•</span>
                                        <p class="infop">Minimo un numero</p>
                                    </li>
                                </ul>
                            </div>
                            <button type="button" class="togglepassword"><img src="../../../assets/showpw.png" alt="Show password"></button>
                        </div>
                        <div class="inputbox fullwidth">
                            <label for="universita">Università</label>
                            <select name="universita" required>
                                <option value="">Seleziona università</option>
                                <?php
                                    require_once __DIR__ . '/../../../config.php';
                                    $conn = db_connect();

                                    $query = "SELECT * FROM universita";
                                    $ris = mysqli_query($conn, $query);
                                    while($row = mysqli_fetch_assoc($ris)){
                                        $id = $row['id_universita'];
                                        $nomeUniversita = $row['nome'];
                                        $cittaSede = $row['citta_sede'];
                                        echo "<option value=\"$id\">$cittaSede - $nomeUniversita</option>";
                                    }
                                    mysqli_close($conn);
                                ?>
                                
                            </select>
                        </div>
                        <div class="inputbox fullwidth">
                            <label for="facolta">Facoltà</label>
                            <select name="facolta" required>
                                <option value="">Seleziona facoltà</option>
                                <?php
                                    require_once __DIR__ . '/../../../config.php';
                                    $conn = db_connect();
                                    $query = "SELECT * FROM facolta";
                                    $ris = mysqli_query($conn, $query);
                                    while($row = mysqli_fetch_assoc($ris)){
                                        $id = $row['id_facolta'];
                                        $nomeFacolta = $row['nome'];
                                        echo "<option value=\"$id\">$nomeFacolta</option>";
                                    }
                                    mysqli_close($conn);
                                ?>
                            </select>
                        </div>
                    </div>
                    <button class="loginbtn" type="submit">Registrati</button>
                    <p class="donthaveaccount">Hai già un account? <a href="login.php">Accedi</a></p>
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

    <div class="popup-overlay" id="popupWeakPw">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p>La password inserita non è sicura. Assicurati che abbia almeno 8 caratteri, includa lettere maiuscole e minuscole, numeri e caratteri speciali.</p>
            <button class="popup-btn" onclick="closePopupWeakPw()">Chiudi</button>
        </div>
    </div>

    <div class="popup-overlay" id="popupPwMismatch">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p>Le password non coincidono. Per favore, assicurati che i due campi password siano identici.</p>
            <button class="popup-btn" onclick="closePopupPwMismatch()">Chiudi</button>
        </div>
    </div>
    
</body>
<script>
function openPopupWeakPw() {
    document.getElementById('popupWeakPw').classList.add('active');
}
function closePopupWeakPw() {
    document.getElementById('popupWeakPw').classList.remove('active');
}

function openPopupPwMismatch() {
    document.getElementById('popupPwMismatch').classList.add('active');
}
function closePopupPwMismatch() {
    document.getElementById('popupPwMismatch').classList.remove('active');
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

    <?php if(isset($_GET['weakpw']) && $_GET['weakpw'] == '1'): ?>
    openPopupWeakPw();
    <?php endif; ?>

    <?php if(isset($_GET['pwdmismatch']) && $_GET['pwdmismatch'] == '1'): ?>
    openPopupPwMismatch();
    <?php endif; ?>
});
</script>
</html>