<?php
/**
 * @var db $db
 */

require "settings/init.php";
require "classes/Auth.php";

session_start();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $user = Auth::getUserByUsername($db, $username);

    if ($user && password_verify($password, $user->loginKodeord)) {
        Auth::loginUserSession((int)$user->loginId, $user->loginNavn);
        header("Location: dashboard.php");
        exit();
    } else {
        $msg = "Forkert brugernavn eller adgangskode.";
    }
}
?>
<!-- render form + show <?= htmlspecialchars($msg) ?> if present -->
