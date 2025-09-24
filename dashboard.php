<?php
/**
 * @var db $db
 */

require "settings/init.php";
session_start();

$userId   = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';
if (!$userId) {  // Hvis der ikke kan findes en bruger, sendes tilbage til login
    header("Location: login.php");
    exit;
}

/* Tabelnavne */
$TBL_PLADER  = 'bingoPlade';   // kortId, loginId, kortDato
$TBL_KORT    = 'bingoKort';    // pladeId, kortId, promptId, titel, ...
$TBL_PROMPTS = 'bingoPrompts'; // promptId, label

/* Sørger for at bruger har en row og henter kortId ned */
$row = $db->sql(
        "SELECT kortId FROM {$TBL_PLADER} WHERE loginId = :loginId LIMIT 1",
        [':loginId' => $userId]
);
$kortId = $row[0]->kortId ?? null;                 // <-- undgå undefined array key

if ($kortId === null) {                            // <-- opret plade hvis ingen
    $db->sql(
            "INSERT INTO {$TBL_PLADER} (loginId) VALUES (:loginId)",
            [':loginId' => $userId]
    );
    $row = $db->sql(
            "SELECT kortId FROM {$TBL_PLADER} WHERE loginId = :loginId ORDER BY kortId DESC LIMIT 1",
            [':loginId' => $userId]
    );
    $kortId = $row[0]->kortId ?? 0;                  // fallback så efterfølgende SELECT ikke fejler
}

/* Opdatering af card modals */
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
                    ':t'   => $titel,
                    ':f'   => $forfatter,
                    ':fin' => $finished,
                    ':pId' => $pladeId,
                    ':kId' => $kortId
            ],
            false
    );

    header("Location: dashboard.php?saved=1");
    exit;
}

/* Loader individuelle kort */
$squares = $db->sql(
        "SELECT k.pladeId, k.titel, k.forfatter, k.finished, p.label
     FROM {$TBL_KORT} k
     JOIN {$TBL_PROMPTS} p USING (promptId)
    WHERE k.kortId = :k
    ORDER BY p.promptId",
        [':k' => $kortId]
);

/* Giv tomt array som 2. parameter (fjerner deprecated-warning) */
$prompts = $db->sql(
        "SELECT promptId, label FROM {$TBL_PROMPTS} ORDER BY promptId",
        []
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="bg-light">

<?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success text-center mb-3">
        Konto oprettet!
    </div>
<?php endif; ?>

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
                 style="cursor:pointer"
                 data-bs-toggle="modal"
                 data-bs-target="#cardModal"
                 data-prompt-id="<?= $prompt->promptId ?>"
                 data-prompt-label="<?= htmlspecialchars($prompt->label) ?>"
                 data-prompt-name="<?= htmlspecialchars($prompt->name ?? '') ?>">
                <div class="card-body text-center">
                    <?= htmlspecialchars($prompt->label) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="cardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="modalForm" method="post" action="saveEntry.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="cmPromptLabel"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="prompt_id" id="cmPromptId">
                <input type="hidden" name="action" id="cmAction" value="save">

                <div class="mb-3">
                    <label for="cmTitle" class="form-label">Titel</label>
                    <input name="titel" id="cmTitle" class="form-control" maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="cmAuthor" class="form-label">Forfatter</label>
                    <input name="forfatter" id="cmAuthor" class="form-control" maxlength="255">
                </div>

                <div class="row g-2">
                    <div class="col">
                        <label for="cmCover" class="form-label">Cover</label>
                        <input type="file" name="cover" id="cmCover" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Noter</label>
                    <textarea name="noter" id="cmNotes" class="form-control" rows="3"></textarea>
                </div>

                <div class="">
                    <label class="form-label">Færdig</label>
                    <input type="checkbox" name="finished" id="cmFin" class="">
                </div>
            </div>

            <div class="d-flex gap-2 p-3 pt-0">
                <button type="submit" class="btn btn-success">Gem</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* henter titel fra kort og tager med i modal */
    document.addEventListener('DOMContentLoaded', () => {
        const modalElement = document.querySelector('#cardModal');

        modalElement.addEventListener('show.bs.modal', (e) => {
            const trigger = e.relatedTarget; // clicked card
            if (!trigger) return;
            const label = trigger.getAttribute('data-prompt-label');
            const id = trigger.getAttribute('data-prompt-id');
            document.querySelector('#cmPromptLabel').textContent = label;
            document.querySelector('#cmPromptId').value = id;
        });

        const form = document.getElementById('modalForm');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            // her kan du sende formularen via fetch/AJAX hvis ønsket
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
