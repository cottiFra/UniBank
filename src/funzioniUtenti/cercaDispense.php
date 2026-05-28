<?php
session_start();
require_once __DIR__ . '/../../config.php';

$conn = db_connect();

if(!isset($_SESSION['is_logged'])){
    $_SESSION['is_logged'] = false;
}

$currentUserId = intval($_SESSION['user_id'] ?? 0);

$search = trim($_GET['q'] ?? '');
$idUniversita = intval($_GET['universita'] ?? 0);
$idFacolta = intval($_GET['facolta'] ?? 0);
$idMateria = intval($_GET['materia'] ?? 0);
$minPrezzo = $_GET['min'] ?? '';
$maxPrezzo = $_GET['max'] ?? '';
$sort = $_GET['sort'] ?? 'recenti';

$allowedSort = [
    'recenti' => 'd.data_caricamento DESC',
    'prezzo_asc' => 'd.prezzo ASC',
    'prezzo_desc' => 'd.prezzo DESC',
    'like_desc' => 'numLikes DESC'
];
$orderBy = $allowedSort[$sort] ?? $allowedSort['recenti'];

$queryUniversita = "SELECT id_universita, nome FROM universita ORDER BY nome";
$queryFacolta = "SELECT id_facolta, nome FROM facolta ORDER BY nome";
$queryMaterie = "SELECT id_materia, nome FROM materia ORDER BY nome";

$resultUniversita = mysqli_query($conn, $queryUniversita);
$resultFacolta = mysqli_query($conn, $queryFacolta);
$resultMaterie = mysqli_query($conn, $queryMaterie);

$where = [];
$where[] = "u.bloccato = 0";
$where[] = "d.approvata = 1";
$where[] = "d.bloccata = 0";

if($search !== ''){
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $where[] = "(d.titolo LIKE '%$safeSearch%' OR d.descrizione LIKE '%$safeSearch%' OR m.nome LIKE '%$safeSearch%')";
}

if($idUniversita > 0){
    $where[] = "u.id_universita = $idUniversita";
}
if($idFacolta > 0){
    $where[] = "f.id_facolta = $idFacolta";
}
if($idMateria > 0){
    $where[] = "m.id_materia = $idMateria";
}

if($minPrezzo !== '' && is_numeric($minPrezzo)){
    $where[] = "d.prezzo >= " . intval($minPrezzo);
}
if($maxPrezzo !== '' && is_numeric($maxPrezzo)){
    $where[] = "d.prezzo <= " . intval($maxPrezzo);
}

$whereSql = implode(' AND ', $where);

$queryDispense = "
    SELECT d.id_dispensa, d.titolo, d.descrizione, d.prezzo, u.username, m.nome AS materia, f.nome AS facolta, uni.nome AS universita,
    (SELECT COUNT(*) FROM likes l WHERE l.id_dispensa = d.id_dispensa) AS numLikes,
    (SELECT COUNT(*) FROM likes l2 WHERE l2.id_dispensa = d.id_dispensa AND l2.id_utente = $currentUserId) AS hasLiked
    FROM dispense d
    JOIN utenti u ON d.id_utente = u.id_utente
    JOIN universita uni ON u.id_universita = uni.id_universita
    JOIN materiaperfacolta mpf ON d.id_materiaperfacolta = mpf.id_materiaperfacolta
    JOIN materia m ON mpf.id_materia = m.id_materia
    JOIN facolta f ON mpf.id_facolta = f.id_facolta
    WHERE $whereSql
    ORDER BY $orderBy
";
$resultDispense = mysqli_query($conn, $queryDispense);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>Cerca Dispense - UniBank</title>
    <link rel="stylesheet" href="../variables.css?=<?php echo time();?>">
    <link rel="stylesheet" href="cercaDispense.css?=<?php echo time();?>">
</head>
<body>
    <header class="navbar">
        <div class="nbcontainer">
            <div class="logo">
                <a href="../index.php">
                    <img src="../../assets/logo%20lungo7.png" alt="logo Unibank">
                </a>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="../index.php" class="listelement">Home</a></li>
                    <li><a href="../contactus/contactus.php" class="listelement">Contattaci</a></li>
                    <?php if($_SESSION['is_logged'] == true){ ?>
                    <li><a href="../profile/profile.php" class="listelement">Profilo</a></li>
                    <?php } ?>
                    <?php if($_SESSION['is_logged'] != true){ ?>
                    <li><a href="../authentication/frontend/login.php"><button class="loginbtn">Login</button></a></li>
                    <li><a href="../authentication/frontend/signup.php"><button class="signupbtn">Registrati</button></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </header>

    <main class="searchpage">
        <section class="hero">
            <div class="herobox">
                <h1>Trova la dispensa giusta per il tuo corso</h1>
                <p>Filtra per universita, facolta e materia, poi acquista in pochi click con UniToken.</p>
            </div>
        </section>

        <section class="content">
            <form class="filters" method="GET" action="cercaDispense.php">
                <div class="filtersgrid">
                    <div class="formgroup full">
                        <label for="q">Ricerca</label>
                        <input id="q" type="text" name="q" placeholder="Titolo, descrizione o materia..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="formgroup">
                        <label for="universita">Universita</label>
                        <select id="universita" name="universita">
                            <option value="0">Tutte</option>
                            <?php
                            if($resultUniversita){
                                while($uni = mysqli_fetch_assoc($resultUniversita)){
                                    echo '<option value="' . $uni['id_universita'] . '"';
                                    if($idUniversita === intval($uni['id_universita'])){ echo ' selected'; }
                                    echo '>' . htmlspecialchars($uni['nome']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="formgroup">
                        <label for="facolta">Facolta</label>
                        <select id="facolta" name="facolta">
                            <option value="0">Tutte</option>
                            <?php
                            if($resultFacolta){
                                while($fac = mysqli_fetch_assoc($resultFacolta)){
                                    echo '<option value="' . $fac['id_facolta'] . '"';
                                    if($idFacolta === intval($fac['id_facolta'])){ echo ' selected'; }
                                    echo '>' . htmlspecialchars($fac['nome']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="formgroup">
                        <label for="materia">Materia</label>
                        <select id="materia" name="materia">
                            <option value="0">Tutte</option>
                            <?php
                            if($resultMaterie){
                                while($mat = mysqli_fetch_assoc($resultMaterie)){
                                    echo '<option value="' . $mat['id_materia'] . '"';
                                    if($idMateria === intval($mat['id_materia'])){ echo ' selected'; }
                                    echo '>' . htmlspecialchars($mat['nome']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="formgroup">
                        <label for="min">Prezzo min</label>
                        <input id="min" type="number" step="1" min="0" name="min" value="<?php echo htmlspecialchars($minPrezzo); ?>">
                    </div>

                    <div class="formgroup">
                        <label for="max">Prezzo max</label>
                        <input id="max" type="number" step="1" min="0" name="max" value="<?php echo htmlspecialchars($maxPrezzo); ?>">
                    </div>

                    <div class="formgroup">
                        <label for="sort">Ordina per</label>
                        <select id="sort" name="sort">
                            <option value="recenti" <?php if($sort === 'recenti'){ echo 'selected'; } ?>>Piu recenti</option>
                            <option value="prezzo_asc" <?php if($sort === 'prezzo_asc'){ echo 'selected'; } ?>>Prezzo crescente</option>
                            <option value="prezzo_desc" <?php if($sort === 'prezzo_desc'){ echo 'selected'; } ?>>Prezzo decrescente</option>
                            <option value="like_desc" <?php if($sort === 'like_desc'){ echo 'selected'; } ?>>Piu apprezzate</option>
                        </select>
                    </div>
                </div>

                <div class="filteractions">
                    <button type="submit" class="searchbtn">Cerca dispense</button>
                    <a class="resetbtn" href="cercaDispense.php">Reset filtri</a>
                </div>
            </form>

            <div class="resultsheader">
                <h2>Risultati disponibili</h2>
            </div>

            <div class="dispenselist">
                <?php
                if($resultDispense && mysqli_num_rows($resultDispense) > 0){
                    while($disp = mysqli_fetch_assoc($resultDispense)){
                        $activeClass = '';
                        if(intval($disp['hasLiked']) > 0 && $_SESSION['is_logged'] == true){
                            $activeClass = 'active';
                        }

                        echo '<article class="dispensacard">';
                        echo '<div class="cardtop">';
                        echo '<img class="carddocicon" src="../../assets/document.png" alt="Documento">';
                        echo '<h3>' . htmlspecialchars($disp['titolo']) . '</h3>';
                        echo '<p class="description">' . htmlspecialchars($disp['descrizione']) . '</p>';
                        echo '</div>';

                        echo '<div class="details">';
                        echo '<p class="course">' . htmlspecialchars($disp['materia']) . '</p>';
                        echo '<p class="university">' . htmlspecialchars($disp['universita']) . '</p>';
                        echo '<p class="faculty">' . htmlspecialchars($disp['facolta']) . '</p>';
                        echo '</div>';

                        echo '<div class="cardbottom">';
                        echo '<div class="meta">';
                        echo '<p class="author">di ' . htmlspecialchars($disp['username']) . '</p>';
                        echo '</div>';

                        echo '<div class="actions">';
                        echo '<div class="leftactions">';
                        echo '<p class="price">' . htmlspecialchars($disp['prezzo']) . ' <img src="../../assets/unitoken.png" alt="UT"></p>';
                        echo '<span class="likecount">' . intval($disp['numLikes']) . '</span>';
                        echo '<a href="aggiuntaLikeDispensa.php?id_dispensa=' . $disp['id_dispensa'] . '&from=cerca"><button type="button" class="likebtn ' . $activeClass . '">';
                        echo '<img class="likeborder" src="../../assets/likeborder.png" alt="like">';
                        echo '<img class="like" src="../../assets/like.png" alt="like">';
                        echo '</button></a>';
                        echo '</div>';

                        if($_SESSION['is_logged'] == true){
                            echo '<form action="../acquistaDispense/elaborazioneAcquisto.php" method="POST">';
                            echo '<input type="hidden" name="id_dispensa" value="' . $disp['id_dispensa'] . '">';
                            echo '<input type="hidden" name="from" value="../funzioniUtenti/cercaDispense.php">';
                            echo '<button type="submit" class="buybtn">Compra</button>';
                            echo '</form>';
                        }else{
                            echo '<a href="../authentication/frontend/login.php"><button type="button" class="buybtn">Accedi per comprare</button></a>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</article>';
                    }
                }else{
                    echo '<div class="empty">';
                    echo '<h3>Nessuna dispensa trovata</h3>';
                    echo '<p>Prova a cambiare i filtri o la ricerca testuale.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <div class="popup-overlay" id="popupBuyError">
        <div class="popup-box">
            <h3>Attenzione</h3>
            <p id="popupBuyErrorText">Si è verificato un errore durante l'acquisto.</p>
            <button class="popup-btn" onclick="closePopup('popupBuyError')">Chiudi</button>
        </div>
    </div>

    <script>
    function openPopup(id){
        document.getElementById(id)?.classList.add('active');
    }
    function closePopup(id){
        document.getElementById(id)?.classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', function(){
        const navbar = document.querySelector('.nbcontainer');
        function ombraNavbar(){
            if(window.scrollY === 0){
                navbar.classList.add('no-shadow');
            }else{
                navbar.classList.remove('no-shadow');
            }
        }
        ombraNavbar();
        window.addEventListener('scroll', ombraNavbar);

        const buyError = "<?php echo htmlspecialchars($_GET['buy_error'] ?? ''); ?>";
        if(buyError){
            const msg = {
                own_dispensa: "Non puoi acquistare una dispensa caricata da te.",
                already_bought: "Hai già comprato questa dispensa.",
                insufficient_balance: "Saldo UniToken insufficiente per completare l'acquisto.",
                not_found: "Dispensa non trovata o non disponibile.",
                generic: "Errore durante l'acquisto. Riprova più tardi."
            };
            document.getElementById('popupBuyErrorText').textContent = msg[buyError] || msg.generic;
            openPopup('popupBuyError');
        }
    });
    </script>
</body>
</html>
