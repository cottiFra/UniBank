<?php
session_start();
require_once __DIR__ . '/../config.php';

$conn = db_connect();

$queryHeader = '
    SELECT d.id_dispensa, d.titolo, d.prezzo, u.username, d.bloccata AS dispensaBloc
    FROM dispense d, utenti u
    WHERE d.id_utente = u.id_utente
    AND u.bloccato = 0
    AND d.approvata = 1
    AND d.bloccata = 0
    ORDER BY d.data_caricamento DESC
    LIMIT 3
';
$resultHeader = mysqli_query($conn, $queryHeader);

$currentUserId = $_SESSION['user_id'] ?? 0;
$queryHero ="
    SELECT d.id_dispensa, d.titolo, d.descrizione, d.prezzo, u.username, m.nome as materia, f.nome as facolta, uni.nome as universita, d.bloccata AS dispensaBloc,
    (SELECT COUNT(*) FROM likes l WHERE d.id_dispensa = l.id_dispensa) AS numLikes,
    (SELECT COUNT(*) FROM likes WHERE id_dispensa = d.id_dispensa AND id_utente = $currentUserId) AS hasLiked
    FROM dispense d, utenti u, materiaperfacolta mpf, materia m, facolta f, universita uni
    WHERE d.id_utente = u.id_utente
    AND d.id_materiaperfacolta = mpf.id_materiaperfacolta
    AND mpf.id_materia = m.id_materia
    AND mpf.id_facolta = f.id_facolta
    AND u.id_universita = uni.id_universita
    AND u.bloccato = 0
    AND d.approvata = 1
    AND d.bloccata = 0
    ORDER BY d.data_caricamento DESC
    LIMIT 4
";
$resultHero = mysqli_query($conn, $queryHero);

if(!isset($_SESSION['is_logged'])){
    $_SESSION['is_logged'] = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../assets/logowhitebg.png">
    <title>UniBank - HomePage</title>
    <link rel="stylesheet" href="index.css?=<?php echo time();?>">
    <link rel="stylesheet" href="variables.css?=<?php echo time();?>">
    <style>
        .popup-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .popup-overlay.active { display: flex; }
        .popup-box { background: #fff; border-radius: 12px; padding: 30px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .popup-box h3 { color: #1b2a5e; margin-bottom: 12px; }
        .popup-box p { color: #6b7280; margin-bottom: 18px; }
        .popup-btn { background: #1b2a5e; color: #fff; border: none; border-radius: 6px; padding: 10px 22px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="nbcontainer">
            <div class="logo">
                <img src="../assets/logo%20lungo7.png" alt="logo Unibank">
            </div>
            <div class="menu">
                <ul>
                    <li>
                        <a href="funzioniUtenti/cercaDispense.php" class="listelement">Sfoglia</a>
                    </li>
                    <li>
                        <a href="contactus/contactus.php" class="listelement">Contattaci</a>
                    </li>
                    <?php
                    if(!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){ ?>
                    <li>
                        <a href="authentication/frontend/login.php">
                            <button class="loginbtn">Login</button>
                        </a>
                    </li>
                    <?php } ?>
                    <?php
                    if(!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){?>
                    <li>
                        <a href="authentication/frontend/signup.php">
                            <button class="signupbtn">Registrati</button>
                        </a>
                    </li>
                    <?php } ?>
                    <?php
                    if(isset($_SESSION['is_logged']) && $_SESSION['is_logged'] == true){ ?>
                    <li>
                        <div class="profileicon">
                            <img src="../assets/user.png" alt="user">
                        </div>
                        <div class="userpopup">
                            <div class="uppfavatar">
                                <?php
                                $initials = '';
                                if(isset($_SESSION['username'])){
                                    $name = trim($_SESSION['username']);
                                    $initials = strtoupper(substr($name,0,1));
                                }else{ $initials = 'U'; }
                                ?>
                                <span><?php echo $initials; ?></span>
                            </div>
                            <span>Ciao, <?php echo $_SESSION['username'] ?></span>
                            <span>Saldo: 
                            <?php 
                                $query = "SELECT saldo FROM utenti WHERE id_utente = {$_SESSION['user_id']}";
                                $ris = mysqli_query($conn, $query);
                                if($ris){
                                    $row = mysqli_fetch_assoc($ris);
                                    echo htmlspecialchars($row['saldo']);
                                }else{
                                    echo '0';
                                }
                            ?>
                            <img src="../assets/unitoken.png" alt="UT"></span>
                            <a href="profile/profile.php" class="mioprofile"><button class="visprofilebtn">Visualizza profilo</button></a>
                            <a href="authentication/backend/logout.php"><button class="logoutbtn">Logout</button></a>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="hpheader">
            <div class="hpheadercontainer">
                <div class="textbox">
                    <h1>Il <span>mercato</span> del sapere universitario</h1>
                    <p>Condividi le tue dispense e guadagna UniToken. Acquista materiali di qualità dai tuoi colleghi universitari.</p>
                    <div class="btnbox">
                        <?php if($_SESSION['is_logged'] == true) {?>
                            <a href="upload/uploadmaterial.php">
                                <button class="startbtn">Carica dispense</button>
                            </a>
                        <?php }
                        else { ?>
                            <a href="authentication/frontend/signup.php">
                                <button class="startbtn">Inizia Gratis</button>
                            </a>
                        <?php } ?>

                        <a href="funzioniUtenti/cercaDispense.php"><button class="sfogliabtn">Sfoglia dispense</button></a>
                    </div>
                </div>
                <div class="headerdispense">
                    <?php
                    while($disp = mysqli_fetch_assoc($resultHeader)){
                            echo '<div class="hddispensabox">';
                            echo '<span class="hdnomedispensa">' . htmlspecialchars($disp['titolo']) . '</span>';
                            echo '<span class="hdprezzodispensa">' . $disp['prezzo'] . ' <img class="ut" src="../assets/unitoken.png" alt="UT"></span>';
                            echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="hero">
            <div class="herocontainer">
                <div class="dispenserecenti">
                    <h2>Dispense Recenti</h2>
                    <a href="funzioniUtenti/cercaDispense.php" class="veditutte">Vedi tutte →</a>
                </div>
                <div class="herodispense">
                    <?php
                    $count = 0;
                    while($disp = mysqli_fetch_assoc($resultHero)){
                            $count++;
                            echo '<div class="hrdispensabox">';
                            echo '<div class="dbtextbox">';
                            echo '<img class="dbdocument" src="../assets/document.png" alt="">';
                            echo '<h4 class="dbnomedispensa">' . htmlspecialchars($disp['titolo']) . '</h4>';
                            echo '<p class="dbcorso">' . htmlspecialchars($disp['materia']) . '</p>';
                            echo '<p class="dbuniversita">' . htmlspecialchars($disp['universita']) . '</p>';
                            echo '<p class="dbfacolta">' . htmlspecialchars($disp['facolta']) . '</p>';
                            echo '<p class="dbuser">di ' . htmlspecialchars($disp['username']) . '</p>';
                            echo '</div>';
                            echo '<div class="dbbuyfield">';
                            echo '<span class="dbprezzodispensa">' . $disp['prezzo'] . ' <img class="ut" src="../assets/unitoken.png" alt="UT"></span>';
                            echo '<form action="./acquistaDispense/elaborazioneAcquisto.php" method="POST">';
                            echo '<input type="hidden" name="id_dispensa" value="' . $disp['id_dispensa'] . '">';
                            echo '<input type="hidden" name="from" value="../index.php">';
                            echo $disp['numLikes'];
                            $activeClass = '';
                            if($disp['hasLiked'] > 0  && $_SESSION['is_logged'] == true && !empty($_SESSION['is_logged'])){
                                $activeClass = 'active';
                            }
                            echo '<a href="funzioniUtenti/aggiuntaLikeDispensa.php?id_dispensa='.$disp['id_dispensa'].'"><button type="button" class="likebtn '.$activeClass.'">'; 
                            echo '<img class="likeborder" src="../assets/likeborder.png" alt="like">';
                            echo '<img class="like" src="../assets/like.png" alt="like">';
                            echo '</button></a>';
                            if(isset($_SESSION['is_logged']) && $_SESSION['is_logged'] == true){
                                echo '<button type="submit" class="buybtn">Compra</button>';
                            }else{
                                echo '<a href="authentication/frontend/login.php"><button type="button" class="buybtn">Accedi per comprare</button></a>';
                            }
                            echo '</form>';
                            echo '</div>';
                            echo '</div>';
                    }
                    if($count == 0){
                        echo '<p>Nessuna dispensa disponibile al momento.</p>';
                    }
                    ?>
                </div>
            </div>
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
                        <a href="../../index.html">Home</a>
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
                <a href="contactus/contactus.php"><button class="contactbtn">Contattaci</button></a>
            </div>
        </div>
        <p class="copyright">© 2026 UniBank™. All rights reserved.</p>
    </footer>

    <div class="popup-overlay" id="popupBuyError">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p id="popupBuyErrorText">Si è verificato un errore durante l'acquisto.</p>
            <button class="popup-btn" onclick="closePopup('popupBuyError')">Chiudi</button>
        </div>
    </div>
</body>


<script>
function openPopup(id){ document.getElementById(id)?.classList.add('active'); }
function closePopup(id){ document.getElementById(id)?.classList.remove('active'); }
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.nbcontainer');
    function ombraNavbar() {
        if (window.scrollY === 0) {
            navbar.classList.add('no-shadow');
        } else {
            navbar.classList.remove('no-shadow');
        }
    }
    ombraNavbar();
    window.addEventListener('scroll', ombraNavbar);

    const likeBtns = document.querySelectorAll('.likebtn');
    likeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });

    const buyError = "<?php echo htmlspecialchars($_GET['buy_error'] ?? ''); ?>";
    if(buyError){
        const msg = {
            own_dispensa: "Non puoi acquistare una dispensa caricata da te.",
            already_bought: "Hai già comprato questa dispensa.",
            insufficient_balance: "Saldo UniToken insufficiente per completare l'acquisto.",
            not_found: "Dispensa non trovata o non disponibile.",
            generic: "Errore durante l'acquisto. Riprova più tardi."
        };
        document.getElementById('popupBuyErrorText').textContent = msg[buyError] || msg.generic;
        openPopup('popupBuyError');
    }
});
</script>
</html>