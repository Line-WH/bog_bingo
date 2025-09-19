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

$prompts = $db->sql("SELECT promptId, label FROM {$TBL_PROMPTS} ORDER BY promptId"
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
    </div>

    <?php foreach ($prompts as $prompt): ?>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <?= htmlspecialchars($prompt->label) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</body>
</html>

