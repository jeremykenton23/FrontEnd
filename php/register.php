<?php
require 'db.php'; // Verbindt met de database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Haal de ingevoerde gegevens op
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $postal_code = $_POST['postal_code'];
    $house_number = $_POST['house_number'];
    $birthdate = $_POST['birthdate'];

    // Stap 1: Valideer het e-mailadres
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Ongeldig e-mailadres.";
        exit;
    }

    // Stap 2: Hash het wachtwoord
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Stap 3: Voeg de gegevens toe aan de database met een Prepared Statement
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, postal_code, house_number, birthdate) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Voer de query uit met de gehashte wachtwoord en andere gebruikersgegevens
        if ($stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $postal_code, $house_number, $birthdate])) {
            // Redirect naar de inlogpagina na succesvolle registratie
            header("Location: ../index.html?success=registered");
            exit();
        } else {
            echo "Registratie mislukt.";
        }
    } catch (PDOException $e) {
        // Foutafhandeling voor databasefouten
        echo "Fout bij registratie: " . $e->getMessage();
    }
}
?>
