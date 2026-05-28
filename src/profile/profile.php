<?php
session_start();
require_once __DIR__ . '/../../config.php';
$conn = db_connect();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>UniBank - Profilo</title>
    <link rel="stylesheet" href="profile.css?=<?php echo time();?>">
    <link rel="stylesheet" href="../variables.css?=<?php echo time();?>">
</head>
<body>
<header class="navbar">
    <div class="nbcontainer">
        <div class="logo">
            <img src="../../assets/logo%20lungo7.png" alt="logo Unibank">
        </div>
        <div class="menu">
            <ul>
                <li>
                    <a href="../funzioniUtenti/cercaDispense.php" class="listelement">Sfoglia</a>
                </li>
                <li>
                    <a href="../contactus/contactus.php" class="listelement">Contattaci</a>
                </li>
                <li>
                    <a href="../index.php" class="listelement">Home</a>
                </li>
                <?php if(!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){ ?>
                <li>
                    <a href="../authentication/frontend/login.php">
                        <button class="loginbtn">Login</button>
                    </a>
                </li>
                <li>
                    <a href="../authentication/frontend/signup.php">
                        <button class="signupbtn">Registrati</button>
                    </a>
                </li>
                <?php } else { ?>
                <li>
                    <div class="profileicon">
                        <img src="../../assets/user.png" alt="user">
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
                        <span>Ciao,&nbsp;<span class="urnamep"> <?php echo $_SESSION['username']?></span></span>
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
                            <img src="../../assets/unitoken.png" alt="UT"></span>
                        <a href="../authentication/backend/logout.php"><button class="logoutbtn">Logout →</button></a>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</header>

<main class="profilepage">
    <div class="pfcontainer">
        <?php if(isset($_SESSION['upload_success'])): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px;">
                    <?php
                        echo $_SESSION['upload_success']; 
                        unset($_SESSION['upload_success']);
                    ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION['upload_error'])): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">
                    <?php 
                        echo $_SESSION['upload_error']; 
                        unset($_SESSION['upload_error']);
                    ?>
                </div>
            <?php endif; ?>
        <section class="pfheadercard">
            <div class="pfuser">
                <div class="pfavatar">
                    <?php
                    $initials = '';
                    if(isset($_SESSION['username'])){
                        $name = trim($_SESSION['username']);
                        $initials = strtoupper(substr($name,0,1));
                    }else{ $initials = 'U'; }
                    ?>
                    <span><?php echo $initials; ?></span>
                </div>
                <div class="pfmeta">
                    <h3 class="pfname"><?php echo htmlspecialchars($_SESSION['username'] ?? 'user'); ?></h3>
                    <?php
                        echo '<p class="pfuniv">'.'•'.$_SESSION['universita'] .'<br>'.'•'.  $_SESSION['facolta'].'</p>'
                    ?>
                    <span class="pfbadge"><img src="../../assets/unitoken.png" alt="UT">
                    <?php 
                                $query = "SELECT saldo FROM utenti WHERE id_utente = {$_SESSION['user_id']}";
                                $ris = mysqli_query($conn, $query);
                                if($ris){
                                    $row = mysqli_fetch_assoc($ris);
                                    echo htmlspecialchars($row['saldo']);
                                }else{
                                    echo '0';
                                }
                    ?> UniToken</span>
                </div>
            </div>
            <div class="pfactions">
                <a href="../funzioniUtenti/modificaProfiloUtente.php"><button class="editbtn">✎ Modifica profilo</button></a>
            </div>
        </section>
        
        <section class="pfstats">
            <div class="statcard">
                <span class="statlabel">Dispense caricate</span>
                <?php
                    $query = "SELECT COUNT(*) AS totaleCaricate FROM dispense WHERE id_utente = {$_SESSION['user_id']} AND approvata = 1";
                    $ris = mysqli_query($conn, $query);
                    $dispenseCaricate = mysqli_fetch_assoc($ris);
                    echo '<span class="statvalue">' . htmlspecialchars($dispenseCaricate['totaleCaricate']) . '</span>';
                ?>
            </div>
            <div class="statcard">
                <span class="statlabel">Dispense acquistate</span>
                <?php
                    $query = "SELECT COUNT(*) AS totaleAcquistate FROM acquisti WHERE id_utente = {$_SESSION['user_id']}";
                    $ris = mysqli_query($conn, $query);
                    $dispenseAcquistate = mysqli_fetch_assoc($ris);
                    echo '<span class="statvalue">' . htmlspecialchars($dispenseAcquistate['totaleAcquistate']) . '</span>';
                ?>
            </div>
            <div class="statcard">
                <span class="statlabel">Token guadagnati</span>
                <?php
                    $query = "
                            SELECT prezzo 
                            FROM dispense d,acquisti a
                            WHERE d.id_dispensa = a.id_dispensa
                            AND d.id_utente = {$_SESSION['user_id']}
                            ";
                    $ris = mysqli_query($conn,$query);
                    $guadagno = 0;
                    while($riga = mysqli_fetch_assoc($ris)){
                        $guadagno+=$riga['prezzo'];
                    }
                    echo '<span class="statvalue green">'.$guadagno.'</span>';
                ?>
            </div>
            <div class="statcard">
                <span class="statlabel">Token spesi</span>
                <?php
                    $query = "
                            SELECT prezzo 
                            FROM dispense d,acquisti a
                            WHERE d.id_dispensa = a.id_dispensa
                            AND a.id_utente = {$_SESSION['user_id']}
                            ";
                    $ris = mysqli_query($conn,$query);
                    $speso = 0;
                    while($riga = mysqli_fetch_assoc($ris)){
                        $speso+=$riga['prezzo'];
                    }
                    echo '<span class="statvalue yellow">'.$speso.'</span>';
                ?>
            </div>
        </section>

        <section class="pfupload">
            <div style="display: flex; flex-direction: column; gap: 5px">
                <h3>Carica una nuova dispensa</h3>
                <p class="infop">Carica e rendi pubblico i tuoi materiali per guadagnare UniToken</p>
            </div>
            <a style="text-decoration: none" href="../upload/uploadmaterial.php"><button class="pfuploadbtn"><img src="../../assets/upload.png" alt="+">Carica ora</button></a>
        </section>

        <section class="pfsections">
            <div class="pfcolumn">
                <h4>Dispense caricate da me</h4>
                <div class="pfbox">
                    <?php
                        $query = "
                                SELECT *
                                FROM dispense d, materiaperfacolta m, utenti u
                                WHERE d.id_utente = u.id_utente
                                AND d.id_materiaperfacolta = m.id_materiaperfacolta
                                AND d.id_utente = {$_SESSION['user_id']}
                                AND d.approvata = 1
                        ";
                        $ris = mysqli_query($conn,$query);
                        if(mysqli_num_rows($ris) == 0){
                            echo '<p>Nessuna dispensa caricata</p>';
                        }else{
                            while($riga = mysqli_fetch_assoc($ris)){
                                echo '<div class="pfboxrow">';
                                        echo '<div>';
                                            echo '<h5>'.$riga['titolo'].'</h5>';
                                            echo '<p>' .'Caricata in data '.substr($riga['data_caricamento'], 0, 10) . '</p>';
                                            #echo '<p>'.$riga[''].'</p>';
                                        echo '</div>';
                                    echo '<span class="pricebadge">'.$riga['prezzo'].'<img src="../../assets/unitoken.png" alt="UT">' .'</span>';
                                echo '</div>';
                            }
                        }
                        
                    ?>
                </div>
            </div>
            <div class="pfcolumn">
                <h4>Dispense acquistate</h4>
                <div class="pfbox">
                            <?php
                                if(isset($_SESSION['user_id'])){
                                    $query = "SELECT d.id_dispensa, d.titolo, d.prezzo, a.data_acquisto, u.username
                                              FROM acquisti a, dispense d, utenti u
                                              WHERE a.id_utente = {$_SESSION['user_id']}
                                              AND a.id_dispensa = d.id_dispensa
                                              AND d.id_utente = u.id_utente
                                              AND u.bloccato = 0
                                              ORDER BY a.data_acquisto DESC";
                                              
                                    $ris = mysqli_query($conn, $query);
                                    if(!$ris){
                                        echo '<p>Errore caricamento dispense: ' . htmlspecialchars(mysqli_error($conn)) . '</p>';
                                    }else{
                                        if(mysqli_num_rows($ris) == 0){
                                            echo '<p>Nessuna dispensa acquistata</p>';
                                        }else{
                                            while($riga = mysqli_fetch_assoc($ris)){
                                                echo '<div class="pfboxrow">';
                                                echo '<div>';
                                                echo '<h5>' . htmlspecialchars($riga['titolo']) . '</h5>';
                                                echo '<p>di ' . htmlspecialchars($riga['username']) .' | acquistata in data ' . substr($riga['data_acquisto'], 0, 10) .'</p>';
                                                echo '<form method="POST" action="../downloadDispense/downloadDispensa.php" style="display:inline;">';
                                                echo '</div>';
                                                echo '<input type="hidden" name="id_dispensa" value="' . $riga['id_dispensa'] . '">';
                                                echo '<div class="downloadbox">';
                                                echo '<span class="pricebadge2">'.'-'.$riga['prezzo'].'<img src="../../assets/unitoken.png" alt="UT">' .'</span>';
                                                echo '<button type="submit" class="downloadbtn"><img src="../../assets/download.png" alt="Download"></button>';
                                                echo '</div>';
                                                echo '</div>';
                                                
                                                echo '</form>';
                                            }
                                        }
                                    }
                                }else{
                                    echo '<p>Accedi per visualizzare le tue dispense</p>';
                                }
                            ?>
                </div>
            </div>
        </section>
        <section class="pfsections">
        <div class="pfcolumn">
            <h4>Dispense a cui ho messo like</h4>
            <div class="pfbox">
                <?php
                    if(isset($_SESSION['user_id'])){
                        $query = "
                                SELECT d.id_dispensa, d.id_utente AS autoreId, d.titolo, d.prezzo, l.data_like, u.username
                                FROM likes l, dispense d, utenti u
                                WHERE l.id_dispensa = d.id_dispensa
                                AND d.id_utente = u.id_utente
                                AND l.id_utente = {$_SESSION['user_id']}
                                ORDER BY l.data_like DESC
                        ";
                        $ris = mysqli_query($conn, $query);

                        if(!$ris){
                            echo '<p>Errore caricamento like: ' . htmlspecialchars(mysqli_error($conn)) . '</p>';
                        }else{
                            if(mysqli_num_rows($ris) == 0){
                                echo '<p>Non hai ancora messo like a nessuna dispensa</p>';
                            }else{
                                while($riga = mysqli_fetch_assoc($ris)){
                                    echo '<div class="pfboxrow">';
                                    echo '<div>';
                                    echo '<h5>' . htmlspecialchars($riga['titolo']) . '</h5>';
                                    echo '<p>di ' . htmlspecialchars($riga['username']) . ' | Like del ' . htmlspecialchars(substr($riga['data_like'], 0, 10)) . '</p>';
                                    echo '</div>';
                                    echo '<div class="buyboxprofile">';
                                    echo '<span class="pricebadge">' . htmlspecialchars($riga['prezzo']) . '<img src="../../assets/unitoken.png" alt="UT"></span>';
                                    if(intval($riga['autoreId']) === intval($_SESSION['user_id'])){
                                        echo '<button type="button" class="buyprofilebtn" onclick="openPopup(\'popupOwnDispensa\')">Compra</button>';
                                    }else{
                                        echo '<form method="POST" action="../acquistaDispense/elaborazioneAcquisto.php">';
                                        echo '<input type="hidden" name="id_dispensa" value="' . htmlspecialchars($riga['id_dispensa']) . '">';
                                        echo '<input type="hidden" name="from" value="../profile/profile.php">';
                                        echo '<button type="submit" class="buyprofilebtn">Compra</button>';
                                        echo '</form>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                        }
                    }else{
                        echo '<p>Accedi per visualizzare questa sezione</p>';
                    }
                ?>
            </div>
        </div>

        <div class="pfcolumn">
                <h4>Chi ha acquistato le tue dispense</h4>
                <div class="pfbox">
                            <?php
                                if(isset($_SESSION['user_id'])){
                                    $query = "
                                            SELECT u.username, uni.nome AS nomeUni, f.nome AS nomeFacolta, a.data_acquisto, d.titolo, d.prezzo AS prezzoDispensa
                                            FROM acquisti a, dispense d, utenti u, universita uni, facolta f
                                            WHERE a.id_dispensa = d.id_dispensa
                                            AND a.id_utente = u.id_utente
                                            AND u.id_universita = uni.id_universita
                                            AND u.id_facolta = f.id_facolta
                                            AND u.bloccato = 0
                                            AND d.id_utente = {$_SESSION['user_id']}
                                            ORDER BY a.data_acquisto DESC
                                    ";
                                    $ris = mysqli_query($conn, $query);
                                    if(!$ris){
                                        echo '<p>errore: ' . mysqli_error($conn) . '</p>';
                                    }else{
                                        if(mysqli_num_rows($ris) == 0){
                                            echo '<p>Nessuno ha ancora comprato le tue dispense</p>';
                                        }else{
                                            while($riga = mysqli_fetch_assoc($ris)){
                                                echo '<div class="pfboxrow">';
                                                echo '<div>';
                                                echo '<h5>' . $riga['titolo'] . '</h5>';
                                                echo '<p>' . $riga['username'] . ' da ' . htmlspecialchars($riga['nomeUni']) . ' • ' . htmlspecialchars($riga['nomeFacolta']) . ' | Acquistata in data ' . htmlspecialchars(substr($riga['data_acquisto'], 0, 10)) . '</p>';
                                                echo '</div>';
                                                echo '<span class="pricebadge1">'.'+'.$riga['prezzoDispensa'].'<img src="../../assets/unitoken.png" alt="UT">' .'</span>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                }else{
                                    echo '<p>Accedi per visualizzare questa sezione</p>';
                                }
                            ?>
                </div>
            </div>
        </section>

        <div class="logoutsection">
            <a href="../authentication/backend/logout.php"><button class="exitbtn">→ Esci dall'account</button></a>
        </div>
    </div>
</main>

<div class="popup-overlay" id="popupOwnDispensa">
    <div class="popup-box">
        <h3>Attenzione</h3>
        <p>Non puoi acquistare una dispensa caricata da te.</p>
        <button class="popup-btn" onclick="closePopup('popupOwnDispensa')">Chiudi</button>
    </div>
</div>

<div class="popup-overlay" id="popupBuyError">
    <div class="popup-box">
        <h3>Attenzione</h3>
        <p id="popupBuyErrorText">Si è verificato un errore durante l'acquisto.</p>
        <button class="popup-btn" onclick="closePopup('popupBuyError')">Chiudi</button>
    </div>
</div>

<script>
    function openPopup(id){
        document.getElementById(id)?.classList.add('active');
    }

    function closePopup(id){
        document.getElementById(id)?.classList.remove('active');
    }

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
</body>
</html>
