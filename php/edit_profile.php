<?php
session_start();

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');  // Verwijs naar de inlogpagina als gebruiker niet is ingelogd
    exit();
}

// Include het bestand voor databaseverbinding
require '../php/db.php';  // Zorg ervoor dat dit pad klopt

// Verwerk formulierinzending voor profiel
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verkrijg de gegevens van het profielformulier
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : '';
    $house_number = isset($_POST['house_number']) ? $_POST['house_number'] : '';
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : '';

    // Valideer de gegevens
    if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($email) && !empty($postal_code) && !empty($house_number) && !empty($birthdate)) {
        try {
            // Update de gegevens in de database
            $stmt = $pdo->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, postal_code = ?, house_number = ?, birthdate = ? WHERE id = ?");
            $stmt->execute([$username, $first_name, $last_name, $email, $postal_code, $house_number, $birthdate, $_SESSION['user_id']]);

            $success = "Profiel succesvol bijgewerkt.";
        } catch (PDOException $e) {
            $error = "Fout bij het bijwerken van gegevens: " . $e->getMessage();
        }
    }

    // Verwerk formulierinzending voor wachtwoord
    if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        try {
            // Haal het huidige wachtwoord op van de database
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            $hashed_password = $user['password'];

            // Controleer het oude wachtwoord
            if (!password_verify($current_password, $hashed_password)) {
                $password_error = "Oud wachtwoord is niet correct.";
            } elseif ($new_password !== $confirm_password) {
                $password_error = "Nieuwe wachtwoorden komen niet overeen.";
            } elseif (empty($new_password)) {
                $password_error = "Nieuw wachtwoord mag niet leeg zijn.";
            } else {
                // Hash het nieuwe wachtwoord en update het in de database
                $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_hashed_password, $_SESSION['user_id']]);
                $password_success = "Wachtwoord succesvol bijgewerkt.";
            }
        } catch (PDOException $e) {
            $password_error = "Fout bij het bijwerken van wachtwoord: " . $e->getMessage();
        }
    }
}

// Verkrijg de gegevens van de gebruiker
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Controleer of er gegevens zijn opgehaald
    if (!$user) {
        die("Gebruiker niet gevonden.");
    }
} catch (PDOException $e) {
    die("Fout bij het ophalen van gegevens: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfielPlus - Profiel Bewerken</title>
    <link rel="stylesheet" href="../css/style.css">  <!-- Zorg ervoor dat het pad naar je stylesheet klopt -->
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="profile.php">Home</a></li>
            <li><a href="edit_profile.php">Profiel Bewerken</a></li>
            <li><a href="edit_school.php">Schoolprestaties Bewerken</a></li>
            <li><a href="edit_work.php">Werkervaring Bewerken</a></li>
            <li><a href="edit_hobbies.php">Hobby's Bewerken</a></li>
            <li><a href="admin.php">Beheerderspaneel</a></li>
            <li><a href="logout.php">Uitloggen</a></li>
        </ul>
    </nav>
</header>

<main id="edit-profile-section">
    <div id="edit-profile-container" class="blue-box">
        <h2>Profiel Bewerken</h2>

        <!-- Profiel Update Formulier -->
        <?php if (isset($success)) : ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <?php if (isset($error)) : ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="edit_profile.php" method="post">
            <label for="username">Gebruikersnaam:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="first_name">Voornaam:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

            <label for="last_name">Achternaam:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="postal_code">Postcode:</label>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code']); ?>" required>

            <label for="house_number">Huisnummer:</label>
            <input type="text" id="house_number" name="house_number" value="<?php echo htmlspecialchars($user['house_number']); ?>" required>

            <label for="birthdate">Geboortedatum:</label>
            <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>" required>

            <input type="submit" value="Bijwerken">
        </form>

        <!-- Wachtwoord Wijziging Formulier -->
        <h2>Wachtwoord Wijzigen</h2>

        <?php if (isset($password_success)) : ?>
            <p class="success"><?php echo htmlspecialchars($password_success); ?></p>
        <?php endif; ?>

        <?php if (isset($password_error)) : ?>
            <p class="error"><?php echo htmlspecialchars($password_error); ?></p>
        <?php endif; ?>

        <form action="edit_profile.php" method="post">
            <label for="current_password">Huidig Wachtwoord:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">Nieuw Wachtwoord:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Bevestig Nieuw Wachtwoord:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <input type="submit" value="Wachtwoord Bijwerken">
        </form>

        <a href="profile.php">Terug naar profiel</a>
    </div>
</main>

<footer>
    <p>&copy; 2024 ProfielPlus. Alle rechten voorbehouden.</p>
</footer>
</body>
</html>
