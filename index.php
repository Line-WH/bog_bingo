<?php
/**
 * @var db $db
 */

require "settings/init.php";
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    
    <title>Bog Bingo</title>
    
    <meta name="robots" content="All">
    <meta name="author" content="Udgiver">
    <meta name="copyright" content="Information om copyright">
    
    <link href="css/styles.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100 text-center">

    <div class="container">
        <h1 class="display-4 mb-4">📚 Welcome to Book Bingo!</h1>
        <p class="lead mb-5">Gamify your reading goals — one square at a time.</p>

        <div class="d-grid gap-3 col-6 mx-auto">
                <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                <a href="register.php" class="btn btn-outline-secondary">Register</a>
        </div>
    </div>


    <script src="assets/js/app.js"></script>


</body>
</html>
