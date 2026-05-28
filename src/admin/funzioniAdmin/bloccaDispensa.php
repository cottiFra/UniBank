<?php
session_start();
require_once __DIR__ . '/../../../config.php';
$conn = db_connect();

if(!isset($_SESSION['user_id']) || $_SESSION['ruolo'] != 1){
    die("Accesso negato.");
}

$idDispensa = intval($_REQUEST['id_dispensa']);
$query = "UPDATE dispense SET bloccata = 1 WHERE id_dispensa = {$idDispensa}";
$ris = mysqli_query($conn,$query);
header("Location: ../adminmateriali.php");
?>
