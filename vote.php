<!DOCTYPE html>
<html lang="it">

</html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina di Voto</title>
    <!-- Sarebbe stato meglio utilizzare un file CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: .6rem;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        select {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }


        select {
            width: 10%;
            display: inline-block;
            margin-right: 10px;
            box-sizing: border-box;
        }


        select:last-of-type {
            width: calc(100% - 20% - 40px);
        }


        .options {
            margin-top: 15px;
        }

        .options label {
            display: block;
            margin-bottom: 5px;
        }

        .submit-btn {
            margin-top: 20px;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <!-- Div principale a cui applico uno stile di base -->
    <div class="container">

        <!-- Parte di codice php per la renderizzazione delle varie opzioni di voto e degli input
            per l'inserimento dell'anagrafica -->

        <?php

        // In questo file sono presenti le funzioni di base per la connessione al database
        require_once "config.php";


        // Controllo che tra i parametri della query vi sia presente l'id del sondaggio e che non sia vuoto
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            // Se vuoto o non presente, termino l'esecuzione dello script con un messaggio di errore
            exit("ID sondaggio non valido.");
        }

        // Recupero le informazioni del sondaggio e delle opzioni associate con l'id fornito
        $poll_id = intval($_GET['id']);
        $query = "SELECT *, DATE_FORMAT(data, '%d/%m/%Y') AS formatted_date FROM sondaggi WHERE id = ?";
        
        // Preparo la query
        $stmt = $pdo->prepare($query);

        // Eseguo la query
        $stmt->execute([$poll_id]);

        // Estraggo il primo (e unico, data la ricerca tramite id) sondaggio trovato, e lo salvo come array associativo
        $sondaggio = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se non è presente nessun sondaggio, esco dallo script con un messaggio
        if (!$sondaggio) {
            exit("Sondaggio non trovato.");
        }


        // Se il sondaggio è stato trovato procedo a recuperare le opzioni associate
        $query = "SELECT * FROM opzioni WHERE fk_sondaggio = ?";

        // Preparo la query
        $stmt = $pdo->prepare($query);

        // Eseguo la query
        $stmt->execute([$poll_id]);

        /**
         * Estraggo tutte le opzioni e le salvo in un array, avente come ciascun elemento un array associativo
         * che rappresenta la singola opzione
         *   Array grande: recordset
         *   Array piccolo associativo: singola riga del recordset
         */
        $opzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Creo la descrizione del sondaggio da renderizzare
         * E' composto da:
         * - descrizione del sondaggio presente nel database
         * - stringa generata contentente:
         *   - numero di turni
         *   - durata di ciascun turno
         *   - data del sondaggio
         */
        $desc = htmlspecialchars($sondaggio['descrizione']) . "\n\nL'assemblea durerà " . htmlspecialchars($sondaggio['turni']);
        $desc .= " turni.\nOgni turno durerà " . htmlspecialchars($sondaggio['durata_turno']) . " minuti.";
        $desc .= "\nData: " . htmlspecialchars($sondaggio['formatted_date']);

        // htmlspecialchars è una funzione che converte tutti i caratteri speciali in entità HTML.
        // Cosa vuol dire? => che se per esempio inserisco un tag html nella stringa che stampo a schermo con "echo"
        // questa non verrà interpretata come codice html ma verrà mostrata come semplice stringa di testo.

        // Comincio la renderizzazione del sondaggio

        echo '<div class="sondaggio-info">';
        echo '<h1>' . htmlspecialchars($sondaggio['titolo']) . '</h1>';
        echo '<label for="descrizione" class="descrizione-sondaggio">Descrizione:</label>';
        echo "<textarea rows=\"" . (substr_count($desc, "\n") + 1) . "\" id=\"descrizione\" readonly style=\"width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 10px; box-sizing: border-box; resize: none;\">";
        
        /**
         * substr_count($desc, "\n") + 1 serve per contare il numero di righe della descrizione
         * in modo da recolare automaticamente l'altezza della textarea.
         * Più nello specifico substr_count conta quante volte è presente un "Needle" (Ago, sottostringa da cercare)
         * all'interno di un Haystack (Pagliaio, stringa in cui cercare).
         * Qui cerco quante volte è presente il carattere di "New Line" (Nuova riga, "\n") all'interno della stringa $desc.
         */
        
        // (Per creare un commento multi-linea in modo più facile basta scrivere /** + invio. Automaticamente
        // ogni volta che si preme invio viene aggiunto un * all'inizio della riga successiva.)
        
        // Stampo la descriizone del sondaggio
        echo "{$desc}</textarea>";
        echo '</div>';

        ?>

        <?php
        // Uso questo tag PHP per commentare in modo più facile :)

        /**
         * Questo form invia tutti i dati relativi al voto (anagrafica e opzioni scelte) alla pagina submit_vote.php.
         * 
         * Utilizzo il metodo POST perché:
         *      - I dati inviati (nome, cognome, email, opzioni scelte) sono sensibili e non dovrebbero essere visibili
         *        facilmente nell'URL (cosa che succederebbe con il metodo GET)
         *      - Non vi è un limite di lunghezza dei dati inviati (cosa che di nuovo, succederebbe con il metodo GET)
         * 
         * No validate serve per disabilitare la validazione dei campi in HTML5, in modo da poter gestire gli errori
         * lato server in modo più completo.
         */
        ?>

        <form action="admin/submit_vote.php" method="POST" novalidate>
            <!-- L'input tipo Hidden è un campo che non è visibile graficamente ma che mantiene il valore quando
                il form viene inviato. -->
            <input type="hidden" name="id_sondaggio" value="<?php echo $sondaggio["id"] ?>">

            <!-- Così controllo in modo molto semplice (e molto poco sicuro) lato server se sto inviando una richiesta
                da questo script -->
            <input type="hidden" name="source" value="vote">
        
            <!-- La label è un campo di testo speciale che si lega agli input
            tramite l'attributo "for", che deve contenere l'id dell'input a cui si riferisce -->
            <!-- (Non è obbligatorio ma è utile. Per esempio cliccando la label si può cominciare a scrivere dentro un input) -->
            <label for="name">Nome:</label>
            
            <!-- Nome votante -->
            <input type="text" id="name" name="name" required>

            <!-- Cognome  votante-->
            <label for="surname">Cognome:</label>
            <input type="text" id="surname" name="surname" required>
            
            <!-- Div per la scelta della classe - sezione - indirizzo -->
            <div>
                <label for="class">Classe:</label>
                <!-- br, Break Line, è il "A capo" in HTML -->
                <br>

                <select id="class" name="class" required>
                
                <?php
                /**
                 * Ecco di nuovo un tag PHP solo per commentare in modo più facile :)
                 * 
                 * Qui possiamo vedere una strana sintassi per il ciclo for, dove le istruzioni
                 * da eseguire non sono racchiuse tra due parentesi graffe {} e espresse in PHP
                 * ma sono codice HTML racchiuso tra due tag PHP.
                 * 
                 * <?php for (condizione): ?>
                 *     <!-- Codice HTML da ripetere finché la condizione è vera -->
                 * <?php endfor; ?î
                 * 
                 * Sì, si poteva anche fare con le parentesi graffe, ma questa sintassi è molto più
                 * leggibile quando si deve mischiare codice PHP e HTML.
                 * 
                 * In questo caso i vari cicli for servono per generare le opzioni delle select che serviranno
                 * a scegliere la classe (1-5), la sezione (A-D) e l'indirizzo (presi dal database).
                 */
                ?>

                    <!-- Qua si potevano scrivere anche a mano ma così mi piace di più :) -->
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>

                    <!-- Stessa cosa della select sopra -->
                <select id="section" name="section" required>
                    <?php foreach (range('A', 'D') as $section): ?>
                        <option value="<?= $section; ?>"><?= $section; ?></option>
                    <?php endforeach; ?>

                    <?php
                    /**
                     * Attenzione!
                     * Osserviamo come ho stampato a schermo le variabili nei due casi:
                     * - Primo caso:
                     *   <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                     * Cosa succede? Apro il tag PHP, eseguo la funzione echo specificando la variabile da stampare, e chiudo
                     * 
                     * - Secondo caso:
                     *   <option value="<?= $section; ?>"><?= $section; ?></option>
                     * Cosa succede? Apro un tag PHP speciale, è una scorciatoia per scrivere "echo".
                     * All'interno posso inserire solo espressioni che di solito possono essere associate con la funzione echo.
                     * Come:
                     * - Variabili ($section)
                     * - Operazioni matematiche ($a + $b)
                     * - Funzioni che ritornano un valore (date('Y'))
                     * 
                     * Non posso invece inserire istruzioni di controllo (if, for, while, switch), dichiarazioni di funzione o
                     * di variabili, e posso inserire solo una singola espressione.
                     * 
                     * Quindi no:
                     *   <?= $a = 5; ?> (Dichiarazione di variabile)
                     *   <?= if ($a > $b) { echo $a; } ?> (Istruzione di controllo) 
                     *   <?= function test() { return 5; } ?> (Dichiarazione di funzione)
                     * 
                     * Però Sì:
                     *   <?= (condizione) ? 'vero' : 'falso'; ?> (condizione ternaria)
                     * 
                     * Questa sintassi è molto comoda per stampare a schermo variabili o espressioni in modo rapido e conciso.
                     */
                    ?>
                </select>

                <select id="address" name="address" required>
                    <?php
                    // Query per prendere tutti gli indirizzi
                    $query = "SELECT id, nome FROM indirizzo";

                    // Preparo la query
                    $stmt = $pdo->prepare($query);

                    // Eseguo la query
                    $stmt->execute();

                    // Salvo il risultato in un array avente come ciascun elemento un array associativo
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Per ogni indirizzo eseguo la renderizzazione
                    foreach ($result as $indirizzo) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
                    }

                    ?>
                </select>
            </div>

            <!-- Email -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" pattern=".+@poloromani\.net$" title="Inserisci solo mail di istituto. Deve quindi finire per @poloromani.net" placeholder="Email di istituto (@poloromani.net)" required>

            <!-- Div per la renderizzazione delle opzioni -->
            <div class="options">
                <label>Scegli le tue opzioni:</label>
                <hr>
                <!-- hr, Horizontal Line. Riga che occupa la larghezza massima (solo estetica) -->

                <?php
                // Se non ci sono opzioni stampo un messaggio
                if (empty($opzioni)) {
                    echo '<p>Nessuna opzione disponibile per questo sondaggio.</p>';
                } else {
                    // Altrimenti per ogni opzione eseguo la renderizzazione
                    foreach ($opzioni as $row) {

                        // Calcolo il numero totale di posti per quell'opzione (posti per turno * numero di turni)
                        $posti_totali = $row['posti'] * $sondaggio['turni'];

                        // Racchiudo tutto dentro una label, così che cliccando su qualsiasi punto della card
                        // si seleziona la checkbox
                        echo '<label for="checkbox-' . $row['id'] . ' ">';
                        
                        echo '<div class="opzione">';
                        echo '<p style="font-size: 1.4rem; margin-bottom: 5px">';
                        
                        // Checkbox per selezionare l'opzione, il valore è l'id dell'opzione
                        echo '<input id="checkbox-' . $row['id'] . '" type="checkbox" name="vote[]" value="' . $row['id'] . '">';
                        
                        // Titolo dell'opzione e in più piccolo il numero di posti occupati e il numero totale di posti
                        // con id in modo da poterli aggiornare dinamicamente con JavaScript
                        echo '<strong>' . htmlspecialchars($row['titolo']) . '</strong>';
                        echo ' <span style="font-size: .6rem; color: gray">(<span id="optionid-' . $row["id"] . '"></span>/<span id="optionid-' . $row["id"] . '-tot"></span>)</span></p>';

                        // Textarea con la descrizione dell'opzione
                        echo '<textarea rows=';
                        echo substr_count(htmlspecialchars($row['descrizione']), "\n") + 1;
                        // Stesso trick di prima per calcolare il numero di righe
                        // della descrizione in modo da regolare automaticamente l'altezza della textarea.

                        echo ' id="descrizione" readonly style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 10px; box-sizing: border-box; resize: none;">';

                        // Stampo contenuto generato in precedenza
                        echo htmlspecialchars($row['descrizione']) . '</textarea>';

                        // Chiudo tutti i tag aperti
                        echo '</div></label><br><hr>';
                    }
                }
                ?>
            </div>

            <!-- Invia voto -->
            <button type="submit" class="submit-btn">Invia Voto</button>
        </form>


        <script>
            // Funzione JavaScript per limitare il numero di checkbox selezionabili (dato presente per ogni sondaggio nel DB)
            
            /**
             * Per ogni checkbox, aggiungo un listener (funzione che viene eseguita solo quando avviene un certo evento)
             * che eseguirà un controlo sul nunmero totale di checkbox selezionate.
             * 
             * forEach per applicare il listener a CIASCIUNA checkbox
             */
            document.querySelectorAll('input[name="vote[]"]').forEach(checkbox => {

                // Il listener richiede il tipo di evento da ascoltare (change, quando lo stato della checkbox cambia, o click)
                // e la funzione da eseguire quando l'evento avviene

                // La funzione si può esprimere in due modi:
                // 1) Funzione anonima (senza nome) espressa con la parola chiave function:
                //    checkbox.addEventListener('change', function(parametro1, parametro2, ...) {qualcosa});
                // 
                // 2) Funzione lambda (freccia) espressa con sintassi più compatta:
                //    checkbox.addEventListener('change', (parametro1, parametro2, ...) => {qualcosa});)

                checkbox.addEventListener('change', () => {

                    // Faccio una query che prende tutte le checkbox con nome "vote[]" che sono selezionate (checked)
                    const checkboxes = document.querySelectorAll('input[name="vote[]"]:checked');

                    // Ricavo il numero minimo e massimo di opzioni selezionabili, passandole da PHP a JavaScript
                    // (di solito questo tipo di dato, da PHP a Javascript, viene richiesto tramite chiamate AJAX, ma per
                    // semplicità le passo direttamente così)

                    // parseInt server per convertire una stringa in un numero intero
                    // json_encode server per convertire un valore PHP in una stringa JSON
                    // (utile per passare array o oggetti da PHP a JavaScript, ma qui lo uso per passare un semplice numero)
                    const minChoices = parseInt(<?php echo json_encode($sondaggio["min_voti"]); ?>, 10);
                    const maxChoices = parseInt(<?php echo json_encode($sondaggio["max_voti"]); ?>, 10);

                    // Se il numero di checkbox selezionate è maggiore del massimo, avendo il riferimento della checkbox
                    // appena premuta, la deseleziono e mostro un messaggio
                    if (checkboxes.length > maxChoices) {
                        alert(`Puoi selezionare al massimo ${maxChoices} opzione/i.`);
                        checkbox.checked = false;
                    }
                });
            });

            // Funzione asincrona per recupare il numero di posti occupati per ciascuna opzione
            async function fetchPostiOccupati() {
                // Try and catch. Per una vedere cosa sono guarda config.php
                try {
                    // faccio una chiamata al file admin/get_posti.php con l'id del sondaggio come parametro
                    const response = await fetch('admin/get_posti.php?id=<?php echo $sondaggio["id"] ?>');

                    // Se la risposta non è ok (codice di stato HTTP non 200), lancio un errore (Exception)
                    if (!response.ok) {
                        throw new Error('Errore durante il recupero dei dati.');
                    }

                    // Se tutto va bene estraggo i dati
                    const data = await response.json();

                    // E aggiorno il contenuto delle card
                    updatePostiOccupati(data);
                } catch (error) {
                    // Nel caso di errori, gli stampo nella console del browser
                    console.error('Errore:', error);
                }
            }

            function updatePostiOccupati(data) {
                // Per ogni elemento di "data"
                data.forEach(option => {
                    // Recupero gli elementi HTML da aggiornare (span con id specifico)
                    const postiElement = document.querySelector(`span[id="optionid-${option.opzione_id}"]`);
                    const postiTotElement = document.querySelector(`span[id="optionid-${option.opzione_id}-tot"]`);

                    // E se gli elementi esistono stampo il numero di posti occupati e il numero totale di posti
                    if (postiElement) {
                        postiElement.textContent = option.scelte_count;
                    }
                    if (postiTotElement) {
                        postiTotElement.textContent = <?= $sondaggio["turni"]; ?> * option.posti;
                    }

                    // Disabilito la checkbox se i posti sono esauriti
                    const checkbox = document.querySelector(`input[type="checkbox"][value="${option.opzione_id}"]`);
                    if (checkbox && option.scelte_count >= (<?= $sondaggio["turni"]; ?> * option.posti)) {
                        checkbox.disabled = true;
                        checkbox.checked = false;
                    }
                });
            }

            // Creo una specie di while(true) che ogni 3 secondi richiama la funzione per aggiornare i posti disponibili
            setInterval(fetchPostiOccupati, 3000);

            // Eseguo una chiamata iniziale, altrimenti dovrei aspettare 3 secondi prima di vedere i posti occupati
            fetchPostiOccupati();
        </script>
    </div>
</body>

</html>