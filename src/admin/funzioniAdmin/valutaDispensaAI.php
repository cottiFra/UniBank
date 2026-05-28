<?php
session_start();
require_once __DIR__ . '/../../../config.php';
$conn = db_connect();

if(!isset($_SESSION['user_id']) || $_SESSION['ruolo'] != 1){
    die("Accesso negato.");
}

$idDispensa = intval($_REQUEST['id_dispensa']);

$query = "
    SELECT d.percorso_file, d.titolo, m.nome as materia, f.nome as facolta
    FROM dispense d, materiaperfacolta mpf, materia m, utenti u, facolta f
    WHERE d.id_materiaperfacolta = mpf.id_materiaperfacolta
    AND mpf.id_materia = m.id_materia
    AND d.id_utente = u.id_utente
    AND u.id_facolta = f.id_facolta
    AND d.id_dispensa = {$idDispensa}
";

$ris = mysqli_query($conn, $query);
if(mysqli_num_rows($ris) == 0){
    die("Dispensa non trovata o mancano i collegamenti con la materia.");
}
$dispensa = mysqli_fetch_assoc($ris);

$percorsoFisico = __DIR__ . '/../../../' . $dispensa['percorso_file'];

if(!file_exists($percorsoFisico)){
    die("Il file fisico della dispensa non esiste: " . $percorsoFisico);
}

// lettura e conversione in Base64
$fileContent = file_get_contents($percorsoFisico);
$base64File = base64_encode($fileContent);
//otteniamo tipo mime, se fallisce usiamo application/pdf
$mimeType = mime_content_type($percorsoFisico) ?: 'application/pdf';

$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
if(empty($apiKey)){
    die("Per favore inserisci una chiave API di Gemini valida in config.php");
}
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
$prompt = "Sei un revisore universitario molto rigoroso. Devi analizzare questo documento caricato da uno studente della facoltà di '{$dispensa['facolta']}' per il corso di '{$dispensa['materia']}'. 
Regole:
1. Verifica che NON sia spam o materiale pubblicitario.
2. Verifica che NON contenga contenuti offensivi o inappropriati.
3. Valuta quanto il materiale è attinente e utile per il corso indicato nella rispettiva facoltà.
4.Se la dispensa non c'entra niente con il corso '{$dispensa['materia']}' dai direttamente un voto minore a 4.5 anche se è attinente alla facolta, però avvisa l'admin.
5.Se la dispensa non c'entra niente con la facoltà di '{$dispensa['facolta']}' dai direttamente un voto minore a 4.5 anche se è attinente al corso, però avvisa l'admin.
Restituisci SOLO un JSON valido con questa struttura esatta:
{
  \"punteggio\": [un numero da 0 a 10],
  \"motivazione\": \"[una breve spiegazione del perché di questo voto]\",
  \"is_spam\": [true o false],
  \"is_offensivo\": [true o false]
}";

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt],
                [
                    "inline_data" => [
                        "mime_type" => $mimeType,
                        "data" => $base64File
                    ]
                ]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// Ignora controlli SSL in localhost se necessario
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpCode != 200){
    die("Errore dall'API Gemini. Codice: $httpCode. Risposta: " . $response);
}

$jsonResponse = json_decode($response, true);
if(!isset($jsonResponse['candidates'][0]['content']['parts'][0]['text'])){
    die("Formato risposta AI non valido. Risposta: " . $response);
}

$aiText = $jsonResponse['candidates'][0]['content']['parts'][0]['text'];

// pulisci il markdown di code block se presente
$aiText = preg_replace('/```json\s*/', '', $aiText);
$aiText = preg_replace('/```/', '', $aiText);
$aiData = json_decode(trim($aiText), true);

if(!$aiData || !isset($aiData['punteggio'])){
    die("L'AI non ha restituito un JSON valido: " . htmlspecialchars($aiText));
}

$punteggio = floatval($aiData['punteggio']);
$motivazione = htmlspecialchars($aiData['motivazione']);
$is_spam = isset($aiData['is_spam']) && $aiData['is_spam'] ? true : false;
$is_offensivo = isset($aiData['is_offensivo']) && $aiData['is_offensivo'] ? true : false;

$azioneIntraprese = "";

if($is_spam || $is_offensivo || $punteggio < 4.5){
    #unlink($percorsoFisico); senno per le prove ogni volta bisogna uploadare i file dopo che vengono eliminati
    $delQuery = "DELETE FROM dispense WHERE id_dispensa = {$idDispensa}";
    mysqli_query($conn, $delQuery);
    $azioneIntraprese = "Rifiutata ed eliminata. Punteggio troppo basso o contenuto inappropriato.";
    $colore = "red";
}else if($punteggio >= 7.5){
    $upQuery = "UPDATE dispense SET approvata = 1 WHERE id_dispensa = {$idDispensa}";
    mysqli_query($conn, $upQuery);
    $azioneIntraprese = "Approvata automaticamente! Il materiale è considerato di alta qualità.";
    $colore = "green";
}else{
    $azioneIntraprese = "Richiesta revisione manuale. Il materiale è dubbio, decidi tu se approvarlo.";
    $colore = "orange";
}

?>

<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Risultato Valutazione AI</title>
    <link rel="stylesheet" href="../admin.css?=<?php echo time();?>">
    <link rel="stylesheet" href="../../variables.css?=<?php echo time();?>">
    <style>
        .ai-result-box {
            background-color: var(--grey3);
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            color: var(--white);
        }
        .score-display {
            font-size: 3rem;
            font-weight: 700;
        }
        .score-red { color: #ff4d4d; }
        .score-orange { color: #ffa64d; }
        .score-green { color: #4dff4d; }
        .action-taken {
            margin-top: 20px;
            padding: 15px;
            background-color: rgba(255,255,255,0.05);
            border-radius: 8px;
            border-left: 4px solid var(--white);
        }
        .action-red { border-color: #ff4d4d; }
        .action-orange { border-color: #ffa64d; }
        .action-green { border-color: #4dff4d; }
    </style>
</head>
<body>
    <div class="admin-page">
        <div class="admin-container" style="max-width: 800px; margin: 50px auto;">
            <h2 style="color:var(--white);">Risultato Analisi Intelligenza Artificiale</h2>
            
            <div class="ai-result-box">
                <h3>Dispensa: <?php echo htmlspecialchars($dispensa['titolo']); ?></h3>
                <p>Materia: <?php echo htmlspecialchars($dispensa['materia']); ?></p>
                
                <hr style="border: 1px solid var(--grey2); margin: 20px 0;">
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p><strong>Punteggio Assegnato:</strong></p>
                        <span class="score-display score-<?php echo $colore; ?>">
                            <?php echo $punteggio; ?>/10
                        </span>
                    </div>
                    <div>
                        <p>Spam: <strong><?php echo $is_spam ? 'SÌ' : 'NO'; ?></strong></p>
                        <p>Offensivo: <strong><?php echo $is_offensivo ? 'SÌ' : 'NO'; ?></strong></p>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <p><strong>Motivazione dell'AI:</strong></p>
                    <p style="background: var(--grey2); padding: 15px; border-radius: 8px;">
                        <?php echo $motivazione; ?>
                    </p>
                </div>

                <div class="action-taken action-<?php echo $colore; ?>">
                    <h4>Azione Eseguita:</h4>
                    <p><?php echo $azioneIntraprese; ?></p>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <a href="../adminmateriali.php">
                        <button class="view-btn" style="padding: 10px 20px; cursor: pointer;">Torna ai materiali</button>
                    </a>
                    <?php if($colore === 'orange') { ?>
                        <a href="../../downloadDispense/downloadDispensa.php?id_dispensa=<?php echo $idDispensa; ?>">
                            <button class="view-btn" style="padding: 10px 20px; cursor: pointer; background-color: #2196F3; border-color: #2196F3;">Vedi Dispensa</button>
                        </a>
                        <a href="approvaDispensa.php?id_dispensa=<?php echo $idDispensa; ?>">
                            <button class="approve-btn" style="padding: 10px 20px; cursor: pointer;">Approva Manualmente</button>
                        </a>
                        <a href="eliminaDispensa.php?id_dispensa=<?php echo $idDispensa; ?>">
                            <button class="delete-btn-table" style="padding: 10px 20px; cursor: pointer;">Elimina Definitivamente</button>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
