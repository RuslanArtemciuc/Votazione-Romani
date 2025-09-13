<?php

/**
 * Questo scirpt serve a scaricare un file zip contenente 2 file CSV
 * 
 * CSV = Comma Separated Values, Valori separati da virgola.
 * Sono file che si possono importare su foglio di calcono (Excell o Google Spreadsheet che sia).
 */

// Includo il file di configurazione che contiene la connessione al database
require_once "../config.php";

// Controllo che l'utente sia autenticato come admin
check_admin();
// Controllo che sia stato passato l'id dell'opzione da eliminare tramite metodo (GET)
if (!isset($_GET['id'])) {
    /**
     * Se non è stato passato un id, stavolta non mostro solo un messaggio..
     * 
     * Praticamente stampo del codice HTML che però contiene una semplice cosa, uno script JavaScript che
     * fa apparire un pop up con l'errore, e reindirizza il client alla dashboard
     * 
     * E' un esperimento che ho provato a fare qui per la prima volta e da qualche altra parte in giro per l'app,
     * essendo però un po' difficile da leggere come codice non l'ho usato dappertutto 
     */
    die("
    <script>
        alert('ID del sondaggio non specificato.');
        window.location.href = 'dashboard.php';
    </script>
    ");
}

// Converto l'id in intero per sicurezza
$id = intval($_GET["id"]);

// Recupero tutte le informazioni del sondaggio (serve per titolo, turni, ecc.)
$stmt = $pdo->prepare("SELECT * FROM `sondaggi` WHERE id = ?");
$stmt->execute([$id]);
$sondaggio = $stmt->fetch(PDO::FETCH_ASSOC); // Non fetchAll perché la query ritorna solo un valore



// --- PRIMO CSV: riepilogo opzioni ---
// Query per ottenere tutte le opzioni del sondaggio, con conteggio delle scelte e calcolo posti totali
$stmt = $pdo->prepare("
        SELECT  titolo, 
                descrizione, 
                durata, 
                posti, 
                COUNT(fk_opzione) as occupati, 
                posti*? as posti_totali 
        FROM `opzioni`
            LEFT JOIN scelte
            ON fk_opzione = opzioni.id
        WHERE fk_sondaggio = ?
        GROUP BY id, titolo, descrizione, durata, posti
        ");
/**
 * Per ogni opzione, seleziono il numero di scelte di quell'opzione (più info di base come titolo eccetera).
 * faccio però una left join per stmapare a schermo anche le opzioni che non sono mai state scelte (e che
 * altrimenti non troverebbero mai una corrispondenza con "... ON fk_opzione = opzioni.id ...")
 */

$stmt->execute([$sondaggio["turni"], $id]);
$opzioniData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se opzioniData è una variabile vuota mi fermo e uso il codice HTML (visto in precedenza)
if (empty($opzioniData)) {
    die("
    <script>
        alert('Il sondaggio che stai provando a scaricare non ha dati.');
        window.location.href = 'dashboard.php';
    </script>
    ");
}
/**
 * Hmmmm... Come faccio a generare non un file CSV ma 2, e poi inserirli all'interno di un file zip?
 * 
 * E' un po' complicato...
 * 
 * Praticamente devo dire al server:
 * "Hey server, stampa dei dati, e preparali come file, MA NON INVIARLI SUBITO AL CLIENT,
 * tienili in un posto sicuro nella tua memoria RAM e quando te lo dico io, salva quel posticino sicuro
 * dentro una variabile."
 * 
 * Faccio questo per entrambi i file CSV, e infine:
 * "Ok, adesso abbiamo la variabile csv1Content, e csv2Content che contengono il contenuto (che sarebbe
 * dovuto arrivare subito al client) dei file CSV. Prendili e spiaccicali dentro un file zip che ho creato
 * temporaneamente sul tuo disco. Dopo che li hai messi lì invia quel file zip al client."
 * 
 * Poteva essere molto più semplice di così?
 * Sì.
 * 
 * Potevo semplicemente mettere due tasti con "Scarica primo file, Scarica secondo file"?
 * Sì.
 * 
 * Mi sono complicato la vita?
 * Sì.
 * 
 * Però ho imparato tante nuove cose e mi è piaciuto.
 * 
 * Ma concretamente cosa succede?
 * ob_start() :     Tutte le cose dopo questa funzione che normalmente dovrebbero andare al client
 *                  (i file CSV), non vengono spediti ma sono intrappolati temporaneamente dentro la
 *                  memoria RAM del server.
 * 
 * fopen() :        mi consente di avviare uno stream di dati (argomento di terza superiore, se non sapete di
 *                  cosa parlo, chiedete al vostro professore...), in questo caso di tipo "php://output"
 *                  (output verso il client che però intrappolo all'interno della memoria RAM), "w" in
 *                  modalità scrittura.
 * 
 * fputcsv() :      funzione che mi consente facilmente di creare un file CSV, devo specificare lo stream di dati
 *                  nel quale inviare il file (stream che dovrebbe andare verso il client ma che intrappolo) e
 *                  le varie colonne che voglio che il file abbia.
 *                  Ogni volta che uso la funzione è come se andassi a capo, quindi:
 *                   - Come prima riga voglio vedere il titolo delle colonne
 *                   - Poi itero (foreach) l'array di dati che ho ricavato prima nello script, e per ogni opzione
 *                     genero una nuova riga nel CSV.
 * 
 * fclose() :       chiudo uno stream, in questo caso lo stream per il file CSV
 * 
 * ob_get_clean() : Ricavo tutto il contenuto della memoria RAM del server (non è proprio così, ma per
 *                  renderla facile sì) e la salvo dentro una variabile. Infine dopo aver preso il contenuto
 *                  svuoto la memoria.
 *                  Così riesco a salvarmi il file CSV dentro una variabile
 * 
 * ORA RIPETO TUTTO PER IL SECONDO FILE :D
 */

// Genero il contenuto del primo CSV in memoria
ob_start();
$output1 = fopen('php://output', 'w');

// Intestazione colonne
fputcsv(
    $output1,
    ['Titolo', 'Descrizione', 'Durata', 'Posti per Turno', 'Occupati', 'Posti Totali']
);

// Scrittura delle opzioni su più righe
foreach ($opzioniData as $row) {
    fputcsv($output1, $row);
}

// Chiusura stream di dati
fclose($output1);

// Salvataggio del file CSV dentro una variabile
$csv1Content = ob_get_clean();

// --- SECONDO CSV: elenco voti/studenti ---
// Genero il contenuto del secondo CSV in memoria
ob_start();
$output2 = fopen('php://output', 'w');

// Intestazione colonne
fputcsv($output2, ['Nome', 'Cognome', 'Email', 'Classe', 'Sezione', 'Indirizzo', 'Opzione', 'Timestamp']);


// Query per ottenere tutti i voti espressi dagli studenti, con anagrafica e opzione scelta
$stmt = $pdo->prepare(
    "
        SELECT  studenti.nome,
                studenti.cognome,
                studenti.email, 
                studenti.classe, 
                studenti.sezione, 
                indirizzo.nome AS indirizzo, 
                opzioni.titolo AS opzione_titolo, 
                scelte.timestamp
            FROM scelte
                JOIN studenti   ON fk_studente  = studenti.email
                JOIN opzioni    ON fk_opzione   = opzioni.id 
                JOIN indirizzo  ON fk_indirizzo = indirizzo.id
                JOIN sondaggi   ON fk_sondaggio = sondaggi.id
            WHERE fk_sondaggio = ?
            GROUP BY    studenti.nome, 
                        studenti.cognome, 
                        studenti.email, 
                        studenti.classe, 
                        studenti.sezione, 
                        indirizzo.nome, 
                        opzioni.titolo, 
                        scelte.timestamp
"
);
$stmt->execute([$id]);
$votiData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Per ogni riga del recordset genero una riga nel CSV
foreach ($votiData as $row) {
    fputcsv($output2, [
        $row['nome'],
        $row['cognome'],
        $row['email'],
        $row['classe'],
        $row['sezione'],
        $row['indirizzo'],
        $row['opzione_titolo'],
        $row['timestamp']
    ]);
}

/**
 * Per problemi di interpretazione da parte di Excell, ho dovuto inserire quest'istruzione che in poche parole dice
 * "HEY, QUESTO FILE E' CODIFICATO IN UTF-8"
 * 
 * (UTF-8 e ASCII anche questi argomenti da terza superiore)
 */
fprintf($output2, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Chiusura dello stream e salvataggio del file in variabile
fclose($output2);
$csv2Content = ob_get_clean();


/**
 * Come faccio a creare un file zip ed inviarlo ad un client?
 * 
 * Devo prima creare un file temporaneo .zip, trovare un modo per far capire a PHP come interpretarlo,
 * aggiungere i file che ho generato in precedenza, inviare il file al client ed eliminare il file temporaneo.
 * 
 * Come faccio a far capire a PHP come gestire un file ZIP?
 * Uso la classe ZipArchive.
 * 
 * tempnam() :              Crea un file temporaneo con nome casuale nel disco del server, vuole una directory (passo la directory dei file
 *                          temporanei del sistema operativo), e l'estensione del file.
 * 
 * new ZipArchive() :       Creo un oggetto che salvo come $zip. Mi aiuterà ad interpretare via PHP il file Zip
 * 
 * $zip->open() :           Metodo che mi consente di aprire un file zip, passo come flag ZipArchive::CREATE perché sto creando il file
 * 
 * $zip->addFromString() :  Metodo che mi consente di aggiungere un file all'interno della cartella. Vuole il nome che avrà il file inserito
 *                          ed il contenuto del file. E' proprio qui che uso i file salvati nelle variabili in precedenza.
 * 
 * $zip->close() :          Chiudo lo stream. Il file viene salvato.
 * 
 * header() :               Aggiunge degli header alla risposta HTTP del server verso il client, in questo caso specifico che la pagina
 *                          get_sondaggio.php ritornerà un contenuto di tipo zip, che c'è un allegato chiamato Sondaggio_titolosondaggio.zip e
 *                          anche la dimensione del file (Altrimenti durante il download non apparirebbe la percentuale di download).
 *                          Infine reindirizzo alla dashboard.
 *                          
 * 
 * readfile() :             Usa il buffer php://output in modalità scrittura (come prima, ma senza intrappolarlo in memoria), in modo che il client
 *                          possa ricevere il contenuto del file.
 * 
 * unlink:                  Elimino il file temporaneo una volta che il client ha scaricato la zip.
 */

// --- CREAZIONE ARCHIVIO ZIP ---
// Creo un file temporaneo per il file zip
$tempZip = tempnam(sys_get_temp_dir(), 'zip');

// Oggetto che mi aiuterà a leggere il file zip
$zip = new ZipArchive();

// Apro l'archivio zip in modalità scrittura, se non va a buonfine mostro un'errore
if (!$zip->open($tempZip, ZipArchive::CREATE) === TRUE) {
    die("
        <script>
            alert('Qualcosa è andato storto mentre l'archivio si stava creando. Riprova');
            window.location.href = 'dashboard.php';
        </script>
    ");
}
// Aggiungo i due CSV all'archivio
$zip->addFromString("Opzioni_Sondaggio_{$sondaggio['titolo']}.csv", $csv1Content);
$zip->addFromString("Voti_Sondaggio_{$sondaggio['titolo']}.csv", $csv2Content);

// Chiudo lo stream e salvo il file zip
$zip->close();

// Invio l'archivio zip come download all'utente.
// Specifico il contenuto di questa pagina php:
header('Content-Type: application/zip');

// Specifico che c'è un attachment (allegato), e lo nomino Sondaggio_titolosondaggio.zip
header('Content-Disposition: attachment; filename="Sondaggio_' . $sondaggio['titolo'] . '.zip"');

// Dichiaro la grandezza del file in byte, in modo che da browser si vedrà anche la % di download
header('Content-Length: ' . filesize($tempZip));

// Leggo il file e lo invio al client
readfile($tempZip);

// Elimino il file temporaneo dopo il download
unlink($tempZip);

// Redirect alla dashboard
header("Location: dashboard.php");