<?php
session_start();
require_once __DIR__ . '/../../config.php';
$conn = db_connect();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../assets/logowhitebg.png">
    <title>UniBank - Gestione Messaggi</title>
    <link rel="stylesheet" href="admin.css?=<?php echo time();?>">
    <link rel="stylesheet" href="../variables.css?=<?php echo time();?>">
    <style>
        .message-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .message-modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 80%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .message-modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .message-modal-close:hover {
            color: black;
        }

        .message-detail {
            margin: 15px 0;
        }

        .message-detail-label {
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .message-detail-value {
            background-color: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            color: #333;
            word-break: break-word;
        }

        .motivo-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .motivo-badge.problema_tecnico { background-color: #fee2e2; color: #991b1b; }
        .motivo-badge.segnalazione { background-color: #fef3c7; color: #92400e; }
        .motivo-badge.pagamenti { background-color: #dbeafe; color: #0c4a6e; }
        .motivo-badge.account { background-color: #d1fae5; color: #065f46; }
        .motivo-badge.suggerimento { background-color: #f3e8ff; color: #6b21a8; }
        .motivo-badge.altro { background-color: #e0e7ff; color: #3730a3; }
    </style>
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
                        <a href="../authentication/backend/logout.php"><button class="logoutbtn">Logout</button></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<main class="admin-page">
    <nav class="admin-tabs">
        <div class="tabs-container">
            <a href="adminpanoramica.php" class="tab-item">Panoramica</a>
            <a href="adminusers.php" class="tab-item">Gestione utenti</a>
            <a href="adminmateriali.php" class="tab-item">Gestione materiali</a>
            <a href="adminstoricoacquisti.php" class="tab-item">Storico acquisti</a>
            <a href="adminmessaggi.php" class="tab-item active">Messaggi di contatto</a>
        </div>
    </nav>

    <div class="admin-container">
        <section class="admin-header-actions">
            <h4>Messaggi di Contatto</h4>
            <form method="GET" action="adminmessaggi.php" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; margin: 0;">
                <div class="search-bar" style="margin: 0;">
                    <?php 
                    $search_val = "";
                    if(isset($_GET['search'])){
                        $search_val = $_GET['search'];
                    }
                    ?>
                    <input type="text" name="search" placeholder="Cerca per nome, email o oggetto" value="<?php echo htmlspecialchars($search_val); ?>">
                    <button type="submit" class="search-btn">Cerca</button>
                </div>
                <div class="sort-bar" style="display: flex; gap: 10px; align-items: center;">
                    <?php
                    $current_sort = 'data_desc';
                    if(isset($_GET['sort'])){
                        $current_sort = $_GET['sort'];
                    }
                    ?>
                    <select name="sort" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 14px; background-color: #f9fafb; outline: none; color: #4b5563; cursor: pointer;">
                        <option value="data_desc" <?php if($current_sort == 'data_desc') echo 'selected'; ?>>Più recenti</option>
                        <option value="data_asc" <?php if($current_sort == 'data_asc') echo 'selected'; ?>>Meno recenti</option>
                    </select>
                    <button type="submit" class="search-btn">Applica Filtri</button>
                </div>
            </form>
        </section>

        <section class="admin-content-full">
            <div class="admin-box no-padding">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>DATA</th>
                            <th>NOME</th>
                            <th>EMAIL</th>
                            <th>MOTIVO</th>
                            <th>OGGETTO</th>
                            <th>IP</th>
                            <th>AZIONI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $log_file = __DIR__ . '/../../src/contactus/funzioniContact/messaggi/contatti.log';
                            $messages = [];

                            if(file_exists($log_file)){
                                $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                                foreach($lines as $line){
                                    $data = json_decode($line, true);
                                    if($data){
                                        $messages[] = $data;
                                    }
                                }
                            }
                            if(isset($_GET['search']) && $_GET['search'] != ''){
                                $search = strtolower(trim($_GET['search']));
                                $filtered = [];
                                foreach($messages as $msg){
                                    if(strpos(strtolower($msg['nome']), $search) !== false ||
                                       strpos(strtolower($msg['email']), $search) !== false ||
                                       strpos(strtolower($msg['oggetto']), $search) !== false){
                                        $filtered[] = $msg;
                                    }
                                }
                                $messages = $filtered;
                            }

                            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'data_desc';
                            if($sort == 'data_asc'){
                                // Ordina dal meno recente al più recente
                                for($i = 0; $i < count($messages); $i++){
                                    for($j = $i + 1; $j < count($messages); $j++){
                                        if(strtotime($messages[$i]['timestamp']) > strtotime($messages[$j]['timestamp'])){
                                            $temp = $messages[$i];
                                            $messages[$i] = $messages[$j];
                                            $messages[$j] = $temp;
                                        }
                                    }
                                }
                            }else{
                                for($i = 0; $i < count($messages); $i++){
                                    for($j = $i + 1; $j < count($messages); $j++){
                                        if(strtotime($messages[$i]['timestamp']) < strtotime($messages[$j]['timestamp'])){
                                            $temp = $messages[$i];
                                            $messages[$i] = $messages[$j];
                                            $messages[$j] = $temp;
                                        }
                                    }
                                }
                            }

                            $motivi_map = [
                                'problema_tecnico' => 'Problema Tecnico',
                                'segnalazione' => 'Segnalazione',
                                'pagamenti' => 'Pagamenti',
                                'account' => 'Account',
                                'suggerimento' => 'Suggerimento',
                                'altro' => 'Altro'
                            ];

                            if(empty($messages)){
                                echo '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #999;">Nessun messaggio trovato</td></tr>';
                            }else{
                                foreach($messages as $index => $msg){
                                    $motivo_label = isset($motivi_map[$msg['motivo']]) ? $motivi_map[$msg['motivo']] : $msg['motivo'];
                                    
                                    echo '<tr>';
                                    echo    '<td>'.htmlspecialchars($msg['timestamp']).'</td>';
                                    echo    '<td>'.htmlspecialchars($msg['nome']).'</td>';
                                    echo    '<td>'.htmlspecialchars($msg['email']).'</td>';
                                    echo    '<td><span class="motivo-badge '.$msg['motivo'].'">'.$motivo_label.'</span></td>';
                                    echo    '<td>'.htmlspecialchars(substr($msg['oggetto'], 0, 30)).'...'.'</td>';
                                    echo    '<td>'.htmlspecialchars($msg['ip']).'</td>';
                                    echo    '<td><button class="action-btn view-btn" onclick="viewMessage('.$index.')">Visualizza</button></td>';
                                    echo '</tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div id="messageModal" class="message-modal">
    <div class="message-modal-content">
        <span class="message-modal-close" onclick="closeMessage()">&times;</span>
        <h2 style="color: #1f2937; margin-bottom: 20px;">Dettagli Messaggio</h2>
        
        <div class="message-detail">
            <div class="message-detail-label">Data e Ora</div>
            <div class="message-detail-value" id="modal-timestamp"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">Nome Mittente</div>
            <div class="message-detail-value" id="modal-nome"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">Email Mittente</div>
            <div class="message-detail-value" id="modal-email"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">Indirizzo IP</div>
            <div class="message-detail-value" id="modal-ip"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">ID Utente</div>
            <div class="message-detail-value" id="modal-id-utente"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">Motivo del Contatto</div>
            <div class="message-detail-value" id="modal-motivo"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">Oggetto</div>
            <div class="message-detail-value" id="modal-oggetto"></div>
        </div>

        <div class="message-detail">
            <div class="message-detail-label">Messaggio</div>
            <div class="message-detail-value" id="modal-messaggio" style="white-space: pre-wrap; line-height: 1.6;"></div>
        </div>
    </div>
</div>

<script>
    const messagesData = <?php echo json_encode($messages); ?>;
    
    function viewMessage(index) {
        const msg = messagesData[index];
        if(!msg) return;
        
        const motivi_map = {
            'problema_tecnico': 'Problema Tecnico',
            'segnalazione': 'Segnalazione',
            'pagamenti': 'Pagamenti',
            'account': 'Account',
            'suggerimento': 'Suggerimento',
            'altro': 'Altro'
        };
        
        document.getElementById('modal-timestamp').textContent = msg.timestamp;
        document.getElementById('modal-nome').textContent = msg.nome;
        document.getElementById('modal-email').textContent = msg.email;
        document.getElementById('modal-ip').textContent = msg.ip;
        document.getElementById('modal-id-utente').textContent = msg.id_utente || 'Utente non registrato';
        document.getElementById('modal-motivo').textContent = motivi_map[msg.motivo] || msg.motivo;
        document.getElementById('modal-oggetto').textContent = msg.oggetto;
        document.getElementById('modal-messaggio').textContent = msg.messaggio;
        
        document.getElementById('messageModal').style.display = 'block';
    }
    
    function closeMessage() {
        document.getElementById('messageModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('messageModal');
        if(event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
</body>
</html>
