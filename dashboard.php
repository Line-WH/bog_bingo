<?php

/**
 * @var db $db
 */

require "settings/init.php";
session_start();
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">

    <title>Bog Bingo - skrivebord</title>

    <meta name="robots" content="All">
    <meta name="author" content="Udgiver">
    <meta name="copyright" content="Information om copyright">

    <link href="css/styles.css" rel="stylesheet" type="text/css">

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100 text-center">

<?php if (isset($_GET['registered'])): ?>
<div class="alert alert-success text-center mb-3">
    Konto oprettet!
</div>
<?php endif; ?>

<?php
$cards = [
        ["titel" => "kort 1", "text" => " her skal der stå noget tekst", "link" =>"#"],
        ["titel" => "kort 2", "text" => " her skal der stå noget tekst", "link" =>"#"],
        ["titel" => "kort 3", "text" => " her skal der stå noget tekst", "link" =>"#"]

];
while (count($cards) < 24) {
    $i = count($cards) + 1;
    $cards[] = ["titel" => "kort $i", "text" => "beskrivelse for kort $i", "link" =>"#"];
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 m-0">Bog Bingo</h1>
        <a class="btn btn-outline-secondary btn-sm" href="logout.php">Log ud</a>
    </div>

    <!-- Progress placeholder (JS can update later) -->
    <div class="mb-4">
        <div class="d-flex justify-content-between small">
            <span>Fremgang: <span id="doneCount">0</span>/24</span>
            <span id="percentLabel">0%</span>
        </div>
        <div class="progress">
            <div id="progressBar" class="progress-bar" style="width:0%"></div>
        </div>
    </div>

    <!-- Responsive grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
        <?php foreach ($cards as $c): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($c['titel']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($c['text']) ?></p>
                        <a href="<?= htmlspecialchars($c['link']) ?>" class="btn btn-primary">Åbn</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>



<script src="assets/js/app.js"></script>
</body>


</html>
