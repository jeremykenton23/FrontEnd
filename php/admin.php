<?php
session_start();
require '../php/db.php';

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Controleer of de gebruiker een beheerder is
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] == 0) {
    die("Toegang geweigerd. Alleen beheerders hebben toegang tot deze pagina.");
}

// Verwerken van acties (deactiveren, activeren, verwijderen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($user_id && $action) {
        try {
            switch ($action) {
                case 'deactivate':
                    $stmt = $pdo->prepare("UPDATE users SET status = 0 WHERE id = ?");
                    break;
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE users SET status = 1 WHERE id = ?");
                    break;
                case 'delete':
                    // Eerst verwijderen we de werkervaring van de gebruiker
                    $stmt = $pdo->prepare("DELETE FROM work_experience WHERE user_id = ?");
                    $stmt->execute([$user_id]);

                    // Dan verwijderen we de gebruiker zelf
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    break;
                default:
                    throw new Exception("Ongeldige actie.");
            }
            $stmt->execute([$user_id]);
            header('Location: admin.php');
            exit();
        } catch (PDOException $e) {
            die("Fout bij het verwerken van de actie: " . $e->getMessage());
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}

// Haal alle gebruikers op
try {
    $stmt = $pdo->prepare("SELECT id, username, email, status FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Fout bij het ophalen van gebruikers: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gebruikers Beheren</title>
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
    <h2>Beheer Gebruikers</h2>
    <table>
        <thead>
        <tr>
            <th>Gebruikersnaam</th>
            <th>Email</th>
            <th>Status</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo $user['status'] ? 'Actief' : 'Deactief'; ?></td>
                <td>
                    <?php if ($user['status']) : ?>
                        <form method="post" action="">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <button type="submit" name="action" value="deactivate" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt deactiveren?')">Deactiveren</button>
                        </form>
                    <?php else : ?>
                        <form method="post" action="">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <button type="submit" name="action" value="activate" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt activeren?')">Activeren</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                        <button type="submit" name="action" value="delete" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">Verwijderen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
