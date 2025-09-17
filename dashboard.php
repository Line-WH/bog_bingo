<?php

/**
 * @var db $db
 */


require "settings/init.php";
session_start();

$userId   = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';

if (!$userId) {           // guard in case someone hits the page directly
  header("Location: login.php");
  exit;
}

/* TABLE NAMES — match your schema */
$TBL_PLADER  = 'bingoPlade';   // has: kortId, loginId, kortDato
$TBL_KORT    = 'bingoKort';    // has: pladeId, kortId, promptId, titel, ...
$TBL_PROMPTS = 'bingoPrompts'; // has: promptId, label

/* 1) Ensure the user has a row in bingoPlade */
$row = $db->sql(
  "SELECT kortId FROM {$TBL_PLADER} WHERE loginId = :u LIMIT 1",
  [':u' => (int)$userId]
);
if ($row) {
  $kortId = (int)$row[0]->kortId;
} else {
  $db->sql("INSERT INTO {$TBL_PLADER} (loginId) VALUES (:u)", [':u' => (int)$userId], false);
  $kortId = (int)$db->sql("SELECT LAST_INSERT_ID() AS id")[0]->id;

  // seed 24 squares from prompts
  $db->sql(
    "INSERT INTO {$TBL_KORT} (kortId, promptId)
     SELECT :k, p.promptId FROM {$TBL_PROMPTS} p",
    [':k' => $kortId],
    false
  );
}

/* 2) Handle update of a square */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pladeId'])) {
  $pladeId   = (int)$_POST['pladeId'];
  $titel     = trim($_POST['titel'] ?? '');
  $forfatter = trim($_POST['forfatter'] ?? '');
  $finished  = isset($_POST['finished']) ? 1 : 0;

  $db->sql(
    "UPDATE {$TBL_KORT}
        SET titel = :t, forfatter = :f, finished = :fin
      WHERE pladeId = :id AND kortId = :k",
    [
      ':t' => $titel,
      ':f' => $forfatter,
      ':fin' => $finished,
      ':id' => $pladeId,
      ':k'  => $kortId
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

<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100 text-center">

<?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success text-center mb-3">
        Konto oprettet!
    </div>
<?php endif;
?>

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
