
<?php

// Includo il file di configurazione che contiene la connessione al database e funzioni di utilità
require_once "../config.php";

// Controllo che l'utente sia autenticato come admin
// Se la sessione non è valida, la funzione farà un redirect e terminerà lo script
check_admin();

// Preparo la query per selezionare tutti i sondaggi
$stmt = $pdo->query("SELECT * FROM sondaggi");

// Eseguo la query
$stmt->execute();

// Estraggo tutti i risultati come array di array associativi
// Ogni elemento dell'array rappresenta un sondaggio (una riga della tabella)
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap per aiutare con lo stile della pagina -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap è una libreria vastissima che può aiutare uno sviluppatore a maneggiare stili e non solo! Sono presenti
     molte funzionalità, come pop up, schede che si aprono, caroselli di immagini e tanto altro. Consulta: https://getbootstrap.com.
     Chiedi più informazioni al tuo professore! -->
    
    <!-- Foglio di stile CSS -->
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <div class="container mt-5">
        <!-- Titolo della dashboard -->
        <h1 class="mb-4">Admin Dashboard</h1>

        <!-- Pulsante per creare un nuovo sondaggio -->
        <a href="manage_as.php" class="btn btn-primary mb-3">Crea nuovo Sondaggio</a>

        <h2>Elenco Sondaggi Esistenti</h2>
        <div class="table-responsive">
            <!-- Tabella che mostra tutti i sondaggi -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Titolo</th>
                        <th>Descrizione</th>
                        <th>Data</th>
                        <th>Turni</th>
                        <th>Durata singolo turno</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($polls as $poll): ?>
                        <!-- Per capire meglio questo tipo di foreach, consulta gli script nella parte user (fuori dalla cartella admin) -->
                        <!--------------------->
                        <tr>
                            <!-- ID del sondaggio -->
                            <td><?= $poll['id']; ?></td>
                            <!-- Titolo del sondaggio, con escape per sicurezza -->
                            <!-- Per capire cosa fa htmlspecialchars, consulta gli script nella parte user (fuori dalla cartella admin) -->
                            <td><?= htmlspecialchars($poll['titolo']); ?></td>
                            <!-- Descrizione del sondaggio -->
                            <!-- Qui la funzione nl2br (New Line To Break) converte il carattere "A capo" (\n) nella loro versione HTML <br>-->
                            <td><?= nl2br(htmlspecialchars($poll['descrizione'])); ?></td>
                            <!-- Data del sondaggio -->
                            <td><?= htmlspecialchars($poll['data']); ?></td>
                            <!-- Numero di turni previsti -->
                            <td><?= htmlspecialchars($poll['turni']); ?></td>
                            <!-- Durata di ogni turno, in minuti -->
                            <td><?= htmlspecialchars($poll['durata_turno']); ?>min</td>
                            <td>
                                <!-- Pulsante per modificare il sondaggio (apre la pagina di modifica con l'id) -->
                                <a href="manage_as.php?id=<?= $poll['id']; ?>" class="btn btn-sm btn-warning" title="Modifica sondaggio">
                                    ✏️
                                </a>
                                <!-- Pulsante per eliminare il sondaggio, con conferma tramite prompt, ovvero una specie di pop up inclusa in JavaScript -->
                                <a href="delete_poll.php?id=<?= $poll['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDeletion('<?php echo addslashes(strtoupper($poll['titolo'])); ?>')" title="Elimina sondaggio">
                                    ❌
                                </a>
                                <!-- Script JavaScript per la conferma di eliminazione -->
                                <script>
                                    /**
                                     * Funzione che chiede all'utente di digitare il nome del sondaggio in maiuscolo
                                     * per confermare l'eliminazione. Restituisce true solo se il testo inserito corrisponde.
                                     */
                                    function confirmDeletion(pollTitle) {
                                        const userInput = prompt(`Sicuro di voler cancellare il sondaggio e di conseguenza relativi dati?\n Se sì digita il nome del sondaggio in maiuscolo (${pollTitle})`);
                                        return userInput === pollTitle;
                                    }
                                </script>
                                <!-- Pulsante per scaricare i dati del sondaggio (esporta risultati come CSV, consulta get_sondaggio.php) -->
                                <a href="get_sondaggio.php?id=<?php echo $poll['id']; ?>" class="btn btn-sm btn-success" title="Scarica risultati">
                                    ⬇️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Per capire meglio cosa vuol dire "endforeach", consulta gli script nella parte user (fuori dalla cartella admin) -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- File importato per far funzionare correttamente certe componenti della parte bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>