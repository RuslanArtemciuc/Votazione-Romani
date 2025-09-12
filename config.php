<?php
// File di configurazione per la connessione al database e la gestione delle sessioni


// Con questo controllo, verifico che l'utente non stia cercando di accedere direttamente a questo file tramite URL
//      Esempio: http://localhost/config.php
// In tal caso, lo reindirizzo alla homepage (index.php)
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    header('Location: index.php');
    exit();
}

// Avvio della sessione PHP
session_start();

// Variabili per la connessione al database.
// ATTENZIONE: Modifica questi valori se necessario
$host = 'localhost';
$dbname = 'votazioneromani';
$username = 'root';
$password = '';

// Connessione al database con PDO (PHP Data Objects)
// Breve try and catch. Cosa fa? => Prova ad eseguire una sezione di codice (try) e, se si verifica un errore,
//                    è in grado di gestirlo senza fermare tutto il codice come succederebbe altrimenti.
// Nel caso di errore per ora si limita a mostrare un messaggio di errore e blocca l'esecuzione del resto del codice die().
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Funzione che verifica se l'utente ha fatto l'accesso come admin.
// La importo all'inizio di ogni pagina admin.
// Nel caso non fosse autenticato, lo reindirizzo alla homepage.
function check_admin()
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] === false) {
        header('Location: /index.php');
        exit();
    }
}
