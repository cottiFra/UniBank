<?php
session_start();
require_once __DIR__ . '/../../../config.php';

if (!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] !== true) {
    header("Location: ../../authentication/frontend/login.php");
    exit();
}

$conn = db_connect();

$titolo = trim($_POST['titolo'] ?? '');
$descrizione = trim($_POST['descrizione'] ?? '');
$prezzo = (int)($_POST['prezzo'] ?? 0);
$id_materia = (int)($_POST['corso'] ?? 0); 
$id_facolta = $_SESSION['id_facolta']; 
$id_utente = $_SESSION['user_id'];

if(!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK){
    $_SESSION['upload_error'] = 'Nessun file ricevuto o errore durante l\'upload.';
    header('Location: ../uploadmaterial.php');
    exit();
}

$file = $_FILES['file'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));


$base_dir = dirname(dirname(dirname(__DIR__)));
$upload_dir = $base_dir . DIRECTORY_SEPARATOR . 'dispense' . DIRECTORY_SEPARATOR;

$new_file_name = uniqid('dispensa_', true) . '.' . $file_extension;
$target_path = $upload_dir . $new_file_name;
$percorso_db = 'dispense/' . $new_file_name;

if(move_uploaded_file($file['tmp_name'], $target_path)){
    
    $queryRel = "SELECT id_materiaperfacolta FROM materiaperfacolta WHERE id_materia = $id_materia AND id_facolta = $id_facolta";
    $risRel = mysqli_query($conn, $queryRel);
    $relazione = mysqli_fetch_assoc($risRel);

    if($relazione){
        $id_materiaperfacolta = $relazione['id_materiaperfacolta'];

        $titolo_sicuro = mysqli_real_escape_string($conn, $titolo);
        $descrizione_sicura = mysqli_real_escape_string($conn, $descrizione);

        $queryInsert = "INSERT INTO dispense (titolo, descrizione, prezzo, percorso_file, data_caricamento, id_utente, id_materiaperfacolta) 
                        VALUES ('$titolo_sicuro', '$descrizione_sicura', $prezzo, '$percorso_db', NOW(), $id_utente, $id_materiaperfacolta)";
        
        $risInsert = mysqli_query($conn, $queryInsert);
        
        if($risInsert){
            $_SESSION['upload_success'] = 'Dispensa inviata con successo, ora aspetta l\'approvazione da parte degli admin per vederla online';
        }else{
            $_SESSION['upload_error'] = 'Errore durante il salvataggio nel database.';
            unlink($target_path);
        }
    }else{
        $_SESSION['upload_error'] = 'Materia non valida per la tua facoltà.';
        unlink($target_path);
    }
} else {
    $_SESSION['upload_error'] = 'Errore durante lo spostamento fisico del file.';
}

header('Location: ../../profile/profile.php');
exit();
?>
