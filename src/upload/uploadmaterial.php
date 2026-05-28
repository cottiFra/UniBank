<?php
session_start();
require_once __DIR__ . '/../../config.php';

$conn = db_connect();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>UniBank - Upload</title>
    <link rel="stylesheet" href="uploadmaterial.css?=<?php echo time();?>">
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
                            <a href="authentication/frontend/login.php">
                                <button class="loginbtn">Login</button>
                            </a>
                        </li>
                    <?php } ?>
                    <?php
                    if(!isset($_SESSION['is_logged']) || $_SESSION['is_logged'] != true){?>
                        <li>
                            <a href="authentication/frontend/signup.php">
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
            <h1 style="color: var(--color-blue-background)">Carica Dispensa</h1>
            <div class="content">
                <div class="uploadbox">
                    <form action="funzioniUpload/elaborazioneUpload.php" method="post" enctype="multipart/form-data">
                        <div class="formgrid">
                            <div class="inputbox fullwidth">
                                <label for="titolo">Titolo della dispensa</label>
                                <input type="text" name="titolo" placeholder="Titolo..." required>
                            </div>
                            <div class="inputbox fullwidth">
                                <label for="descrizione">Descrizione</label>
                                <textarea class="description" name="descrizione" cols="30" rows="8" placeholder="Descrivi il contenuto..." required></textarea>
                            </div>
                            <div class="inputbox">
                                <label for="corso">Corso/Materia</label>
                                <select name="corso" id="corso" required>
                                    <option value="">Seleziona materia</option>
                                    <?php
                                        $id_facolta_utente = $_SESSION['id_facolta'];

                                        $query = "
                                            SELECT m.id_materia, m.nome
                                            FROM materia m, materiaperfacolta mpf
                                            WHERE m.id_materia = mpf.id_materia
                                            AND mpf.id_facolta = '$id_facolta_utente'
                                            ORDER BY m.nome ASC
                                        ";
                                        $ris = mysqli_query($conn, $query);
                                        while($record = mysqli_fetch_assoc($ris)){
                                            echo '<option value="'.$record['id_materia'].'">'.$record['nome'].'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="inputbox">
                                <label for="prezzo">Prezzo ( <img style="width: 20px" src="../../assets/unitoken.png" alt="UT">)</label>
                                <input type="number" name="prezzo" placeholder="5" min="0" required>
                            </div>
                            <div class="inputbox fullwidth">
                                <label for="file">File (PDF, DOCX, ODT, RTF)</label>
                                <div class="uploadwrapper">
                                    <input type="file" name="file" id="file" accept=".pdf,.docx,.odt,.rtf" required hidden>
                                    <label for="file" class="uploadlabel">
                                        <div class="uploadcontent">
                                            <div style="font-size: 30px; color: #ccc;" id="uploadicon">⬆</div>
                                            <p id="uploadtext">Clicca per caricare o trascina il file qui</p>
                                            <span>PDF, DOCX, ODT, RTF - max 50MB</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="inputbox fullwidth">
                                <button class="publishbtn" type="submit">Pubblica dispensa</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="infobox">
                    <h4 style="display: flex; justify-content: flex-start; align-items: center; gap: 10px; color: var(--color-yellow-primary); margin-bottom: 15px; font-size: 18px;"><img style="width: 20px" src="../../assets/info.png" alt="i"> Come funzionano gli UniToken?</h4>
                    <p class="infop">Quando pubblichi una dispensa, imposti un prezzo in UniToken. Ogni volta che un utente acquista la tua dispensa, guadagni quei token.</p>
                    <div class="suggestedpricebox">
                        <p>Prezzo consigliato</p>
                        <h2 style="color: var(--color-yellow-primary)">5-15 token</h2>
                        <p>Le dispense complete vendono di più</p>
                    </div>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px;">
                        <li class="li"">
                            <span style="color: var(--color-yellow-primary)">•</span>
                            <p class="infop">Assicurati che il PDF sia leggibile</p>
                        </li>
                        <li class="li">
                            <span style="color: var(--color-yellow-primary)">•</span>
                            <p class="infop">Scrivi una descrizione dettagliata</p>
                        </li>
                        <li class="li"">
                            <span style="color: var(--color-yellow-primary)">•</span>
                            <p class="infop">Pubblica solo materiale originale</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script>
        const fileInput = document.getElementById('file');
        const fileUploadText = document.getElementById('uploadtext');
        const uploadIcon = document.getElementById('uploadicon');

        fileInput.addEventListener('change', function(){
            if (this.files && this.files.length > 0) {
                fileUploadText.textContent = "File selezionato: " + this.files[0].name;
                fileUploadText.style.color = "var(--color-blue-background)";
                fileUploadText.style.fontWeight = "600";
            } else {
                fileUploadText.textContent = "Clicca per caricare o trascina il file qui";
                fileUploadText.style.color = "";
                fileUploadText.style.fontWeight = "";
            }
        });

        const label = document.querySelector('.uploadlabel');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            label.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e){
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            label.addEventListener(eventName, () => label.classList.add('highlight'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            label.addEventListener(eventName, () => label.classList.remove('highlight'), false);
        });

        label.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;

            const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text', 'application/rtf', 'text/rtf'];
            const allowedExtensions = ['.pdf', '.docx', '.odt', '.rtf'];

            if(files.length > 0){
                const file = files[0];
                const fileName = file.name.toLowerCase();
                const fileType = file.type;
                
                const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
                const hasValidType = allowedTypes.includes(fileType) || fileName.endsWith('.docx') || fileName.endsWith('.odt') || fileName.endsWith('.rtf');
                
                if(hasValidExtension || hasValidType){
                    fileInput.files = files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                } else {
                    alert("Formato non supportato. Per favore carica solo file PDF, DOCX, ODT o RTF.");
                }
            }
        }, false);
    </script>
</body>
</html>
