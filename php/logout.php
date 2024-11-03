<?php
session_start(); // Start de sessie

// Verwijder alle sessievariabelen
$_SESSION = array();

// Verwijder de sessiecookie als deze bestaat
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Vernietig de sessie
session_destroy();

// Stuur de gebruiker door naar de inlogpagina
header('Location: ../index.html'); // Of 'login.php' als dat de juiste inlogpagina is
exit();
?>
