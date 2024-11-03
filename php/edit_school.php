<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Verwerken van formulierinvoer voor toevoegen van schoolprestaties
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['school_name'], $_POST['diploma'], $_POST['grade'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO school_performance (user_id, school_name, diploma, grade) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_POST['school_name'], $_POST['diploma'], $_POST['grade']]);
            header('Location: edit_school.php');
            exit();
        } catch (PDOException $e) {
            die("Fout bij het toevoegen van schoolprestaties: " . $e->getMessage());
        }
    }
}

// Verwerken van verwijderverzoek
if (isset($_GET['delete'])) {
    $performance_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM school_performance WHERE id = ? AND user_id = ?");
        $stmt->execute([$performance_id, $_SESSION['user_id']]);
        header('Location: edit_school.php');
        exit();
    } catch (PDOException $e) {
        die("Fout bij het verwijderen van schoolprestaties: " . $e->getMessage());
    }
}

// Ophalen van schoolprestaties
try {
    $stmt = $pdo->prepare("SELECT * FROM school_performance WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $school_performances = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Fout bij het ophalen van schoolprestaties: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfielPlus - Schoolprestaties Bewerken</title>
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
    <section id="school-performance">
        <h2>Schoolprestaties</h2>
        <div class="form-container">
            <form method="post" action="">
                <label for="school_name">Schoolnaam:</label>
                <input type="text" id="school_name" name="school_name" required><br>

                <label for="diploma">Diploma:</label>
                <input type="text" id="diploma" name="diploma" required><br>

                <label for="grade">Cijfers:</label>
                <input type="text" id="grade" name="grade" required><br>

                <button type="submit">Toevoegen</button>
            </form>
        </div>

        <div class="school-performance-list">
            <?php foreach ($school_performances as $school) : ?>
                <div class="performance-item">
                    <strong>Schoolnaam:</strong> <?php echo htmlspecialchars($school['school_name']); ?><br>
                    <strong>Diploma:</strong> <?php echo htmlspecialchars($school['diploma']); ?><br>
                    <strong>Cijfers:</strong> <?php echo htmlspecialchars($school['grade']); ?><br>
                    <a href="?delete=<?php echo htmlspecialchars($school['id']); ?>" onclick="return confirm('Weet je zeker dat je deze schoolprestatie wilt verwijderen?');">Verwijderen</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
