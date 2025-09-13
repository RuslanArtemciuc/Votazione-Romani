<?php
// Includo il file di configurazione che contiene la connessione al database
require_once "../config.php";

// Gli header consentono di inserire informazioni in più nella chiamata HTML tra client e server.
// In questo caso sto dicendo "Hey client, preparati! Il contenuto di questa pagina sarà JSON"
header('Content-Type: application/json');


// Controllo che sia stato passato l'id dell'opzione da eliminare tramite metodo (GET)
if (!isset($_GET['id'])) {
    // Se non è stato passato un id, mostro un messaggio di errore e termino lo script
    echo "Nessun ID fornito.";
    exit;
}

// Converto l'id in intero per sicurezza
$id = intval($_GET["id"]);


// Recupero tutte le opzioni (id dell'opzione e posti totali per ciascun'opzione) associate
// a questo sondaggio
$stmt = $pdo->prepare("SELECT id, posti FROM opzioni WHERE fk_sondaggio = ?");
$stmt->execute([$id]);

// Estraggo tutte le opzioni come array di array associativi
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparo la variabile che stamperò a schermo
$result = [];

// Per ogni opzione calcolo quante volte è stata scelta
foreach ($options as $option) {
    $optionId = $option['id']; // ID dell'ozione
    $optionPosti = $option['posti']; // Posti totali per turno per quell'opzione

    // Query per contare quante scelte sono state fatte per questa opzione (e ricavare quindi i posti occupati)
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM scelte
        WHERE fk_opzione = ?
        ");
    $countStmt->execute([$optionId]);

    // Estraggo il conteggio
    $count = $countStmt->fetch()['count'];

    // Aggiungo i dati di questa opzione all'array risultato
    $result[] = [
        'opzione_id' => $optionId, // id dell'opzione
        'scelte_count' => $count,  // numero di scelte fatte (posti occupati TOTALI,
                                   //                           non per turno, di quell'opzione)
        'posti' => $optionPosti    // numero di posti totali (PER TURNO)
    ];
}

// Restituisco il risultato come JSON
echo json_encode($result);