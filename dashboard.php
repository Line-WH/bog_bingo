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

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100 text-center">



<script src="assets/js/app.js"></script>
</body>
<?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success text-center mb-3">
        Konto oprettet!
    </div>
<?php endif; ?>

</html>
