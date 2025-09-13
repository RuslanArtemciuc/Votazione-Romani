<?php

// Includo il file di configurazione che contiene la connessione al database e funzioni di utilità
include "../config.php";

// Controllo che l'utente sia autenticato come admin
check_admin();

// Controllo se la richiesta è di tipo POST (cioè se il form è stato inviato)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Recupero i dati inviati dal form tramite POST.
     * 
     * Si può osservare una sintassi strana:
     * 
     * $_POST['chiave'] ?? null;
     * 
     * Cosa vuol dire?
     * Questo tipo di sintassi si usa quando voglio provare ad assegnare un valore ad una variabile, e diciamo
     * che uso come "piano B" una variabile di riserva nel caso qualcosa vada storto con il valore iniziale.
     * Quindi:
     * "Hey server! Prova ad assegnare a $fk_sondaggio il valore $_POST['fk_sondaggio'], se qualcosa va storto
     * però (per esempio $_POST non ha la chiave 'fk_sondaggio'), usa il valore null (valore che indica "nulla",
     * avrei potuto usare anche una stringa vuota).
     */
    $fk_sondaggio = $_POST['fk_sondaggio'] ?? null; // ID del sondaggio a cui aggiungere l'opzione
    $titolo = $_POST['titolo'] ?? null; // Titolo dell'opzione
    $descrizione = $_POST['descrizione'] ?? null; // Descrizione dell'opzione
    $durata = "1"; // Durata dell'opzione (inizialmente settata a uno, può essere cambiata)
    $posti = $_POST['posti'] ?? null; // Numero di posti disponibili per l'opzione

    // Controllo che tutti i campi obbligatori siano presenti (se una variabile è null, questo if fallisce)
    if ($fk_sondaggio && $titolo && $descrizione && $durata && $posti) {

        // Per informazioni sul try and catch, consulta la parte di codice nella sezione user
        // (fuori dalla cartella admin)
        try {
            // Preparo la query per inserire una nuova opzione nella tabella "opzioni"
            $stmt = $pdo->prepare(
                "INSERT INTO opzioni (fk_sondaggio, titolo, descrizione, durata, posti)
                VALUES (:fk_sondaggio, :titolo, :descrizione, :durata, :posti)"
                );

            /**
             * Associo i parametri della query ai valori ricevuti dal form
             * 
             * Per farlo uso la funzione bindParam().
             * Questa funzione ci consente di introdurre delle variabili nella query riducendo il rischio di
             * inconsistenze (per esempio inserire una stringa dove ci andrebbe un intero) e riducendo il rischio
             * di SQL Injection (Ovvero l'inserimento di codice SQL da parte di una persona malevola, usato per
             * esempio per aggiungere dati, modificarli o eliminarli senza permesso).
             * 
             * Come parametri vuole
             * param: ovvero la parte di testo che si vuole sostituire con il valore
             * var: ovvero la variabile che si vuole utilizzare
             * type: il tipo di variabile (stringa, intro, float, eccetera)
             */
            $stmt->bindParam(':fk_sondaggio', $fk_sondaggio, PDO::PARAM_INT);
            $stmt->bindParam(':titolo', $titolo, PDO::PARAM_STR);
            $stmt->bindParam(':descrizione', $descrizione, PDO::PARAM_STR);
            $stmt->bindParam(':durata', $durata, PDO::PARAM_INT);
            $stmt->bindParam(':posti', $posti, PDO::PARAM_INT);

            // Eseguo la query
            $stmt->execute();

            // Dopo l'inserimento, reindirizzo l'utente alla pagina di gestione del sondaggio
            // passando l'id del sondaggio che si sta modificando come parametro
            header("Location: manage_as.php?id=" . $fk_sondaggio);

        } catch (PDOException $e) {
            // In caso di errore nella query, mostro il messaggio di errore
            echo "Errore: " . $e->getMessage();
        }
    } else {
        // Se manca qualche campo obbligatorio, mostro un messaggio di errore
        echo "Tutti i campi sono obbligatori.";
    }
} else {

    // Se si accede alla pagina con un metodo diverso da POST (come GET, OPTIONS, DELETE o PUT)
    // Stampo un messaggio di errore
    echo "Richiesta non valida. Solo POST!";
}
