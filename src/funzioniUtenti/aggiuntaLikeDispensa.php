<?php
session_start();
require_once __DIR__ . '/../../config.php';
$conn = db_connect();

if(empty($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){
    header("Location: ../authentication/frontend/login.php");
    exit;
}

$idUtente = $_SESSION['user_id'];
$id_dispensa = intval($_REQUEST['id_dispensa']);
$from = $_GET['from'] ?? 'index';

$checkQuery = "SELECT * FROM likes WHERE id_dispensa = $id_dispensa AND id_utente = $idUtente";
$checkRis = mysqli_query($conn, $checkQuery);

if(mysqli_num_rows($checkRis) > 0){
    $query = "DELETE FROM likes WHERE id_dispensa = $id_dispensa AND id_utente = $idUtente";
}else{
    $query = "INSERT INTO likes (id_dispensa, id_utente, data_like) VALUES ($id_dispensa, $idUtente, NOW())";
}

mysqli_query($conn, $query);

if($from === 'cerca'){
    header("Location: cercaDispense.php");
    exit;
}

header("Location: ../index.php");
exit;
?>
