<?php

/**
 * @var db $db
 */

require "settings/init.php";
session_start();

$userId   = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';
if (!$userId) {           // Sikkerhed for hvis der ikke kan findes en bruger, sendes tilbage til forside
  header("Location: login.php");
  exit;
}

/*Tabelnavne */
$TBL_PLADER  = 'bingoPlade';   // kortId, loginId, kortDato
$TBL_KORT    = 'bingoKort';    // pladeId, kortId, promptId, titel, ...
$TBL_PROMPTS = 'bingoPrompts'; // promptId, label

/* sørger for at bruger har en row henter kortId ned */
$row = $db->sql("SELECT kortId FROM {$TBL_PLADER} WHERE loginId = :loginId LIMIT 1", [':loginId' => $userId]);
$kortId = $row[0]->kortId;

/* 2) Handle update of a square */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pladeId'])) {
  $pladeId   = $_POST['pladeId'];
  $titel     = trim($_POST['titel'] ?? '');
  $forfatter = trim($_POST['forfatter'] ?? '');
  $finished  = isset($_POST['finished']) ? 1 : 0;

  $db->sql(
    "UPDATE {$TBL_KORT}
        SET titel = :t, forfatter = :f, finished = :fin
      WHERE pladeId = :pId AND kortId = :kId",
    [
      ':t' => $titel,
      ':f' => $forfatter,
      ':fin' => $finished,
      ':pId' => $pladeId,
      ':kId'  => $kortId
    ],
    false
  );

  header("Location: dashboard.php?saved=1");
  exit;
}

/* 3) Load squares */
$squares = $db->sql(
  "SELECT k.pladeId, k.titel, k.forfatter, k.finished, p.label
     FROM {$TBL_KORT} k
     JOIN {$TBL_PROMPTS} p USING (promptId)
    WHERE k.kortId = :k
    ORDER BY p.promptId",
  [':k' => $kortId]
);
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

<body class="bg-light">

<?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success text-center mb-3">
        Konto oprettet!
    </div>
<?php endif;
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 m-0">Bog Bingo</h1>
        <a class="btn btn-outline-secondary btn-sm" href="logout.php">Log ud</a>
    </div>

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





    <!-- Responsive grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-1">
        <?php foreach ($cards as $c): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($c['titel']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($c['text']) ?></p>
                        <a href="<?= htmlspecialchars($c['link']) ?>" class="btn btn-success">Åbn</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>



<script src="assets/js/app.js"></script>

</body>


</html>
