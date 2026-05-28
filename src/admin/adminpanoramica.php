<?php
session_start();
require_once __DIR__ . '/../../config.php';
$conn = db_connect();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>UniBank - Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css?=<?php echo time();?>">
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
                        <span>Ciao, <?php echo $_SESSION['username'] ?></span>
                        <a href="../authentication/backend/logout.php"><button class="logoutbtn">Logout</button></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<main class="admin-page">
    <nav class="admin-tabs">
        <div class="tabs-container">
            <a href="adminpanoramica.php" class="tab-item active">Panoramica</a>
            <a href="adminusers.php" class="tab-item">Gestione utenti</a>
            <a href="adminmateriali.php" class="tab-item">Gestione materiali</a>
            <a href="adminstoricoacquisti.php" class="tab-item">Storico acquisti</a>
            <a href="adminmessaggi.php" class="tab-item">Messaggi di contatto</a>
        </div>
    </nav>

    <div class="admin-container">
        <section class="admin-stats">
            <div class="stat-card">
                <?php
                    $query = "SELECT COUNT(*) AS numUtenti FROM utenti";
                    $ris = mysqli_query($conn,$query);
                    $riga = mysqli_fetch_assoc($ris);
                    $query2 = "SELECT COUNT(*) AS numAdmin FROM utenti WHERE ruolo = 1";#utenti ruolo = 0 admin = 1, 
                    #conto gli admin cosi poi faccio totale utenti - num ADMIN cosi da avere un numero preciso di utenti che non siano admin
                    $ris2 = mysqli_query($conn,$query2);
                    $riga2 = mysqli_fetch_assoc($ris2);
                    $numUtentiNoAdmin = $riga['numUtenti'] - $riga2['numAdmin'];
                    echo '<span class="stat-label" id="utenti-label">Numero utenti</span>';
                    echo '<span class="stat-value blue" id="utenti-value"
                              data-utenti="'.$numUtentiNoAdmin.'"
                              data-admin="'.$riga2['numAdmin'].'"
                              data-totale="'.$riga['numUtenti'].'">'.$numUtentiNoAdmin.'</span>';
                ?>
                <div class="stat-pills" id="utenti-pills">
                    <button class="stat-pill active" data-target="utenti" data-label="Numero utenti">Utenti</button>
                    <button class="stat-pill"        data-target="admin"  data-label="Numero admin">Admin</button>
                    <button class="stat-pill"        data-target="totale" data-label="Numero totale utenti + admin">Totale</button>
                </div>
            </div>
            <div class="stat-card">
                <?php
                    $query = "
                            SELECT COUNT(*) AS numDispenseAttive
                            FROM utenti u, dispense d
                            WHERE u.bloccato = 0
                            AND d.bloccata = 0
                            AND d.approvata = 1
                            AND d.id_utente = u.id_utente
                    ";
                    $ris = mysqli_query($conn,$query);
                    $riga = mysqli_fetch_assoc($ris);

                    
                    #query per vedere quante dispense sono state disattivate a causa dei blocchi degli utenti 
                    $query2 = "
                            SELECT COUNT(*) AS numBloccate
                            FROM utenti u, dispense d
                            WHERE (u.bloccato = 1 OR d.bloccata = 1)
                            AND d.approvata = 1
                            AND d.id_utente = u.id_utente
                    ";
                    $ris2 = mysqli_query($conn,$query2);
                    $riga2 = mysqli_fetch_assoc($ris2);
                    $query3 = "
                            SELECT COUNT(*) AS inAttesa
                            FROM utenti u, dispense d
                            WHERE u.bloccato = 0
                            AND d.approvata = 0
                            AND d.id_utente = u.id_utente
                    ";
                    $ris3 = mysqli_query($conn,$query3);
                    $riga3 = mysqli_fetch_assoc($ris3);
                    $dispTotaliAttive   = $riga['numDispenseAttive']  ?? 0;
                    $dispBloccate = $riga2['numBloccate'] ?? 0;
                    $dispInAttesa = $riga3['inAttesa'] ?? 0;
                    echo '<span class="stat-label" id="dispense-label">Dispense approvate attive</span>';
                    echo '<span class="stat-value blue" id="dispense-value"'
                        .' data-totali="'.$dispTotaliAttive.'"'
                        .' data-bloccate="'.$dispBloccate.'"'
                        .' data-inattesa="'.$dispInAttesa.'">'.$dispTotaliAttive.'</span>';
                ?>
                <div class="stat-pills" id="dispense-pills">
                    <button class="stat-pill active" data-target="totali"   data-label="Dispense approvate attive">Attive</button>
                    <button class="stat-pill"        data-target="bloccate" data-label="Dispense bloccate">Bloccate</button>
                    <button class="stat-pill"        data-target="inattesa" data-label="Dispense in attesa di approvazione">In attesa</button>
                </div>
            </div>
            <div class="stat-card">
                <?php
                    $query = "
                            SELECT SUM(saldo) AS totale
                            FROM utenti
                            WHERE ruolo = 0
                            AND bloccato = 0
                    ";#utenti ruolo = 0 admin = 1
                    $ris = mysqli_query($conn,$query);
                    $riga = mysqli_fetch_assoc($ris);

                    #utenti bloccati calcolo 
                    $query2 = "
                            SELECT SUM(saldo) AS totale
                            FROM utenti
                            WHERE ruolo = 0
                            AND bloccato = 1
                    ";
                    $ris2 = mysqli_query($conn,$query2);
                    $riga2 = mysqli_fetch_assoc($ris2);
                    $tokAttivi   = $riga['totale']  ?? 0;
                    $tokBloccati = $riga2['totale'] ?? 0;
                    echo '<span class="stat-label" id="token-label">Token posseduti da utenti attivi</span>';
                    echo '<span class="stat-value yellow" id="token-value"'
                        .' data-attivi="'.$tokAttivi.'"'
                        .' data-bloccati="'.$tokBloccati.'">'.$tokAttivi.'</span>';
                ?>
                <div class="stat-pills" id="token-pills">
                    <button class="stat-pill active" data-target="attivi"   data-label="Token posseduti da utenti attivi">Attivi</button>
                    <button class="stat-pill"        data-target="bloccati" data-label="Token posseduti da utenti bloccati">Bloccati</button>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-label">Acquisti totali</span>
                <?php
                    $query = "SELECT COUNT(*) AS numAcquisti FROM acquisti";
                    $ris = mysqli_query($conn,$query);
                    $riga = mysqli_fetch_assoc($ris);
                    echo '<span class="stat-value green">'.$riga['numAcquisti'].'</span>';
                ?>   
            </div>
        </section>

        <section class="admin-content">
            <div class="admin-column">
                <h4>Gestione utenti</h4>
                <div class="admin-box no-padding">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>UTENTE</th>
                                <th>STATO</th>
                                <th>RUOLO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $query = "
                                    SELECT username,email,bloccato,ruolo,uni.nome AS uniNome, f.nome AS nomeFacolta,utenti.id_utente AS id
                                    FROM utenti,universita uni, facolta f
                                    WHERE utenti.id_universita = uni.id_universita
                                    AND utenti.id_facolta = f.id_facolta
                                ";
                                $ris = mysqli_query($conn,$query);
                                while($riga = mysqli_fetch_assoc($ris)){
                                    echo '<tr>';
                                    echo    '<td>';
                                    echo        '<div class="user-info">';
                                    if($riga['id'] == $_SESSION['user_id']){
                                        echo    ' <span class="user-name">'.'•'.$riga['username'].'(tu)'.'</span>';
                                    }else{
                                        echo    ' <span class="user-name">'.'•'.$riga['username'].'</span>';
                                    }
                                    echo        ' <span class="user-email">'.'•'.$riga['email'].'</span>';
                                    echo        ' <span class="user-email">'.'•'.$riga['uniNome'].'</span>';
                                    echo        ' <span class="user-email">'.'•'.$riga['nomeFacolta'].'</span>';
                                    echo        '</div>';
                                    echo    '</td>';
                                    echo    '<td>';
                                    if($riga['bloccato'] == 0){
                                        echo '<span class="status status-active">Attivo</span>';
                                    }else{
                                        echo '<span class="status status-blocked">Bloccato</span>';
                                    }
                                    echo    '</td>';
                                    echo    '<td>';
                                    if($riga['ruolo'] == 0){
                                        echo '<span class="status status-user">Utente</span>';
                                    }else{
                                        echo '<span class="status status-admin">Admin</span>';
                                    }
                                    echo    '</td>';
                                    echo '</tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="admin-column">
                <h4>Materiali approvati recentemente</h4>
                <div class="admin-box">
                    <?php
                        $query = "
                            SELECT * 
                            FROM dispense,utenti
                            WHERE utenti.id_utente = dispense.id_utente
                            AND utenti.bloccato = 0
                            AND dispense.approvata = 1 
                            ORDER BY data_caricamento DESC
                            LIMIT 5
                        ";
                        $ris = mysqli_query($conn,$query);
                        $cont = 0;
                        while($riga = mysqli_fetch_assoc($ris)){
                            $cont++;
                            echo '<div class="material-row">';
                            echo    '<div class="material-info">';
                            echo        '<h5>'.$riga['titolo'].'<h5>';
                            echo        '<p>di '. $riga['username'] .' • '. substr($riga['data_caricamento'],0,10) . '</p>';
                            echo    '</div>';
                            echo     '<button class="delete-btn">';
                            echo        '<a href="../downloadDispense/downloadDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><img src="../../assets/download.png" alt="download"></a>';
                            echo     '</button>';
                            echo '</div>';  
                        }
                        if($cont == 0){
                            echo '<div class="material-info">';
                            echo '<p>Non sono state ancora approvate dispense</p>';
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
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

        const valueEl = document.getElementById('utenti-value');
        const labelEl = document.getElementById('utenti-label');
        const pills   = document.querySelectorAll('#utenti-pills .stat-pill');
        //pill per utenti admin e totale
        pills.forEach(pill =>{
            pill.addEventListener('click', function(){
                pills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const target   = this.dataset.target;  // 'utenti' 'admin' 'totale'
                const newVal   = valueEl.dataset[target];
                const newLabel = this.dataset.label;

                valueEl.style.transition = 'opacity 0.15s';
                labelEl.style.transition = 'opacity 0.15s';
                valueEl.style.opacity    = '0';
                labelEl.style.opacity    = '0';
                setTimeout(() =>{
                    valueEl.textContent  = newVal;
                    labelEl.textContent  = newLabel;
                    valueEl.style.opacity = '1';
                    labelEl.style.opacity = '1';
                }, 150);
            });
        });

        // pill per calcolo token posseduti da utenti attivi e bloccati
        const tokenValueEl = document.getElementById('token-value');
        const tokenLabelEl = document.getElementById('token-label');
        const tokenPills   = document.querySelectorAll('#token-pills .stat-pill');

        tokenPills.forEach(pill => {
            pill.addEventListener('click', function() {
                tokenPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const target   = this.dataset.target;   // 'attivi'e 'bloccati'
                const newVal   = tokenValueEl.dataset[target];
                const newLabel = this.dataset.label;

                tokenValueEl.style.transition = 'opacity 0.15s';
                tokenLabelEl.style.transition = 'opacity 0.15s';
                tokenValueEl.style.opacity    = '0';
                tokenLabelEl.style.opacity    = '0';
                setTimeout(() => {
                    tokenValueEl.textContent  = newVal;
                    tokenLabelEl.textContent  = newLabel;
                    tokenValueEl.style.opacity = '1';
                    tokenLabelEl.style.opacity = '1';
                }, 150);
            });
        });

        // pill per dispense totali e da utenti bloccati
        const dispenseValueEl = document.getElementById('dispense-value');
        const dispenseLabelEl = document.getElementById('dispense-label');
        const dispensePills   = document.querySelectorAll('#dispense-pills .stat-pill');

        dispensePills.forEach(pill => {
            pill.addEventListener('click', function() {
                dispensePills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const target   = this.dataset.target;   // 'totali' e 'bloccate'
                const newVal   = dispenseValueEl.dataset[target];
                const newLabel = this.dataset.label;

                dispenseValueEl.style.transition = 'opacity 0.15s';
                dispenseLabelEl.style.transition = 'opacity 0.15s';
                dispenseValueEl.style.opacity    = '0';
                dispenseLabelEl.style.opacity    = '0';
                setTimeout(() => {
                    dispenseValueEl.textContent  = newVal;
                    dispenseLabelEl.textContent  = newLabel;
                    dispenseValueEl.style.opacity = '1';
                    dispenseLabelEl.style.opacity = '1';
                }, 150);
            });
        });
    });
</script>
</body>
</html>