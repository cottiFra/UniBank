<?php
session_start();
require_once __DIR__ . '/../../../config.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../frontend/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors[] = 'Email non valida.';
}
if($password === ''){
    $errors[] = 'Password è obbligatoria.';
}

if(!empty($errors)){
    if(in_array('Email non valida.', $errors)){
        header('Location: ../frontend/login.php?invalid=1');
        exit;
    }
    if(in_array('Password è obbligatoria.', $errors)){
        header('Location: ../frontend/login.php?pwreq=1');
        exit;
    }
    header('Location: ../frontend/login.php?error=1');
    exit;
}

$connection = db_connect();

$query = 'SELECT id_utente, username, password, ruolo, email, bloccato FROM utenti WHERE email = ? LIMIT 1';
$stmt = mysqli_prepare($connection, $query);
if(!$stmt){
    die('Errore nella preparazione della query: ' . mysqli_error($connection));
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$user || !password_verify($password, $user['password'])){
    mysqli_close($connection);
    header('Location: ../frontend/login.php?wrong=1');
    exit;
}

if((int)$user['bloccato'] === 1){
    mysqli_close($connection);
    header('Location: ../frontend/login.php?blocked=1');
    exit;
}

$_SESSION['user_id'] = $user['id_utente'];
$_SESSION['username'] = $user['username'];
$_SESSION['ruolo'] = $user['ruolo'];
$_SESSION['email'] = $user['email'];
$_SESSION['is_logged'] = true;

$query = "
    SELECT u.nome AS nome_universita, f.nome AS nome_facolta, ut.id_facolta
    FROM universita u, facolta f, utenti ut
    WHERE ut.id_universita = u.id_universita
    AND ut.id_facolta = f.id_facolta
    AND ut.id_utente = {$_SESSION['user_id']}
";

$ris = mysqli_query($connection, $query);
$record = mysqli_fetch_assoc($ris);

$_SESSION['universita'] = $record['nome_universita'];
$_SESSION['facolta'] = $record['nome_facolta'];
$_SESSION['id_facolta'] = $record['id_facolta']; 

mysqli_close($connection);

if((int)$user['ruolo'] === 1){
    header('Location: ../../admin/adminpanoramica.php');
    exit;
}

header('Location: ../../index.php');
exit;
?>