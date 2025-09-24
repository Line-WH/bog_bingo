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

    if ($username === "" || $password === "") {
        $msg = "Udfyld både brugernavn og adgangskode.";
    } else {
        $user = Auth::getUserByUsername($db, $username); // henter fra `login`

        if ($user && password_verify($password, $user->loginKodeord)) {
            Auth::loginUserSession((int)$user->loginId, $user->loginNavn);
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "Forkert brugernavn eller adgangskode.";
        }
    }
}
?>
<!doctype html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <title>Bog Bingo - Log ind</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center text-center vh-100">
<div class="card p-4 shadow" style="min-width:320px;max-width:420px;">
    <h2 class="mb-3">Log ind</h2>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3 text-start">
            <label class="form-label">Brugernavn</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label">Adgangskode</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-success w-100">Log ind</button>
    </form>

    <p class="mt-3 mb-0">
        Har du ikke en konto? <a href="register.php">Opret en her</a>.
    </p>
</div>
</body>
</html>
