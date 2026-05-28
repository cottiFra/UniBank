<?php
session_start();
require_once __DIR__ . '/../../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['is_logged'] !== true){
    die('Non autorizzato. Effettua il login.');
}

$id_dispensa = intval($_REQUEST['id_dispensa']);
$conn = db_connect();

if($_SESSION['ruolo'] == 1){
    $query = "SELECT percorso_file, titolo FROM dispense WHERE id_dispensa = $id_dispensa";
}else{
    $query = "
        SELECT d.percorso_file, d.titolo
        FROM acquisti a
        JOIN dispense d ON a.id_dispensa = d.id_dispensa
        WHERE a.id_utente = {$_SESSION['user_id']}
        AND a.id_dispensa = $id_dispensa
    ";
}

$ris = mysqli_query($conn, $query);

if(!$ris || mysqli_num_rows($ris) === 0){
    die('Non hai i permessi per scaricare questa dispensa o non esiste.');
}


$row = mysqli_fetch_assoc($ris);
$percorso_file = $row['percorso_file'];
$titolo = $row['titolo'];

$file_path = __DIR__ . '/../../' . $percorso_file;

if(!file_exists($file_path)){
    die('File non trovato.');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Pragma: public');
header('Cache-Control: public, must-revalidate');

readfile($file_path);
mysqli_close($conn);
exit;
?>
