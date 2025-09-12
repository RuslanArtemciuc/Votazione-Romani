<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scegli Sondaggio - VotazioniRomani</title>

    <!-- Sì, sarebbe stato meglio un file css maaa è andata così -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .login-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .login-btn:hover {
            background-color: #0056b3;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .poll-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 80%;
            margin: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .poll-card:hover {
            transform: scale(1.05);
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 1.7rem;
            margin-bottom: 20px;
            display: inline;
        }

        .card-date {
            display: inline;
            color: gray;
            font-size: .7rem;
        }

        .card-description {
            padding: 0 5px;
        }


        .btn-success {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn-success:hover {
            background-color: #218838;
        }


        /* Per la visualizzazione su cellulare o schermi piccoli si può usare @media che definisce prioprietà css
        che si applicano solo rispettando certe condizioni.*/
        @media (max-width: 768px) {
            .poll-card {
                width: 95%;
            }
        }
    </style>
</head>

<body>
    <!-- Div principale a cui applico uno stile di base -->
    <div class="container">

        <!-- Link per il login nella parte admin -->
        <a href="admin/login.php" class="login-btn">Admin Login</a>
        <h1>Votazione Romani</h1>

        <div class="row">

            <!-- Parte di codice php per la renderizzazione dei sondaggi -->
            <?php

            // In questo file sono presenti le funzioni di base per la connessione al database
            require_once "config.php";

            // Query, che prende tutti i sondaggi e ne formatta la data in giorni/mesi/anno
            $stmt = $pdo->prepare("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') AS formatted_date FROM sondaggi");

            // Eseguo la query
            $stmt->execute();

            // Estraggo tutti i sondaggi e li converto in un unico array, avente come ciascun elemento un array associativo
            // che rappresenta ciascun singolo sondaggio
            $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Comincio la renderizzazione solo se ci sono sondaggi
            if (!empty($polls)) {

                // Per ogni elemento dell'array associativo, che chiamo $poll, creo una card (div con del css)
                // nella quale inserisco i dati presi dall'elemento.
                // Un array associativo ci consente di accedere ai dati tramite il nome della colonna del database
                foreach ($polls as $poll) {
                    echo '
                    <div class="poll-card">
                        <div class="card-body">
                            <p><h2 class="card-title">' . htmlspecialchars($poll['titolo']) . ' </h2> <span class="card-date">' . htmlspecialchars($poll['formatted_date']) . '</span></p>
                            <p class="card-description">' . htmlspecialchars($poll['descrizione']) . '</p>
                            <a href="vote.php?id=' . $poll['id'] . '" class="btn-success">Vota</a>
                        </div>
                    </div>';
                    // htmlspecialchars è una funzione che converte tutti i caratteri speciali in entità HTML.
                    // Cosa vuol dire? => che se per esempio inserisco un tag html nella stringa che stampo a schermo con "echo"
                    // questa non verrà interpretata come codice html ma verrà mostrata come semplice stringa di testo.

                    // Nel link (tasto Vota) inserisco nella query del link l'id del sondaggio, così che nella pagina
                    // vote.php potrò sapere a quale sondaggio si riferisce il voto.

                }

            // Se non ci sono sondaggi, mostro un messaggio
            } else {
                echo '<p class="text-center">Non ci sono sondaggi disponibili al momento.</p>';
            }
            ?>
        </div>
    </div>
</body>

</html>