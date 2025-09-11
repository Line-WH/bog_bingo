<?php
/**
 * @var db $db
 */

require "settings/init.php";
require "classes/Auth.php";
session_start();

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $error = Auth::register($db, $username, $password);

    if ($error === "") {
        // Fetch the row we just created to get loginId
        $user = Auth::getUserByUsername($db, $username);
        Auth::loginUserSession((int)$user->loginId, $user->loginNavn);

        header("Location: dashboard.php");
        exit();
    }
}
?>
<!doctype html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <title>Registrer · Bog Bingo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100 text-center">
<div class="card p-4 shadow" style="min-width:320px;max-width:420px;">
    <h2 class="mb-3">Opret Konto</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3 text-start">
            <label class="form-label">Brugernavn</label>
            <input type="text" name="username" class="form-control"
                   minlength="3" maxlength="20"
                   value="<?= htmlspecialchars($username) ?>" required>
        </div>

        <div class="mb-3 text-start">
            <label class="form-label">Adgangskode</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>

        <button class="btn btn-success w-100">Opret konto</button>
    </form>

    <p class="mt-3 mb-0">
        Har du allerede en konto? <a href="login.php">Login her</a>.
    </p>
</div>
</body>
</html>
