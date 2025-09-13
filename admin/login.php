<?php
// Includo il file di configurazione che contiene la connessione al database e avvia la sessione
require_once "../config.php";

// Se il form è stato inviato tramite POST significa che ci sono dei dati da elaborare
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recupero e pulisco i dati inseriti dall'utente
    $username = trim($_POST['username']) ?? '';
    $password = $_POST['password'] ?? '';
    // Se non sai cosa vuol dire => ?? ''; Consulta add_option.php


    // Preparo la query per cercare l'utente admin con lo username inserito
    $stmt = $pdo->prepare("SELECT * FROM `admin` WHERE username = ? LIMIT 1; ");
    $stmt->execute([$username]);
    $row = $stmt->fetch(); // Estraggo la riga trovata (se esiste)

    // Controllo che l'utente esista e che la password sia corretta
    // password_verify confronta la password inserita con l'hash salvato nel database
    if (!empty($row) && $username === $row["username"] && password_verify($password, $row["password"])) {
        // Login riuscito: salvo le informazioni di login nella sessione
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        // Redirect alla dashboard admin
        header("Location: dashboard.php");
        exit;
    } else {
        // Login fallito: uso la variabile error per inserire il messaggio di errore, che più sotto stamperò a schermo
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <!-- Se non sai come si legge questo if, guarda vote.php riga 237 -->
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>