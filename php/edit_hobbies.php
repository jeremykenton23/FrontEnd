<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Verwerken van formulierinvoer voor toevoegen van hobby's
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hobby_name'], $_POST['description'])) {
        $image_url = null;

        // Verwerken van foto-upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            $upload_file = $upload_dir . basename($_FILES['image']['name']);

            // Zorg ervoor dat het uploadpad bestaat en dat de bestandsextensie is toegestaan
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

            if (in_array($file_extension, $allowed_extensions) && move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                $image_url = basename($_FILES['image']['name']);
            } else {
                die("Fout bij het uploaden van de afbeelding.");
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO hobbies (user_id, hobby_name, description, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_POST['hobby_name'], $_POST['description'], $image_url]);
            header('Location: edit_hobbies.php');
            exit();
        } catch (PDOException $e) {
            die("Fout bij het toevoegen van hobby's: " . $e->getMessage());
        }
    }
}

// Verwerken van verwijderverzoek
if (isset($_GET['delete'])) {
    $hobby_id = intval($_GET['delete']);
    try {
        // Eerst ophalen van het pad naar de afbeelding
        $stmt = $pdo->prepare("SELECT image_url FROM hobbies WHERE id = ? AND user_id = ?");
        $stmt->execute([$hobby_id, $_SESSION['user_id']]);
        $hobby = $stmt->fetch();
        if ($hobby && $hobby['image_url']) {
            $image_path = '../uploads/' . $hobby['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path); // Verwijder de afbeelding
            }
        }

        // Verwijderen van de hobby
        $stmt = $pdo->prepare("DELETE FROM hobbies WHERE id = ? AND user_id = ?");
        $stmt->execute([$hobby_id, $_SESSION['user_id']]);
        header('Location: edit_hobbies.php');
        exit();
    } catch (PDOException $e) {
        die("Fout bij het verwijderen van hobby's: " . $e->getMessage());
    }
}

// Ophalen van hobby's
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
    <title>ProfielPlus - Hobby's Bewerken</title>
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
    <section id="hobbies">
        <h2>Hobby's</h2>
        <div class="form-container">
            <form method="post" action="" enctype="multipart/form-data">
                <label for="hobby_name">Hobby:</label>
                <input type="text" id="hobby_name" name="hobby_name" required><br>

                <label for="description">Beschrijving:</label>
                <textarea id="description" name="description" required></textarea><br>

                <label for="image">Foto:</label>
                <input type="file" id="image" name="image" accept="image/*"><br>

                <button type="submit">Toevoegen</button>
            </form>
        </div>

        <div class="hobbies-list">
            <?php foreach ($hobbies as $hobby) : ?>
                <div class="hobby-item">
                    <strong>Hobby:</strong> <?php echo htmlspecialchars($hobby['hobby_name']); ?><br>
                    <strong>Beschrijving:</strong> <?php echo htmlspecialchars($hobby['description']); ?><br>
                    <?php if ($hobby['image_url']) : ?>
                        <img src="../uploads/<?php echo htmlspecialchars($hobby['image_url']); ?>" alt="<?php echo htmlspecialchars($hobby['hobby_name']); ?>" style="max-width: 200px;"><br>
                    <?php endif; ?>
                    <a href="?delete=<?php echo htmlspecialchars($hobby['id']); ?>" onclick="return confirm('Weet je zeker dat je deze hobby wilt verwijderen?');">Verwijderen</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
