<?php
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
$id = intval($_GET["id"]);

// Avvio una transazione, guarda delete_option.php per maggiori informazioni (riga 45)
$pdo->beginTransaction();
try {
    // Elimino prima tutte le scelte collegate alle opzioni di questo sondaggio
    $stmt = $pdo->prepare("
        DELETE
        FROM scelte
        WHERE fk_opzione IN (
                            SELECT id
                            FROM opzioni
                            WHERE fk_sondaggio=?
        )
    ");
    $stmt->execute([$id]);
    // Per semplicità non uso bindParam


    // Elimino tutte le opzioni collegate al sondaggio
    $stmt = $pdo->prepare("
        DELETE
        FROM opzioni
        WHERE fk_sondaggio=?
    ");
    $stmt->execute([$id]);

    // Infine elimino il sondaggio vero e proprio
    $stmt = $pdo->prepare("
        DELETE
        FROM sondaggi
        WHERE id=?
    ");
    $stmt->execute([$id]);

    // Se tutto è andato bene, confermo la transazione
    $pdo->commit();
} catch (Exception $e) {
    // In caso di errore, annullo tutte le modifiche fatte nella transazione
    $pdo->rollBack();
    echo "Errore durante l'eliminazione del sondaggio: " . $e->getMessage();
}
