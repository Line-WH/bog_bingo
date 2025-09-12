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
<?php
$card = [
        ["titel" => "kort 1", "text" => " her skal der stå noget tekst", "link" =>"#"]
        ["titel" => "kort 2", "text" => " her skal der stå noget tekst", "link" =>"#"]
     ["titel" => "kort 3", "text" => " her skal der stå noget tekst", "link" =>"#"]

];
?>


<div class="row">
    <?php foreach ($cards as $card): ?>
    <div class="col-sm-6 mb-3 mb-sm-0">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Special title treatment</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Special title treatment</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>


<script src="assets/js/app.js"></script>
</body>
<?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success text-center mb-3">
        Konto oprettet!
    </div>
<?php endif; ?>

</html>
