<?php
session_start();
require_once __DIR__ . '/../../../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../contactus.php');
    exit();
}

$nome      = trim($_POST['nome']      ?? '');
$email     = trim($_POST['email']     ?? '');
$oggetto   = trim($_POST['oggetto']   ?? '');
$motivo    = trim($_POST['motivo']    ?? '');
$messaggio = trim($_POST['messaggio'] ?? '');
$privacy   = isset($_POST['privacy']);

$_SESSION['contact_old'] = [
    'nome'      => $nome,
    'email'     => $email,
    'oggetto'   => $oggetto,
    'motivo'    => $motivo,
    'messaggio' => $messaggio,
];

$motivi_validi = ['problema_tecnico','segnalazione','pagamenti','account','suggerimento','altro'];

if($nome === '' || $email === '' || $oggetto === '' || $motivo === '' || $messaggio === ''){
    $_SESSION['contact_error'] = 'Tutti i campi sono obbligatori.';
    header('Location: ../contactus.php');
    exit();
}
if(mb_strlen($nome) > 100 || mb_strlen($email) > 255 || mb_strlen($oggetto) > 150 || mb_strlen($messaggio) > 2000){
    $_SESSION['contact_error'] = 'Uno o piu\' campi superano la lunghezza massima consentita.';
    header('Location: ../contactus.php');
    exit();
}
if(mb_strlen($messaggio) < 10){
    $_SESSION['contact_error'] = 'Il messaggio deve contenere almeno 10 caratteri.';
    header('Location: ../contactus.php');
    exit();
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $_SESSION['contact_error'] = 'Indirizzo email non valido.';
    header('Location: ../contactus.php');
    exit();
}
if(!in_array($motivo, $motivi_validi, true)){
    $_SESSION['contact_error'] = 'Motivo del contatto non valido.';
    header('Location: ../contactus.php');
    exit();
}
if(!$privacy){
    $_SESSION['contact_error'] = 'Devi accettare il trattamento dei dati personali.';
    header('Location: ../contactus.php');
    exit();
}

$now_ts   = time();
$last_ts  = $_SESSION['contact_last_ts'] ?? 0;
if(($now_ts - $last_ts) < 30){
    $_SESSION['contact_error'] = 'Hai inviato un messaggio da poco. Attendi qualche secondo prima di riprovare.';
    header('Location: ../contactus.php');
    exit();
}

$log_dir  = __DIR__ . DIRECTORY_SEPARATOR . 'messaggi';
if(!is_dir($log_dir)){
    @mkdir($log_dir, 0775, true);
}
$log_file = $log_dir . DIRECTORY_SEPARATOR . 'contatti.log';

$id_utente = (isset($_SESSION['is_logged']) && $_SESSION['is_logged'] === true && isset($_SESSION['user_id']))
    ? (int)$_SESSION['user_id']
    : null;

$record = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
    'id_utente' => $id_utente,
    'nome'      => $nome,
    'email'     => $email,
    'oggetto'   => $oggetto,
    'motivo'    => $motivo,
    'messaggio' => $messaggio,
];
@file_put_contents($log_file, json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

$_SESSION['contact_last_ts'] = $now_ts;
unset($_SESSION['contact_old']);
$_SESSION['contact_success'] = 'Messaggio inviato con successo! Ti risponderemo all\'indirizzo ' . $email . ' entro 24-48 ore.';

header('Location: ../contactus.php');
exit();
