<?php
// Includo il file di configurazione che contiene la connessione al database e funzioni di utilità
require_once "../config.php";

// Controllo che l'utente sia autenticato come admin
check_admin();

// Inizializzo le variabili per i campi del sondaggio
$titolo = "";
$descrizione = "";
$data = "";
$turni = "";
$durata_turno = "";

// Se è stato passato un id tramite GET, sto modificando un sondaggio esistente
if (isset($_GET['id'])) {
    $poll_id = intval($_GET['id']);
    // Recupero i dati del sondaggio dal database
    $stmt = $pdo->prepare("SELECT * FROM sondaggi WHERE id = :id");
    $stmt->execute(['id' => $poll_id]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);

    // Popolo le variabili con i dati esistenti (per la modifica)
    $titolo = $poll["titolo"];
    $descrizione = $poll["descrizione"];
    $data = $poll["data"];
    $turni = $poll["turni"];
    $durata_turno = $poll["durata_turno"];
    $min_voti = $poll["min_voti"];
    $max_voti = $poll["max_voti"];
}

// Se il form è stato inviato tramite POST => (creazione o modifica sondaggio)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recupero e pulisco i dati dal form
    $titolo = isset($_POST['titolo']) ? trim($_POST['titolo']) : "";
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : "";
    $data = isset($_POST['data']) ? trim($_POST['data']) : "";
    $turni = isset($_POST['turni']) ? intval($_POST['turni']) : 0;
    $durata_turno = isset($_POST['durata_turno']) ? intval($_POST['durata_turno']) : 0;
    $max_voti = isset($_POST['max_voti']) ? intval($_POST['max_voti']) : 0;
    $min_voti = isset($_POST['min_voti']) ? intval($_POST['min_voti']) : 0;
    // Ho usato molte condizioni ternarie, guarda vote.php riga 293


    // Validazione base dei campi obbligatori
    if (!empty($titolo) && !empty($descrizione) && !empty($data) && $turni > 0 && $durata_turno > 0) {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // MODIFICA sondaggio esistente
            $poll_id = intval($_POST['id']);
            $stmt = $pdo->prepare("UPDATE sondaggi SET titolo = :titolo, descrizione = :descrizione, data = :data, turni = :turni, durata_turno = :durata_turno, max_voti = :max_voti, min_voti = :min_voti WHERE id = :id");
            $stmt->execute([
                'titolo' => $titolo,
                'descrizione' => $descrizione,
                'data' => $data,
                'turni' => $turni,
                'durata_turno' => $durata_turno,
                'id' => $poll_id,
                'min_voti' => $min_voti,
                'max_voti' => $max_voti
            ]);
        } else {
            // CREAZIONE nuovo sondaggio
            $stmt = $pdo->prepare("INSERT INTO sondaggi (titolo, descrizione, data, turni, durata_turno, min_voti, max_voti) VALUES (:titolo, :descrizione, :data, :turni, :durata_turno, :min_voti, :max_voti)");
            $stmt->execute([
                'titolo' => $titolo,
                'descrizione' => $descrizione,
                'data' => $data,
                'turni' => $turni,
                'durata_turno' => $durata_turno,
                'min_voti' => $min_voti,
                'max_voti' => $max_voti
            ]);

            // Recupero l'id del nuovo sondaggio appena creato
            $poll_id = $pdo->lastInsertId();
        }

        // Refresh della pagina, ma passando id come parametro. Così lo script saprà che il sondaggio che si andrà
        // a modificare è esistente
        header("Location: manage_as.php?id=" . $poll_id);
        exit;
    } else {
        // Se mancano campi obbligatori, mostro un messaggio di errore
        echo "All fields are required and must be valid.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea o modifica sondaggio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>

<body>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo isset($poll_id) ? $poll_id : ''; ?>">

        <label for="titolo">Titolo:</label>
        <input type="text" id="titolo" name="titolo" value="<?php echo htmlspecialchars($titolo); ?>" required>
        <br>

        <label for="descrizione">Descrizione:</label>
        <textarea id="descrizione" name="descrizione" required><?php echo htmlspecialchars($descrizione); ?></textarea>
        <br>

        <label for="data">Data:</label>
        <input type="date" id="data" name="data" value="<?php echo htmlspecialchars($data); ?>" required>
        <br>

        <label for="turni">Turni:</label>
        <input type="number" id="turni" name="turni" value="<?php echo htmlspecialchars($turni); ?>" required>
        <br>

        <label for="durata_turno">Durata Turno (minuti):</label>
        <input type="number" id="durata_turno" name="durata_turno" value="<?php echo htmlspecialchars($durata_turno); ?>" required>
        <br>
        
        <label for="min_voti">min_voti:</label>
        <input type="number" id="min_voti" name="min_voti" value="<?php echo htmlspecialchars($min_voti); ?>" required>
        
        <br>
        <label for="max_voti">max_voti:</label>
        <input type="number" id="max_voti" name="max_voti" value="<?php echo htmlspecialchars($max_voti); ?>" required>
        <br>
        <input type="submit" value="Salva">

    </form>
    <br>
    <hr>
    
    <form method="post" action="add_option.php">
        <input type="hidden" name="fk_sondaggio" value="<?php echo isset($poll_id) ? $poll_id : ''; ?>">
        <h3>Aggiungi nuova opzione</h3>
        <label for="titolo_opzione">Titolo:</label>
        <input type="text" id="titolo_opzione" name="titolo" required autofocus>
        <br>
        <label for="descrizione_opzione">Descrizione:</label>
        <textarea id="descrizione_opzione" name="descrizione" required cols="100" rows="4"></textarea>
        <br>
        <label for="posti_opzione">Posti:</label>
        <input type="number" id="posti_opzione" name="posti" required>
        <br>
        <input type="submit" value="Aggiungi Opzione">
    </form>
    <hr>
    <?php
    if (isset($poll_id)) {
        // Recupero tutte le opzioni associate a questo sondaggio
        $stmt = $pdo->prepare("SELECT * FROM opzioni WHERE fk_sondaggio = :fk_sondaggio");
        $stmt->execute(['fk_sondaggio' => $poll_id]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Per ogni opzione, mostro i dettagli e il pulsante per cancellarla
        foreach ($options as $option) {
            $posti_totali = $option['posti'] * $turni; // Calcolo il numero totale di posti
            echo '<div>';
            echo '<strong>Titolo:</strong> ' . htmlspecialchars($option['titolo']) . '<br>';
            echo '<strong>Descrizione:</strong> ' . htmlspecialchars($option['descrizione']) . '<br>';
            echo '<strong>Durata:</strong> ' . htmlspecialchars($option['durata']) . ' turni (' . htmlspecialchars($durata_turno*$option['durata']) . ' minuti)<br>';
            echo '<strong>Posti per turno:</strong> ' . htmlspecialchars($option['posti']) . '<br>';
            echo '<strong>Posti Totali:</strong> ' . htmlspecialchars($posti_totali) . '<br>';
            echo '<a href="delete_option.php?id=' . $option['id'] . '"> <button> Cancella </button> </a>';
            echo '</div><hr>';
        }
    }
    ?>

</body>

</html>