<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: ../frontend/signup.php');
    exit;
}

function passwordSicura($password){
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password) &&
           preg_match('/[\W]/', $password);
}

require_once __DIR__ . '/../../../config.php';

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confermapassword'] ?? '';
$universita = $_POST['universita'] ?? '';
$facolta = $_POST['facolta'] ?? '';

$errors = [];

if($username === ''){
    $errors[] = 'Username è obbligatorio.';
}
if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors[] = 'Email non valida.';
}
if($password === ''){
    $errors[] = 'Password è obbligatoria.';
}
if($password !== $confirmPassword){
    header('Location: ../frontend/signup.php?pwdmismatch=1');
    exit;
}else{
    if(!passwordSicura($password)){
        header('Location: ../frontend/signup.php?weakpw=1');
        exit;
    }
}
if(!ctype_digit($universita) || !ctype_digit($facolta)){
    $errors[] = 'Seleziona università e facoltà valide.';
}


if(!empty($errors)){
    $message = implode('<br>', array_map('htmlspecialchars', $errors));
    echo "<p>$message</p>";
    echo '<p><a href="../frontend/signup.php">Torna alla registrazione</a></p>';
    exit;
}

$connection = db_connect();

$query = 'SELECT id_utente FROM utenti WHERE username = ? OR email = ?';
$stmt = mysqli_prepare($connection, $query);
if(!$stmt){
    die('Errore nella preparazione della query: ' . mysqli_error($connection));
}
mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0){
    mysqli_stmt_close($stmt);
    echo '<p style="color:red;">Username o email già in uso.</p>';
    echo '<p><a href="../frontend/signup.php">Torna alla registrazione</a></p>';
    mysqli_close($connection);
    exit;
}

mysqli_stmt_close($stmt);
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$ruolo = 0;

$inserimentoQuery = 'INSERT INTO utenti (username, password, email, ruolo, id_universita, id_facolta, data_iscrizione) VALUES (?, ?, ?, ?, ?, ?, NOW())';
$insertStmt = mysqli_prepare($connection, $inserimentoQuery);
if(!$insertStmt){
    die('Errore nella preparazione della query di inserimento: ' . mysqli_error($connection));
}
mysqli_stmt_bind_param($insertStmt, 'sssiii', $username, $passwordHash, $email, $ruolo, $universita, $facolta);
$success = mysqli_stmt_execute($insertStmt);

if($success){
    mysqli_stmt_close($insertStmt);
    mysqli_close($connection);
    header('Location: ../frontend/login.php');
    exit;
}

$errorMessage = mysqli_error($connection);
mysqli_stmt_close($insertStmt);
mysqli_close($connection);
echo '<p style="color:red;">Errore durante la registrazione: ' . htmlspecialchars($errorMessage) . '</p>';
echo '<p><a href="../frontend/signup.php">Torna alla registrazione</a></p>';
?>
