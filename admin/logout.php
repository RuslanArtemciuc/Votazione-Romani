<?php
// Avvio la sessione per poter accedere e modificare i dati di sessione
session_start();

// Rimuovo tutte le variabili di sessione (logout effettivo)
session_unset();
// Distruggo la sessione sul server
session_destroy();

// Reindirizzo l'utente alla homepage pubblica dopo il logout
header("Location: /index.php");
exit;