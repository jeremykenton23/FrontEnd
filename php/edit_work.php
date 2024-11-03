<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Verwerken van formulierinvoer voor toevoegen van werkervaring
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['company'], $_POST['position'], $_POST['start_date'], $_POST['end_date'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO work_experience (user_id, company_name, job_title, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_POST['company'], $_POST['position'], $_POST['start_date'], $_POST['end_date']]);
            header('Location: edit_work.php');
            exit();
        } catch (PDOException $e) {
            die("Fout bij het toevoegen van werkervaring: " . $e->getMessage());
        }
    }
}

// Verwerken van verwijderverzoek
if (isset($_GET['delete'])) {
    $work_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM work_experience WHERE id = ? AND user_id = ?");
        $stmt->execute([$work_id, $_SESSION['user_id']]);
        header('Location: edit_work.php');
        exit();
    } catch (PDOException $e) {
        die("Fout bij het verwijderen van werkervaring: " . $e->getMessage());
    }
}

// Ophalen van werkervaringen
try {
    $stmt = $pdo->prepare("SELECT * FROM work_experience WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $work_experiences = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Fout bij het ophalen van werkervaring: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfielPlus - Werkervaring Bewerken</title>
    <link rel="stylesheet" href="../css/style.css">
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
            <li><a href="php/logout.php">Uitloggen</a></li>
        </ul>
    </nav>
</header>

<main>
    <section id="work-experience">
        <h2>Werkervaring</h2>
        <div class="form-container">
            <form method="post" action="">
                <label for="company">Bedrijf:</label>
                <input type="text" id="company" name="company" required><br>

                <label for="position">Functie:</label>
                <input type="text" id="position" name="position" required><br>

                <label for="start_date">Startdatum:</label>
                <input type="date" id="start_date" name="start_date" required><br>

                <label for="end_date">Einddatum:</label>
                <input type="date" id="end_date" name="end_date"><br>

                <button type="submit">Toevoegen</button>
            </form>
        </div>

        <div class="work-experience-list">
            <?php foreach ($work_experiences as $work) : ?>
                <div class="work-item">
                    <strong>Bedrijf:</strong> <?php echo htmlspecialchars($work['company_name']); ?><br>
                    <strong>Functie:</strong> <?php echo htmlspecialchars($work['job_title']); ?><br>
                    <strong>Van:</strong> <?php echo htmlspecialchars($work['start_date']); ?> <strong>Tot:</strong> <?php echo htmlspecialchars($work['end_date']); ?><br>
                    <a href="?delete=<?php echo htmlspecialchars($work['id']); ?>" onclick="return confirm('Weet je zeker dat je deze werkervaring wilt verwijderen?');">Verwijderen</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
