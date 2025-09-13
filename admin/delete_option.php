<?php
// Script per eliminare un opzione da un sondaggio

// Includo il file di configurazione che contiene la connessione al database e funzioni di utilità
require_once "../config.php";

// Controllo che l'utente sia autenticato come admin
check_admin();


// Controllo che sia stato passato l'id dell'opzione da eliminare tramite metodo (GET)
if (!isset($_GET['id'])) {
    // Se non è stato passato un id, mostro un messaggio di errore e termino lo script
    echo "Nessun ID fornito.";
    exit;
}


// Converto l'id in intero per sicurezza
$id = intval($_GET['id']);

// Recupero l'id del sondaggio a cui appartiene l'opzione, prima di eliminarla (serve per il redirect finale)
$stmt = $pdo->prepare("SELECT fk_sondaggio FROM opzioni WHERE id = :id");

// Per vedere cos'è bindValue, guarda addOption.php riga 45
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

// Eseguo la query
$stmt->execute();

/**
 * Per ricavare l'id del sondaggio, posso usare la funzione fetchColumn().
 * Con questa posso selezionare il valore di una colonna della prima riga del recordset.
 * 
 * Accedo alle colonne come se fosse un array, quindi:
 * 0 => Prima colonna
 * 1 => Seconda colonna
 * 2 => Terza colonna
 * n => Nsima colonna + 1
 * 
 * Il valore di default è 0, quindi prende il valore della prima colonna del primo record del recordset
 */
$survey_id = $stmt->fetchColumn();

/**
 * Avvio una transazione per garantire che tutte le operazioni vadano a buon fine o nessuna venga applicata
 * (E' argomento di quinta, non ti preoccupare se non sai cosa vuol dire)
 * 
 * Cos'è una transazione?
 * Un sistema di sicurezza che hanno molti database (MYSQL, POSTGRESQL, MARIADB e tanti altri)
 * 
 * Consente di salvarsi uno snapshot (come una foto) dello stato attuale del database, e registra
 * ogni singola operazione eseguita tramite codice (SQL ovviamente).
 * 
 * A cosa serve concretamente, e come funziona?
 * Se anche solo una singola operazione genera un qualche tipo di errore mentre si sta eseguendo una transazione,
 * è possibile eseguire il "rollBack", ovvero tornare allo stato del database prima della transazione
 * (quindi a quella specie di foto che si è salvato)
 * 
 * E' fondamentale in molti settori, come banche per esempio.
 * Chiedi più informazioni al tuo professore!
 */
$pdo->beginTransaction();

try {
    // Elimino prima tutte le scelte fatte dagli studenti collegate a questa opzione (integrità referenziale)
    $stmt = $pdo->prepare("DELETE FROM scelte WHERE fk_opzione = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Poi elimino l'opzione vera e propria
    $stmt = $pdo->prepare("DELETE FROM opzioni WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Se tutto è andato bene, confermo la transazione
    $pdo->commit();

    // Redirect alla pagina di gestione del sondaggio
    header("Location: manage_as.php?id=$survey_id");
    exit;
} catch (PDOException $e) {
    // In caso di errore, annullo tutte le modifiche fatte nella transazione con l'operazione di rollBack
    $pdo->rollBack();
    echo "Errore durante l'eliminazione dell'opzione: " . $e->getMessage();
}
