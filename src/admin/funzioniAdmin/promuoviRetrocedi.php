<?php
session_start();
require_once __DIR__ . '/../../../config.php';
$conn = db_connect();
$idUtente = intval($_REQUEST['id_utente']);
if($idUtente == $_SESSION['user_id']){
    header("Location: ../adminusers.php?selfRole=1");
    exit;
}else{
    if($_SESSION['ruolo'] == 1){
    $query = "SELECT ruolo FROM utenti WHERE id_utente = {$idUtente}";
    $ris = mysqli_query($conn,$query);
    $riga = mysqli_fetch_assoc($ris);
    if($riga['ruolo'] == 0){
        $query2 = "
                UPDATE utenti
                SET ruolo = 1
                WHERE id_utente = {$idUtente}
        ";
        $ris2 = mysqli_query($conn,$query2);
    }else{
        $query3 = "
                UPDATE utenti
                SET ruolo = 0
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