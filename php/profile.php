<?php
session_start();
require '../php/db.php'; // Zorg ervoor dat dit pad correct is

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html'); // Verwijs naar de inlogpagina als gebruiker niet is ingelogd
    exit();
}

// Haal de gegevens van de gebruiker op
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

// Haal schoolprestaties op
try {
    $stmt = $pdo->prepare("SELECT * FROM school_performance WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $school_performances = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Fout bij het ophalen van schoolprestaties: " . $e->getMessage());
}

// Haal werkervaring op
try {
    $stmt = $pdo->prepare("SELECT * FROM work_experience WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $work_experiences = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Fout bij het ophalen van werkervaring: " . $e->getMessage());
}

// Haal hobby's op
try {
    $stmt = $pdo->prepare("SELECT * FROM hobbies WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hobbies = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Fout bij het ophalen van hobby's: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfielPlus - Profiel</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Pas het pad naar je stylesheet aan -->
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

<main>
    <h1>Welkom, <?php echo htmlspecialchars($user['username']); ?>!</h1>

    <section id="personal-info" class="personal-info">
        <h2>Persoonlijke Gegevens</h2>
        <p>Voornaam: <?php echo htmlspecialchars($user['first_name']); ?></p>
        <p>Achternaam: <?php echo htmlspecialchars($user['last_name']); ?></p>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Postcode: <?php echo htmlspecialchars($user['postal_code']); ?></p>
        <p>Huisnummer: <?php echo htmlspecialchars($user['house_number']); ?></p>
        <p>Geboortedatum: <?php echo htmlspecialchars($user['birthdate']); ?></p>
        <a href="edit_profile.php">Profiel Bewerken</a>
    </section>

    <section id="school-performance" class="school-performance">
        <h2>Schoolprestaties</h2>
        <a href="edit_school.php">Schoolprestaties Beheren</a>
        <div class="school-performance-list">
            <?php if (count($school_performances) > 0) : ?>
                <?php foreach ($school_performances as $school) : ?>
                    <div class="performance-item">
                        <strong>Schoolnaam:</strong> <?php echo htmlspecialchars($school['school_name']); ?><br>
                        <strong>Diploma:</strong> <?php echo htmlspecialchars($school['diploma']); ?><br>
                        <strong>Cijfers:</strong> <?php echo htmlspecialchars($school['grade']); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Geen schoolprestaties gevonden.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="work-experience" class="work-experience">
        <h2>Werkervaring</h2>
        <a href="edit_work.php">Werkervaring Beheren</a>
        <div class="work-experience-list">
            <?php if (count($work_experiences) > 0) : ?>
                <?php foreach ($work_experiences as $work) : ?>
                    <div class="work-item">
                        <strong>Bedrijf:</strong> <?php echo htmlspecialchars($work['company_name']); ?><br>
                        <strong>Functie:</strong> <?php echo htmlspecialchars($work['job_title']); ?><br>
                        <strong>Van:</strong> <?php echo htmlspecialchars($work['start_date']); ?> <strong>Tot:</strong> <?php echo htmlspecialchars($work['end_date']); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Geen werkervaring gevonden.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="hobbies" class="hobbies">
        <h2>Hobby's</h2>
        <a href="edit_hobbies.php">Hobby's Beheren</a>
        <div class="hobbies-list">
            <?php if (count($hobbies) > 0) : ?>
                <?php foreach ($hobbies as $hobby) : ?>
                    <div class="hobby-item">
                        <strong>Hobby:</strong> <?php echo htmlspecialchars($hobby['hobby_name']); ?><br>
                        <strong>Beschrijving:</strong> <?php echo htmlspecialchars($hobby['description']); ?><br>
                        <?php if (!empty($hobby['image_url'])) : ?>
                            <img src="../uploads/<?php echo htmlspecialchars($hobby['image_url']); ?>" alt="<?php echo htmlspecialchars($hobby['hobby_name']); ?>" style="max-width: 200px;"><br>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Geen hobby's gevonden.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2024 ProfielPlus. Alle rechten voorbehouden.</p>
</footer>
</body>
</html>
