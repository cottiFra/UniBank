<?php
session_start();
require_once __DIR__ . '/../../config.php';

$conn = db_connect();

$contact_success = $_SESSION['contact_success'] ?? '';
$contact_error   = $_SESSION['contact_error']   ?? '';
$contact_old     = $_SESSION['contact_old']     ?? [];
unset($_SESSION['contact_success'], $_SESSION['contact_error'], $_SESSION['contact_old']);

$prefill_nome  = '';
$prefill_email = '';
if(isset($_SESSION['is_logged']) && $_SESSION['is_logged'] === true && isset($_SESSION['user_id'])){
    $uid = (int)$_SESSION['user_id'];
    $q = "SELECT username, email FROM utenti WHERE id_utente = $uid";
    $r = mysqli_query($conn, $q);
    if($r && ($u = mysqli_fetch_assoc($r))){
        $prefill_nome  = $u['username'];
        $prefill_email = $u['email'];
    }
}
$val_nome     = htmlspecialchars($contact_old['nome']     ?? $prefill_nome);
$val_email    = htmlspecialchars($contact_old['email']    ?? $prefill_email);
$val_oggetto  = htmlspecialchars($contact_old['oggetto']  ?? '');
$val_motivo   = htmlspecialchars($contact_old['motivo']   ?? '');
$val_messaggio= htmlspecialchars($contact_old['messaggio']?? '');
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>UniBank - Contattaci</title>
    <link rel="stylesheet" href="contactus.css?=<?php echo time();?>">
    <link rel="stylesheet" href="../variables.css?=<?php echo time();?>">
</head>
<body>
    <header class="navbar">
        <div class="nbcontainer">
            <div class="logo">
                <img src="../../assets/logo%20lungo7.png" alt="logo Unibank">
            </div>
            <div class="menu">
                <ul>
                    <li>
                        <a href="../index.php" class="listelement">Home</a>
                    </li>
                    <?php
                    if(!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){ ?>
                        <li>
                            <a href="../authentication/frontend/login.php">
                                <button class="loginbtn">Login</button>
                            </a>
                        </li>
                    <?php } ?>
                    <?php
                    if(!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){?>
                        <li>
                            <a href="../authentication/frontend/signup.php">
                                <button class="signupbtn">Registrati</button>
                            </a>
                        </li>
                    <?php } ?>
                    <?php
                    if(isset($_SESSION['is_logged']) && $_SESSION['is_logged'] == true){ ?>
                        <li>
                            <div class="profileicon">
                                <img src="../../assets/user.png" alt="user">
                            </div>
                            <div class="userpopup">
                                <div class="uppfavatar">
                                    <?php
                                    $initials = '';
                                    if(isset($_SESSION['username'])){
                                        $name = trim($_SESSION['username']);
                                        $initials = strtoupper(substr($name,0,1));
                                    }else{ $initials = 'U'; }
                                    ?>
                                    <span><?php echo $initials; ?></span>
                                </div>
                                <span>Ciao, <?php echo $_SESSION['username'] ?></span>
                                <span>Saldo:
                                    <?php
                                    $query = "SELECT saldo FROM utenti WHERE id_utente = {$_SESSION['user_id']}";
                                    $ris = mysqli_query($conn, $query);
                                    if($ris){
                                        $row = mysqli_fetch_assoc($ris);
                                        echo htmlspecialchars($row['saldo']);
                                    }else{
                                        echo '0';
                                    }
                                    ?>
                                    <img src="../../assets/unitoken.png" alt="UT"></span>
                                <a href="../profile/profile.php" class="mioprofile"><button class="visprofilebtn">Visualizza profilo</button></a>
                                <a href="../authentication/backend/logout.php"><button class="logoutbtn">Logout</button></a>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </header>

    <div class="container">
        <div style="display: flex; flex-direction: column; gap: 10px; text-align: left; width: 100%">
            <h1 style="color: var(--color-blue-background)">Contattaci</h1>
            <div class="content">
            <div class="uploadbox">
                <?php if(!empty($contact_success)){ ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($contact_success); ?></div>
                <?php } ?>
                <?php if(!empty($contact_error)){ ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($contact_error); ?></div>
                <?php } ?>
                <form action="funzioniContact/elaborazioneContact.php" method="post" novalidate>
                    <?php
                    $is_logged = isset($_SESSION['is_logged']) && $_SESSION['is_logged'] === true;
                    ?>
                    <div class="formgrid">
                        <div class="inputbox">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" placeholder="Il tuo nome" value="<?php echo $val_nome; ?>" maxlength="100" required <?php if($is_logged) echo 'disabled'; ?>>
                            <?php if($is_logged) echo '<input type="hidden" name="nome" value="'.htmlspecialchars($val_nome).'">'; ?>
                        </div>
                        <div class="inputbox">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="nome@esempio.com" value="<?php echo $val_email; ?>" maxlength="255" required <?php if($is_logged) echo 'disabled'; ?>>
                            <?php if($is_logged) echo '<input type="hidden" name="email" value="'.htmlspecialchars($val_email).'">'; ?>
                        </div>
                        <div class="inputbox fullwidth">
                            <label for="oggetto">Oggetto</label>
                            <input type="text" id="oggetto" name="oggetto" placeholder="Oggetto del messaggio" value="<?php echo $val_oggetto; ?>" maxlength="150" required>
                        </div>
                        <div class="inputbox fullwidth">
                            <label for="motivo">Motivo del contatto</label>
                            <select id="motivo" name="motivo" required>
                                <option value="">Seleziona un motivo</option>
                                <?php
                                $motivi = [
                                    'problema_tecnico' => 'Problema tecnico',
                                    'segnalazione'     => 'Segnalazione contenuto',
                                    'pagamenti'        => 'Pagamenti / UniToken',
                                    'account'          => 'Account / accesso',
                                    'suggerimento'     => 'Suggerimento',
                                    'altro'            => 'Altro',
                                ];
                                foreach($motivi as $k => $label){
                                    $sel = ($val_motivo === $k) ? ' selected' : '';
                                    echo '<option value="'.$k.'"'.$sel.'>'.$label.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="inputbox fullwidth">
                            <label for="messaggio">Messaggio</label>
                            <textarea class="description" id="messaggio" name="messaggio" cols="30" rows="8" placeholder="Descrivi nel dettaglio la tua richiesta..." maxlength="2000" required><?php echo $val_messaggio; ?></textarea>
                            <small id="charcount" style="align-self: flex-end; color: #999; font-size: 12px;">0 / 2000</small>
                        </div>
                        <div class="inputbox fullwidth">
                            <label class="privacylabel">
                                <input type="checkbox" name="privacy" id="privacy" required>
                                <span>Ho letto e accetto il trattamento dei dati personali secondo l'informativa sulla privacy.</span>
                            </label>
                        </div>
                        <div class="inputbox fullwidth">
                            <button class="publishbtn" type="submit">Invia messaggio</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="infobox">
                <h4 style="display: flex; justify-content: flex-start; align-items: center; gap: 10px; color: var(--color-yellow-primary); margin-bottom: 15px; font-size: 18px;"><img style="width: 20px" src="../../assets/info.png" alt="i"> Hai bisogno di aiuto?</h4>
                <p class="infop">Hai domande, problemi o suggerimenti?
                    Compila il modulo di contatto e ti risponderemo il prima possibile. Il nostro team è sempre disponibile per aiutarti.</p>
                <div class="suggestedpricebox">
                    <p>Tempi di risposta</p>
                    <h2 style="color: var(--color-yellow-primary)">Entro 24-48 ore</h2>
                    <p>Cerchiamo di rispondere rapidamente a tutte le richieste.</p>
                </div>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px;">
                    <li class="li">
                        <span style="color: var(--color-yellow-primary)">•</span>
                        <p class="infop">Descrivi il problema in modo chiaro</p>
                    </li>
                    <li class="li">
                        <span style="color: var(--color-yellow-primary)">•</span>
                        <p class="infop">Inserisci un'email valida per ricevere risposta</p>
                    </li>
                    <li class="li">
                        <span style="color: var(--color-yellow-primary)">•</span>
                        <p class="infop">Ti aggiorneremo via mail appena riceveremo il messaggio</p>
                    </li>
                </ul>
            </div>
            </div>
        </div>
    </div>
    <script>
        const messaggio = document.getElementById('messaggio');
        const charcount = document.getElementById('charcount');
        const maxLen = 2000;
        function updateCount(){
            const len = messaggio.value.length;
            charcount.textContent = len + ' / ' + maxLen;
            charcount.style.color = len > maxLen * 0.9 ? '#c20000' : '#999';
        }
        messaggio.addEventListener('input', updateCount);
        updateCount();

        document.querySelector('form').addEventListener('submit', function(e){
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(email)){
                e.preventDefault();
                alert('Inserisci un indirizzo email valido.');
                return;
            }
            if(messaggio.value.trim().length < 10){
                e.preventDefault();
                alert('Il messaggio deve contenere almeno 10 caratteri.');
                return;
            }
            if(!document.getElementById('privacy').checked){
                e.preventDefault();
                alert('Devi accettare il trattamento dei dati personali per inviare il messaggio.');
            }
        });
    </script>
</body>
</html>
