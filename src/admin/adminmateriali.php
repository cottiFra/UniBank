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
    <title>UniBank - Gestione Materiali</title>
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
                            }else{$initials = 'U'; }
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
            <a href="adminusers.php" class="tab-item">Gestione utenti</a>
            <a href="adminmateriali.php" class="tab-item active">Gestione materiali</a>
            <a href="adminstoricoacquisti.php" class="tab-item">Storico acquisti</a>
            <a href="adminmessaggi.php" class="tab-item">Messaggi di contatto</a>
        </div>
    </nav>

    <div class="admin-container">
        <section class="admin-header-actions">
            <h4>Gestione Materiali</h4>
            <form method="GET" action="adminmateriali.php" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; margin: 0;">
                <div class="search-bar" style="margin: 0;">
                    <?php 
                    $search_val = "";
                    if(isset($_GET['search'])){
                        $search_val = htmlspecialchars($_GET['search']);
                    }
                    ?>
                    <input type="text" name="search" placeholder="Cerca materiale per titolo o autore" value="<?php echo $search_val; ?>">
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
                        <option value="data_desc" <?php if($current_sort == 'data_desc') echo 'selected'; ?>>Data: Più recenti</option>
                        <option value="data_asc" <?php if($current_sort == 'data_asc') echo 'selected'; ?>>Data: Meno recenti</option>
                        <option value="download_desc" <?php if($current_sort == 'download_desc') echo 'selected'; ?>>Downloads: Decrescente</option>
                        <option value="download_asc" <?php if($current_sort == 'download_asc') echo 'selected'; ?>>Downloads: Crescente</option>
                        <option value="revisione" <?php if($current_sort == 'revisione') echo 'selected'; ?>>Stato: Prima in revisione</option>
                        <option value="approvata" <?php if($current_sort == 'approvata') echo 'selected'; ?>>Stato: Prima approvate</option>
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
                            <th>TITOLO</th>
                            <th>AUTORE</th>
                            <th>DATA CARICAMENTO</th>
                            <th>ACQUISTI</th>
                            <th>STATO</th>
                            <th>AZIONI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "
                                    SELECT d.id_dispensa, d.titolo, d.data_caricamento, d.approvata, f.nome AS nomeFacolta, u.username AS nomeUser, u.bloccato, d.bloccata AS dispensaBloc,
                                           (SELECT COUNT(*) FROM acquisti WHERE id_dispensa = d.id_dispensa) AS numDownloads
                                    FROM dispense d, facolta f, utenti u
                                    WHERE d.id_utente = u.id_utente
                                    AND u.id_facolta = f.id_facolta 
                            ";
                            
                            if(isset($_GET['search']) && $_GET['search'] != ''){
                                $search = mysqli_real_escape_string($conn, trim($_GET['search']));
                                $query .= " AND (d.titolo LIKE '%$search%' OR u.username LIKE '%$search%')";
                            }

                            if(isset($_GET['sort'])){
                                $sort = $_GET['sort'];
                                if($sort == 'data_asc'){
                                    $query .= " ORDER BY d.data_caricamento ASC";
                                }else if($sort == 'download_desc'){
                                    $query .= " ORDER BY numDownloads DESC";
                                }else if($sort == 'download_asc'){
                                    $query .= " ORDER BY numDownloads ASC";
                                }else if($sort == 'revisione'){
                                    $query .= " ORDER BY d.approvata ASC, d.data_caricamento DESC";
                                }else if($sort == 'approvata'){
                                    $query .= " ORDER BY d.approvata DESC, d.data_caricamento DESC";
                                }
                                else{
                                    $query .= " ORDER BY d.data_caricamento DESC";
                                }
                            }else{
                                $query .= " ORDER BY d.data_caricamento DESC";
                            }

                            $ris = mysqli_query($conn, $query);
                            while($riga = mysqli_fetch_assoc($ris)){
                                $data = substr($riga['data_caricamento'], 0, 10);
                                $downloads = $riga['numDownloads'] ?? 0;
                                
                                echo '<tr>';
                                echo '    <td>';
                                echo '        <div class="material-info-table">';
                                echo '            <span class="material-title">'.$riga['titolo'].'</span>';
                                echo '            <span class="material-faculty">'.$riga['nomeFacolta'].'</span>';
                                echo '        </div>';
                                echo '    </td>';
                                echo '    <td>'.$riga['nomeUser'].'</td>';
                                echo '    <td>'.$data.'</td>';
                                echo '    <td><span class="download-count">'.$downloads.'</span></td>';
                                
                                if($riga['bloccato'] == 1 || $riga['dispensaBloc'] == 1){
                                    echo '<td><span class="status status-blocked">Bloccata</span></td>';
                                    echo '<td>';
                                    echo '    <div class="action-buttons">';
                                    if($riga['dispensaBloc'] == 1){
                                        echo '        <a href="funzioniAdmin/sbloccaDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><button class="unblock-btn">Sblocca</button></a>';
                                    }
                                    echo '        <a href="../downloadDispense/downloadDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><button class="view-btn">Vedi</button></a>';
                                    echo '        <button class="delete-btn-table" onclick="openPopupEliminaDispensa(\'funzioniAdmin/eliminaDispensa.php?id_dispensa='.$riga['id_dispensa'].'\')">Elimina</button>';
                                    echo '    </div>';
                                    echo '</td>';
                                }else if($riga['approvata'] == 0){
                                    echo '<td><span class="status status-pending">In revisione</span></td>';
                                    echo '<td>';
                                    echo '    <div class="action-buttons">';
                                    echo '        <a href="funzioniAdmin/approvaDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><button class="approve-btn">Approva</button></a>';
                                    echo '        <a href="funzioniAdmin/valutaDispensaAI.php?id_dispensa='.$riga['id_dispensa'].'"><button class="view-btn">Valuta AI </button></a>';
                                    echo '        <a href="../downloadDispense/downloadDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><button class="view-btn">Vedi</button></a>';
                                    echo '        <button class="delete-btn-table" onclick="openPopupEliminaDispensa(\'funzioniAdmin/eliminaDispensa.php?id_dispensa='.$riga['id_dispensa'].'\')">Elimina</button>';
                                    echo '    </div>';
                                    echo '</td>';
                                }else{
                                    echo '<td><span class="status status-active">Approvato</span></td>';
                                    echo '<td>';
                                    echo '    <div class="action-buttons">';
                                    echo '        <a href="funzioniAdmin/bloccaDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><button class="block-btn">Blocca</button></a>';
                                    echo '        <a href="../downloadDispense/downloadDispensa.php?id_dispensa='.$riga['id_dispensa'].'"><button class="view-btn">Vedi</button></a>';
                                    echo '        <button class="delete-btn-table" onclick="openPopupEliminaDispensa(\'funzioniAdmin/eliminaDispensa.php?id_dispensa='.$riga['id_dispensa'].'\')">Elimina</button>';
                                    echo '    </div>';
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div class="popup-overlay" id="popupEliminaDispensa">
    <div class="popup-box">
        <h3>Conferma eliminazione</h3>
        <p>Sei sicuro di voler eliminare questa dispensa? L'azione non è reversibile.</p>
        <div style="display:flex; gap:10px; justify-content:center; align-items: center; margin-top: 10px;">
            <button class="popup-btn" onclick="closePopupEliminaDispensa()">Annulla</button>
            <button class="popup-btn" onclick="confirmEliminaDispensa()">Conferma</button>
        </div>
    </div>
</div>

<script>
    let eliminaDispensaUrl = '';
    function openPopupEliminaDispensa(url) {
        eliminaDispensaUrl = url;
        document.getElementById('popupEliminaDispensa').classList.add('active');
    }
    function closePopupEliminaDispensa() {
        document.getElementById('popupEliminaDispensa').classList.remove('active');
        eliminaDispensaUrl = '';
    }
    function confirmEliminaDispensa() {
        if (eliminaDispensaUrl) {
            window.location.href = eliminaDispensaUrl;
        }
    }
</script>

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
    });
</script>
</body>
</html>
