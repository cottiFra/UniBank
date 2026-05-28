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
    <title>UniBank - Gestione Utenti</title>
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
            <a href="adminpanoramica.php" class="tab-item">Panoramica</a>
            <a href="adminusers.php" class="tab-item active">Gestione utenti</a>
            <a href="adminmateriali.php" class="tab-item">Gestione materiali</a>
            <a href="adminstoricoacquisti.php" class="tab-item">Storico acquisti</a>
            <a href="adminmessaggi.php" class="tab-item">Messaggi di contatto</a>
        </div>
    </nav>

    <div class="admin-container">
        <section class="admin-header-actions">
            <h4>Gestione Utenti</h4>
            <form method="GET" action="adminusers.php" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; margin: 0;">
                <div class="search-bar" style="margin: 0;">
                    <?php 
                    $search_val = "";
                    if(isset($_GET['search'])){
                        $search_val = $_GET['search'];
                    }
                    ?>
                    <input type="text" name="search" placeholder="Cerca utente per username o email" value="<?php echo $search_val; ?>">
                    <button type="submit" class="search-btn">Cerca</button>
                </div>
                <div class="sort-bar" style="display: flex; gap: 10px; align-items: center;">
                    <?php
                    $current_sort = 'data_desc';
                    if(isset($_GET['sort'])){
                        $current_sort = $_GET['sort'];
                    }
                    ?>
                    <select name="sort" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 14px; background-color: #f9fafb; outline: none; color: #4b5563; cursor: pointer;">
                        <option value="data_desc" <?php if($current_sort == 'data_desc') echo 'selected'; ?>>Data iscrizione (Più recenti)</option>
                        <option value="data_asc" <?php if($current_sort == 'data_asc') echo 'selected'; ?>>Data iscrizione (Meno recenti)</option>
                        <option value="admin_first" <?php if($current_sort == 'admin_first') echo 'selected'; ?>>Ruolo: Prima Admin</option>
                        <option value="user_first" <?php if($current_sort == 'user_first') echo 'selected'; ?>>Ruolo: Prima Utenti</option>
                        <option value="saldo_asc" <?php if($current_sort == 'saldo_asc') echo 'selected'; ?>>Saldo: Crescente</option>
                        <option value="saldo_desc" <?php if($current_sort == 'saldo_desc') echo 'selected'; ?>>Saldo: Decrescente</option>
                        <option value="bloccati_first" <?php if($current_sort == 'bloccati_first') echo 'selected'; ?>>Stato: Prima Bloccati</option>
                        <option value="sbloccati_first" <?php if($current_sort == 'sbloccati_first') echo 'selected'; ?>>Stato: Prima Sbloccati</option>
                    </select>
                    <button type="submit" class="search-btn">Applica Filtri</button>
                </div>
            </form>
        </section>

        <section class="admin-content-full">
            <div class="admin-box no-padding">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>UTENTE</th>
                            <th>RUOLO</th>
                            <th>DATA ISCRIZIONE</th>
                            <th>TOKEN</th>
                            <th>STATO</th>
                            <th>AZIONI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT * FROM utenti";
                            
                            if(isset($_GET['search']) && $_GET['search'] != ''){
                                $search = mysqli_real_escape_string($conn, trim($_GET['search']));
                                $query .= " WHERE (username LIKE '%$search%' OR email LIKE '%$search%')";
                            }

                            if(isset($_GET['sort'])){
                                $sort = $_GET['sort'];
                                if($sort == 'data_asc'){
                                    $query .= " ORDER BY data_iscrizione ASC";
                                }else if($sort == 'admin_first'){
                                    $query .= " ORDER BY ruolo DESC"; // 1 = admin, 0 = utente
                                }else if($sort == 'user_first'){
                                    $query .= " ORDER BY ruolo ASC";
                                }else if($sort == 'saldo_asc'){
                                    $query .= " ORDER BY saldo ASC";
                                }else if($sort == 'saldo_desc'){
                                    $query .= " ORDER BY saldo DESC";
                                }else if($sort == 'bloccati_first'){
                                    $query .= " ORDER BY bloccato DESC"; // 1 = bloccato, 0 = attivo
                                }else if($sort == 'sbloccati_first'){
                                    $query .= " ORDER BY bloccato ASC";
                                }else{
                                    $query .= " ORDER BY data_iscrizione DESC";
                                }
                            }else{
                                $query .= " ORDER BY data_iscrizione DESC";
                            }
                            $ris = mysqli_query($conn, $query);
                            while($riga = mysqli_fetch_assoc($ris)){
                                echo '<tr>';
                                echo    '<td>';
                                echo        '<div class="user-info">';
                                if($riga['id_utente'] == $_SESSION['user_id']){
                                        echo    ' <span class="user-name">'.$riga['username'].'(tu)'.'</span>';
                                    }else{
                                        echo    ' <span class="user-name">'.$riga['username'].'</span>';
                                    }
                                echo            '<span class="user-email">'.$riga['email'].'</span>';
                                echo        '</div>';
                                echo    '</td>';
                                echo    '<td>';
                                if($riga['ruolo'] == 0){
                                    echo '<span class="status status-user">Utente</span>';
                                }else{
                                    echo '<span class="status status-admin">Admin</span>';
                                }
                                echo    '</td>';
                                echo    '<td>' . substr($riga['data_iscrizione'], 0, 10) . '</td>';
                                echo    '<td><span class="token-count">' . ($riga['saldo'] ?? 0) . '</span></td>';
                                echo    '<td>';
                                if($riga['bloccato'] == 0){
                                    echo '<span class="status status-active">Attivo</span>';
                                }else{
                                    echo '<span class="status status-blocked">Bloccato</span>';
                                }
                                echo    '</td>';
                                echo    '<td>';
                                echo        '<div class="action-buttons">';
                                echo            '<a href="funzioniAdmin/modificaUtente.php?id_utente='.$riga['id_utente'].'"><button class="edit-btn">Modifica</button></a>';
                                if($riga['bloccato'] == 0){
                                    if($riga['id_utente'] == $_SESSION['user_id']){
                                        echo        '<button class="block-btn" onclick="openPopupBlocca()">Blocca</button>';
                                    }else{
                                        echo        '<button class="block-btn" onclick="openPopupBloccaUtente(\'funzioniAdmin/bloccaSbloccaUtente.php?id_utente='.$riga['id_utente'].'\')">Blocca</button>';
                                    }
                                }else{
                                    echo        '<a href="funzioniAdmin/bloccaSbloccaUtente.php?id_utente='.$riga['id_utente'].'"><button class="unblock-btn">Sblocca</button></a>';
                                }
                                if($riga['ruolo'] == 0){
                                    echo        '<a href="funzioniAdmin/promuoviRetrocedi.php?id_utente='.$riga['id_utente'].'"><button class="approve-btn">Promuovi</button></a>';
                                }else{
                                    echo        '<a href="funzioniAdmin/promuoviRetrocedi.php?id_utente='.$riga['id_utente'].'"><button class="view-btn">Retrocedi</button></a>';
                                }
                                echo            '<button class="delete-btn-table" onclick="openPopupEliminaUtente(\'funzioniAdmin/eliminaUtente.php?id_utente='.$riga['id_utente'].'\')">Elimina</button>';
                                echo        '</div>';
                                echo    '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div class="popup-overlay" id="popupBlocca">
    <div class="popup-box">
        <h3>Operazione non consentita</h3>
        <p>Non puoi bloccare te stesso.</p>
        <button class="popup-btn" onclick="closePopupBlocca()">Chiudi</button>
    </div>
</div>

<script>
    function openPopupBlocca() {
        document.getElementById('popupBlocca').classList.add('active');
    }

    function closePopupBlocca() {
        document.getElementById('popupBlocca').classList.remove('active');
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
    });
</script>

<!-- Popup conferma eliminazione utente -->
<div class="popup-overlay" id="popupEliminaUtente">
    <div class="popup-box">
        <h3>Conferma eliminazione</h3>
        <p>ATTENZIONE: Sei sicuro di voler eliminare DEFINITIVAMENTE questo utente? Tutti i suoi dati, inclusi i record degli acquisti e le dispense caricate, verranno rimossi permanentemente dal sistema. Questa operazione NON può essere annullata.</p>
        <div style="display:flex; gap:10px; justify-content:center; align-items:center; margin-top:10px;">
            <button class="popup-btn" onclick="closePopupEliminaUtente()">Annulla</button>
            <button class="popup-btn" onclick="confirmEliminaUtente()">Conferma</button>
        </div>
    </div>
</div>

<!-- Popup conferma blocco utente -->
<div class="popup-overlay" id="popupBloccaUtente">
    <div class="popup-box">
        <h3>Conferma blocco</h3>
        <p>Sei sicuro di voler bloccare questo utente? L'operazione gli impedirà l'accesso e i suoi materiali non saranno più visibili agli altri. Potrai comunque sbloccarlo in futuro.</p>
        <div style="display:flex; gap:10px; justify-content:center; align-items:center; margin-top:10px;">
            <button class="popup-btn" onclick="closePopupBloccaUtente()">Annulla</button>
            <button class="popup-btn" onclick="confirmBloccaUtente()">Conferma</button>
        </div>
    </div>
</div>

<!-- Popup prevenzione auto-promozione/retrocessione -->
<div class="popup-overlay" id="popupSelfRole">
    <div class="popup-box">
        <h3>Operazione non consentita</h3>
        <p>Non puoi modificare il tuo ruolo.</p>
        <button class="popup-btn" onclick="closePopupSelfRole()">Chiudi</button>
    </div>
</div>

<!-- Popup prevenzione auto-eliminazione -->
<div class="popup-overlay" id="popupSelfDelete">
    <div class="popup-box">
        <h3>Operazione non consentita</h3>
        <p>Non puoi eliminare te stesso.</p>
        <button class="popup-btn" onclick="closePopupSelfDelete()">Chiudi</button>
    </div>
</div>

<script>
    let eliminaUtenteUrl = '';
    function openPopupEliminaUtente(url){
        eliminaUtenteUrl = url;
        document.getElementById('popupEliminaUtente').classList.add('active');
    }
    function closePopupEliminaUtente(){
        document.getElementById('popupEliminaUtente').classList.remove('active');
        eliminaUtenteUrl = '';
    }
    function confirmEliminaUtente(){
        if(eliminaUtenteUrl){
            window.location.href = eliminaUtenteUrl;
        }
    }

    let bloccaUtenteUrl = '';
    function openPopupBloccaUtente(url){
        bloccaUtenteUrl = url;
        document.getElementById('popupBloccaUtente').classList.add('active');
    }

    function closePopupBloccaUtente(){
        document.getElementById('popupBloccaUtente').classList.remove('active');
        bloccaUtenteUrl = '';
    }

    function confirmBloccaUtente(){
        if(bloccaUtenteUrl){
            window.location.href = bloccaUtenteUrl;
        }
    }

    function openPopupSelfRole(){
        document.getElementById('popupSelfRole').classList.add('active');
    }
    function closePopupSelfRole(){
        document.getElementById('popupSelfRole').classList.remove('active');
    }

    function openPopupSelfDelete(){
        document.getElementById('popupSelfDelete').classList.add('active');
    }
    function closePopupSelfDelete(){
        document.getElementById('popupSelfDelete').classList.remove('active');
    }
</script>

</body>
</html>
