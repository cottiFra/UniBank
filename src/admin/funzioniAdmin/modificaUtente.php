<?php
session_start();
require_once __DIR__ . '/../../../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['ruolo'] != 1){
    die("Accesso negato.");
}

$conn = db_connect();

if(isset($_GET['id_utente'])){
    $id_utente = intval($_GET['id_utente']);
}else if(isset($_POST['id_utente'])){
    $id_utente = intval($_POST['id_utente']);
}else{
    die("ID utente non specificato.");
}

$success_msg = "";
$error_msg = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $saldo = intval($_POST['saldo']);
    $id_universita = intval($_POST['id_universita']);
    $id_facolta = intval($_POST['id_facolta']);

    $update_query = "UPDATE utenti SET 
        username = '$username', 
        email = '$email', 
        saldo = $saldo, 
        id_universita = $id_universita, 
        id_facolta = $id_facolta 
        WHERE id_utente = $id_utente";

    if(mysqli_query($conn, $update_query)){
        $success_msg = "Utente aggiornato con successo!";
    }else{
        $error_msg = "Errore durante l'aggiornamento: " . mysqli_error($conn);
    }
}

$query = "SELECT * FROM utenti WHERE id_utente = $id_utente";
$ris = mysqli_query($conn, $query);
if(mysqli_num_rows($ris) == 0){
    die("Utente non trovato.");
}
$utente = mysqli_fetch_assoc($ris);

$q_uni = "SELECT * FROM universita";
$ris_uni = mysqli_query($conn, $q_uni);

$q_facolta = "SELECT * FROM facolta";
$ris_facolta = mysqli_query($conn, $q_facolta);

?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../assets/logowhitebg.png">
    <title>Modifica Utente - UniBank Admin</title>
    <link rel="stylesheet" href="../../variables.css?=<?php echo time();?>">
    <link rel="stylesheet" href="../admin.css?=<?php echo time();?>">
    <link rel="stylesheet" href="modificaUtente.css?=<?php echo time();?>">
</head>
<body>
<header class="navbar">
    <div class="nbcontainer">
        <div class="logo">
            <img src="../../../assets/logo%20lungo7.png" alt="logo Unibank">
        </div>
        <div class="menu">
            <ul>
                <li>
                    <a href="../adminusers.php" class="back-btn">← Torna a Gestione Utenti</a>
                </li>
            </ul>
        </div>
    </div>
</header>

<main class="edit-page">
    <div class="edit-container">
        <h2>Modifica Profilo Utente</h2>
        
        <?php 
        if(isset($success_msg) && $success_msg != ""){
            echo '<div class="alert success">' . $success_msg . '</div>';
        }
        if(isset($error_msg) && $error_msg != ""){
            echo '<div class="alert error">' . $error_msg . '</div>';
        }
        ?>

        <form action="modificaUtente.php" method="POST" class="edit-form">
            <input type="hidden" name="id_utente" value="<?php echo $utente['id_utente']; ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $utente['username']; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Indirizzo Email</label>
                <input type="email" id="email" name="email" value="<?php echo $utente['email']; ?>" required>
            </div>

            <div class="form-group">
                <label for="saldo">Saldo Token</label>
                <input type="number" id="saldo" name="saldo" value="<?php echo $utente['saldo']; ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="id_universita">Università</label>
                <select id="id_universita" name="id_universita" required>
                    <?php 
                    if(isset($ris_uni)){
                        while($uni = mysqli_fetch_assoc($ris_uni)){
                            echo '<option value="' . $uni['id_universita'] . '"';
                            if($uni['id_universita'] == $utente['id_universita']){
                                echo ' selected';
                            }
                            echo '>';
                            echo $uni['citta_sede'] . ' - ' . $uni['nome'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_facolta">Facoltà</label>
                <select id="id_facolta" name="id_facolta" required>
                    <?php 
                    if(isset($ris_facolta)){
                        while($fac = mysqli_fetch_assoc($ris_facolta)){
                            echo '<option value="' . $fac['id_facolta'] . '"';
                            if($fac['id_facolta'] == $utente['id_facolta']){
                                echo ' selected';
                            }
                            echo '>';
                            echo $fac['nome'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="save-btn">Salva Modifiche</button>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        const navbar = document.querySelector('.nbcontainer');
        function ombraNavbar(){
            if(window.scrollY === 0){
                navbar.classList.add('no-shadow');
            }else{
                navbar.classList.remove('no-shadow');
            }
        }
        ombraNavbar();
        window.addEventListener('scroll', ombraNavbar);
    });
</script>
</body>
</html>
