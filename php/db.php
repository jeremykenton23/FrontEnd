<?php
$host = 'localhost';      // Database host
$db   = 'frontend';       // Database naam
$user = 'root';           // Database gebruiker
$pass = 'Softwaredevelopment2023!';  // Database wachtwoord

try {
    // Maak verbinding met de database
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Toon een foutmelding als de verbinding niet kan worden gemaakt
    die("Fout met verbinden naar de database: " . $e->getMessage());
}
?>
