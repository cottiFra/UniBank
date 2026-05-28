<?php
session_start();
require_once __DIR__ . '/../../config.php';
$conn = db_connect();

if(!isset($_SESSION['user_id']) || $_SESSION['ruolo'] != 1){
    header('Location: ../authentication/frontend/login.php');
    exit;
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>UniBank - Storico Acquisti</title>
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
            <a href="adminusers.php" class="tab-item">Gestione utenti</a>
            <a href="adminmateriali.php" class="tab-item">Gestione materiali</a>
            <a href="adminstoricoacquisti.php" class="tab-item active">Storico acquisti</a>
            <a href="adminmessaggi.php" class="tab-item">Messaggi di contatto</a>
        </div>
    </nav>

    <div class="admin-container">
        <section class="admin-header-actions">
            <h4>Storico Acquisti</h4>
            <form method="GET" action="adminstoricoacquisti.php" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; margin: 0;">
                <div class="search-bar" style="margin: 0;">
                    <?php 
                    $search_val = "";
                    if(isset($_GET['search'])){
                        $search_val = htmlspecialchars($_GET['search']);
                    }
                    ?>
                    <input type="text" name="search" placeholder="Cerca per acquirente, dispensa o venditore" value="<?php echo $search_val; ?>">
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
                        <option value="prezzo_desc" <?php if($current_sort == 'prezzo_desc') echo 'selected'; ?>>Prezzo: Decrescente</option>
                        <option value="prezzo_asc" <?php if($current_sort == 'prezzo_asc') echo 'selected'; ?>>Prezzo: Crescente</option>
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
                            <th>DATA ACQUISTO</th>
                            <th>ACQUIRENTE</th>
                            <th>DISPENSA</th>
                            <th>VENDITORE</th>
                            <th>PREZZO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "
                                SELECT a.id_acquisto, a.data_acquisto, u.username AS acquirente, d.titolo AS dispensa, d.prezzo, v.username AS venditore 
                                FROM acquisti a
                                JOIN utenti u ON a.id_utente = u.id_utente
                                JOIN dispense d ON a.id_dispensa = d.id_dispensa
                                JOIN utenti v ON d.id_utente = v.id_utente
                                WHERE 1=1
                            ";
                            
                            if(isset($_GET['search']) && $_GET['search'] != ''){
                                $search = mysqli_real_escape_string($conn, trim($_GET['search']));
                                $query .= " AND (u.username LIKE '%$search%' OR d.titolo LIKE '%$search%' OR v.username LIKE '%$search%')";
                            }

                            if(isset($_GET['sort'])){
                                $sort = $_GET['sort'];
                                if($sort == 'data_asc'){
                                    $query .= " ORDER BY a.data_acquisto ASC";
                                }else if($sort == 'prezzo_asc'){
                                    $query .= " ORDER BY d.prezzo ASC";
                                }else if($sort == 'prezzo_desc'){
                                    $query .= " ORDER BY d.prezzo DESC";
                                }else{
                                    $query .= " ORDER BY a.data_acquisto DESC";
                                }
                            }else{
                                $query .= " ORDER BY a.data_acquisto DESC";
                            }

                            $ris = mysqli_query($conn, $query);
                            if(mysqli_num_rows($ris) > 0) {
                                while($riga = mysqli_fetch_assoc($ris)){
                                    echo '<tr>';
                                    echo    '<td>' . substr($riga['data_acquisto'], 0, 16) . '</td>';
                                    echo    '<td><span class="user-name">' . htmlspecialchars($riga['acquirente']) . '</span></td>';
                                    echo    '<td>' . htmlspecialchars($riga['dispensa']) . '</td>';
                                    echo    '<td><span class="user-name">' . htmlspecialchars($riga['venditore']) . '</span></td>';
                                    echo    '<td><span class="token-count">' . $riga['prezzo'] . '</span></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" style="text-align:center; padding: 20px;">Nessun acquisto trovato.</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
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
    });
</script>

</body>
</html>
