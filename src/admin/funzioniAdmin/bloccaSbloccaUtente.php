<?php
session_start();
require_once __DIR__ . '/../../../config.php';
$conn = db_connect();
$idUtente = intval($_REQUEST['id_utente']);
if($idUtente == $_SESSION['user_id']){
    echo 'non puoi bloccare te stesso';
    echo '<br>';
    echo '<a href="../adminusers.php">torna indietro</a>';
}else{
    if($_SESSION['ruolo'] == 1){
    $query = "SELECT bloccato FROM utenti WHERE id_utente = {$idUtente}";
    $ris = mysqli_query($conn,$query);
    $riga = mysqli_fetch_assoc($ris);
    if($riga['bloccato'] == 0){
        $query2 = "
                UPDATE utenti
                SET bloccato = 1
                WHERE id_utente = {$idUtente}
        ";
        $ris2 = mysqli_query($conn,$query2);
    }else{
        $query3 = "
                UPDATE utenti
                SET bloccato = 0
                WHERE id_utente = {$idUtente}
        ";
        $ris3 = mysqli_query($conn,$query3);
    }
        header("Location: ../adminusers.php");
    }else{
        header("Location: ../adminusers.php");
    }
}

?>