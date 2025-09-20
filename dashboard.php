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

/* opdatering af card modals */
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

    <div class="container py-4">
        <div class="row row-cols-4">
            <?php foreach ($prompts as $prompt): ?>
                    <div class="card g-2 h-100"
                         role="button"
                         data-bs-toggle="modal"
                         data-bs-target="#cardModal"
                         data-prompt-id="$prompt->promptId" data-prompt-label="<?= htmlspecialchars($prompt->label) ?>">
                        <div class="card-body text-center">
                            <?= htmlspecialchars($prompt->label) ?>
                        </div>
                    </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="cardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="saveEntry.php">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="cmPromptLabel">Prompt</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="prompt_id" id="cmPromptId">
                    <input type="hidden" name="action" id="cmAction" value="save">

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input name="title" id="cmTitle" class="form-control" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <input name="author" id="cmAuthor" class="form-control" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pages</label>
                        <input type="number" name="pages" id="cmPages" class="form-control" min="1">
                    </div>

                    <div class="row g-2">
                        <div class="col">
                            <label class="form-label">Started</label>
                            <input type="date" name="started_at" id="cmStarted" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Finished</label>
                            <input type="date" name="finished_at" id="cmFinished" class="form-control">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="cmNotes" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"
                                onclick="document.querySelector('cmAction').value='save'">
                            Save / Update
                        </button>

                        <button type="submit" class="btn btn-success"
                                onclick="document.querySelector('cmAction').value='finish'">
                            Mark as finished
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

