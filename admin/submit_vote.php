<?php

// Includo il file di configurazione che contiene la connessione al database
include "../config.php";

// Se questo file non è stato chiamato tramite POST, invio uno script JavaScript da eseguire.
// Vedi get_sondaggio.php riga 17
if (!$_SERVER['REQUEST_METHOD'] === 'POST') {
    die("
        <script>
            alert('Accesso non autorizzato.');
            window.location.href = '../index.php';
        </script>
    ");
}

// Recupero e pulisco i dati inviati dal form
$id_sondaggio = $_POST['id_sondaggio'] ?? '';
$email = $_POST['email'] ?? '';
$email = strtolower(trim($email)); // Normalizzo l'email
$nome = $_POST['name'] ?? '';
$cognome = $_POST['surname'] ?? '';
$classe = $_POST['class'] ?? '';
$sezione = $_POST['section'] ?? '';
$fk_indirizzo = $_POST['address'] ?? '';
// Se non capisci la dicitura => ?? ''; guarda add_option.php riga 11.

// Validazione base dei dati obbligatori
if (empty($email) || empty($nome) || empty($cognome) || empty($classe) || empty($sezione) || empty($fk_indirizzo) || empty($id_sondaggio)) {
    // Se i dati non sono validi, stampo un piccolo script JavaScript da eseguire.
    // Vedi get_sondaggio.php riga 17
    die("
        <script>
            alert('Qualcosa è andato storto, riprova più tardi.');
            window.location.href = '../index.php';
        </script>
    ");
}

// Recupero i limiti di voto dal sondaggio
$stmt = $pdo->prepare("
                            SELECT min_voti, max_voti
                            FROM sondaggi
                            WHERE id = :id_sondaggio
                    ");

$stmt->execute(['id_sondaggio' => $id_sondaggio]);
$sondaggio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sondaggio) {
    // Se non trovo il sondaggio mostro un errore
    // Vedi get_sondaggio.php riga 17
    die("
        <script>
            alert('Sondaggio non trovato.');
            window.location.href = '../index.php';
        </script>
    ");
}

$min_voti = $sondaggio['min_voti'];
$max_voti = $sondaggio['max_voti'];

// Controllo che il numero di opzioni selezionate sia valido
if (
    !isset($_POST['vote']) ||
    !is_array($_POST['vote']) ||
    count($_POST['vote']) < $min_voti ||
    count($_POST['vote']) > $max_voti
) {
    die("
        <script>
            alert('Il numero di voti selezionati non è valido. Devi selezionare tra $min_voti e $max_voti opzioni.');
            window.location.href = '../vote.php?id=$id_sondaggio';
        </script>
    ");
}

// Controllo che la richiesta provenga dal form di voto
if (!isset($_POST['source']) || $_POST['source'] !== 'vote') {
    die("
        <script>
            alert('Accesso non autorizzato.');
            window.location.href = '../index.php';
        </script>
    ");
}

/**
 * Validazione mai istituzionale
 * 
 * Non è un controllo vero e proprio sull'esistenza della mail.
 * 
 * Quello che succede è:
 *  - Controllo con filter_var, funzione che riesce a controllare la validità di una variabile con certi filtri (Email, MAC Adress, IP Adress, e altro),
 *      l'email
 *  - Controllo infine se la stringa finisce per "poloromani.net"
 */
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    substr($email, -strlen('poloromani.net')) !== 'poloromani.net'
) {
    // Se non è valido: errore
    die("
        <script>
            alert('Email non valida o non appartenente al dominio poloromani.net. Usa una mail di istituto!');
            window.location.href = '../vote.php?id=$id_sondaggio';
        </script>
    ");
}

// Try and Catch, se non sai cos'è guarda pure config.php
try {
    // INIZIO TRANSAZIONE: tutte le operazioni devono andare a buon fine, altrimenti nessuna viene applicata
    $pdo->beginTransaction();
    // Qualcosa in più sulle transazioni in: delete_option.php riga 45

    // Controllo se lo studente esiste già (per evitare duplicati)
    $stmt = $pdo->prepare("
                                SELECT email
                                FROM studenti
                                WHERE email = :email
                        ");
    // In alternativa al bindParam(). vedi add_option.php riga 45
    $stmt->execute(['email' => $email]);

    // Se ottengo un numero di righe del recordset maggiore di 0 significa che lo studente esiste già
    if ($stmt->rowCount() > 0) {
        // Aggiorno i dati dello studente già esistente
        $stmt = $pdo->prepare("UPDATE studenti SET nome = :nome, cognome = :cognome, classe = :classe, sezione = :sezione, fk_indirizzo = :fk_indirizzo WHERE email = :email");
        $stmt->execute([
            'nome' => $nome,
            'cognome' => $cognome,
            'classe' => $classe,
            'sezione' => $sezione,
            'fk_indirizzo' => $fk_indirizzo,
            'email' => $email
        ]);
    } else {
        // Se lo studente non esiste, lo inserisco
        $stmt = $pdo->prepare("
                                INSERT INTO studenti (
                                        email,
                                        nome, 
                                        cognome, 
                                        classe, 
                                        sezione, 
                                        fk_indirizzo
                                    )
                                VALUES (
                                        :email, 
                                        :nome, 
                                        :cognome, 
                                        :classe, 
                                        :sezione, 
                                        :fk_indirizzo
                                    )
                            ");

        // In alternativa al bindParam(). vedi add_option.php riga 45
        $stmt->execute([
            'email' => $email,
            'nome' => $nome,
            'cognome' => $cognome,
            'classe' => $classe,
            'sezione' => $sezione,
            'fk_indirizzo' => $fk_indirizzo
        ]);
    }

    // Controllo se lo studente ha già votato per questo sondaggio
    $stmt = $pdo->prepare("
                                    SELECT fk_studente
                                    FROM scelte
                                    WHERE fk_studente=?
                                    AND fk_opzione IN (
                                                        SELECT id
                                                        FROM opzioni 
                                                        WHERE fk_sondaggio=?
                                                        )
                        ");

    $stmt->execute([$email, $id_sondaggio]);

    if ($stmt->rowCount() > 0) {
        // Se ha già votato in questo sondaggio, annullo la transazione e mostro un messaggio
        $pdo->rollBack();
        die("
            <script>
                alert('Hai già votato con la mail $email.');
                window.location.href = '../index.php';
            </script>
        ");
    } else {
        // Se lo studente non ha ancora votato, inserisco le nuove scelte (voti)
        if (isset($_POST['vote']) && is_array($_POST['vote'])) {
            foreach ($_POST['vote'] as $option_id) {
                $stmt = $pdo->prepare("INSERT INTO scelte (fk_studente, fk_opzione) VALUES (:email, :option_id)");
                $stmt->execute([
                    'email' => $email,
                    'option_id' => $option_id
                ]);
            }
        }

        // Successo !
        echo "
        <script>
            alert('Hai votato con successo con la mail $email! Verrai reindirizzato alla home.');
            window.location.href = '../index.php';
        </script>
        ";
    }

    // Tutto ok: confermo la transazione
    $pdo->commit();

} catch (Exception $e) {
    // In caso di errore, annullo tutte le modifiche fatte nella transazione
    $pdo->rollBack();
    // Mostro un messaggio
    die("
        <script>
            alert('Errore: " . htmlspecialchars($e->getMessage()) . "'); 
            window.location.href = '../index.php';
        </script>"
    );
}
