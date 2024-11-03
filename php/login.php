<?php
session_start();
require 'db.php'; // Zorg ervoor dat dit pad correct is naar je databaseverbinding.

// Controleer of het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Controleer of zowel gebruikersnaam als wachtwoord zijn ingevuld
    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            // Verkrijg de gebruiker uit de database
            $stmt = $pdo->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Controleer of de gebruiker is gevonden en het wachtwoord correct is
            if ($user && password_verify($password, $user['password'])) {
                // Start de sessie en sla gebruikersinformatie op
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // Redirect naar de juiste pagina op basis van admin status
                if ($user['is_admin']) {
                    // Admins worden doorgestuurd naar admin dashboard
                    header('Location: ../admin.php');
                } else {
                    // Normale gebruikers worden doorgestuurd naar hun profielpagina
                    header('Location: ../php/profile.php');
                }
                exit();
            } else {
                // Foutmelding bij verkeerde inloggegevens
                $error = "Onjuiste gebruikersnaam of wachtwoord.";
            }
        } catch (PDOException $e) {
            die("Fout bij het inloggen: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Verwijst naar je CSS-bestand -->
</head>
<body>
<header>
    <h1>Inloggen</h1>
</header>
<main>
    <section id="login-section">
        <div id="login-container">
            <form method="post" action="login.php">
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Inloggen</button>
            </form>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </div>
    </section>
</main>
<footer>
    <p>&copy; <?php echo date("Y"); ?> ProfielPlus. Alle rechten voorbehouden.</p>
</footer>
</body>
</html>
