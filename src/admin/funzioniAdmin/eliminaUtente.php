<?php
session_start();
require_once __DIR__ . '/../../../config.php';
$conn = db_connect();
$idUtente = intval($_REQUEST['id_utente']);
if($idUtente == $_SESSION['user_id']){
    header("Location: ../adminusers.php?selfDelete=1");
    exit;
}else{
    if($_SESSION['ruolo'] == 1){
        $query1 = "DELETE FROM acquisti WHERE id_utente = {$idUtente}";
        mysqli_query($conn, $query1);

        $query2 = "DELETE FROM acquisti WHERE id_dispensa IN (SELECT id_dispensa FROM dispense WHERE id_utente = {$idUtente})";
        mysqli_query($conn, $query2);

        $query3 = "DELETE FROM dispense WHERE id_utente = {$idUtente}";
        mysqli_query($conn, $query3);

        $query4 = "DELETE FROM utenti WHERE id_utente = {$idUtente}";
        $ris = mysqli_query($conn, $query4);
        
        header("Location: ../adminusers.php");
    }else{
        header("Location: ../adminusers.php");
    }
}
?>