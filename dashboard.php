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
$TBL_PLADER  = 'bingoPlade';   // kortId og kortDato og henter loginId
$TBL_KORT    = 'bingoKort';    // KortId, men inheriter promptId og pladeId (pladeId linker til brugerens board)
$TBL_PROMPTS = 'bingoPrompts'; // promptId, label

/* 1) Sørg for at der findes en plade til brugeren (kræver UNIQUE(loginId) i DB) */
$db->sql(
        "INSERT INTO {$TBL_PLADER} (loginId)
   VALUES (:loginId)
   ON DUPLICATE KEY UPDATE loginId = VALUES(loginId)",
        [':loginId' => $userId]
);

/* 2) Hent plade-id (kortId i bingoPlade) ROBUST */
$rows = $db->sql(
        "SELECT kortId FROM {$TBL_PLADER} WHERE loginId = :loginId LIMIT 1",
        [':loginId' => $userId]
);

$pladeId = null;
if ($rows) {
    $first = is_array($rows) ? reset($rows) : $rows;
    $pladeId = is_object($first) ? ($first->kortId ?? null) : ($first['kortId'] ?? null);
}

if ($pladeId === null) {
    // Pæn fallback hvis noget er helt galt
    die("Kunne ikke oprette eller hente din bingoplade. Prøv at genindlæse siden.");
}

//henter kort til PladeId
$squares = $db->sql(
        "SELECT k.pladeId, k.titel, k.forfatter, k.finished, p.promptId, p.label
     FROM {$TBL_KORT} k
     JOIN {$TBL_PROMPTS} p USING (promptId)
    WHERE k.pladeId = :pid
    ORDER BY p.promptId",
        [':pid' => $pladeId]
);

/* Prompts til at vise knapper/kort */
$prompts = $db->sql("SELECT promptId, label FROM {$TBL_PROMPTS} ORDER BY promptId");

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
        <div class="alert alert-success text-center mb-3">Konto oprettet!</div>
    <?php endif; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 m-0">Bog Bingo</h1>
            <a class="btn btn-outline-secondary btn-sm" href="logout.php">Log ud</a>
        </div>
    </div>

    <?php
    $byPrompt = [];
    foreach ($squares as $row) {
        $byPrompt[$row->promptId] = $row;
    }
    ?>

    <div class="container py-4">
        <div class="row row-cols-4 g-2">
            <?php foreach ($prompts as $prompt):
                $p = $byPrompt[$prompt->promptId] ?? null;
                ?>
                <div class="col">
                    <div class="card h-100"
                         role="button"
                         style="cursor:pointer"
                         data-bs-toggle="modal"
                         data-bs-target="#cardModal"
                         data-prompt-id="<?= $prompt->promptId ?>"
                         data-prompt-label="<?= htmlspecialchars($prompt->label) ?>"
                            <?php if ($p): ?>
                                data-title="<?= htmlspecialchars($p->titel ?? '') ?>"
                                data-author="<?= htmlspecialchars($p->forfatter ?? '') ?>"
                                data-finished="<?= (int)($p->finished ?? 0) ?>"
                            <?php endif; ?>
                    >
                        <div class="card-body text-center">
                            <?= htmlspecialchars($prompt->label) ?>
                        </div>
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

                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
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

                        <div class="mt-3">
                            <label class="form-label">Noter</label>
                            <textarea name="noter" id="cmNotes" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="">
                            <label class="form-label">Færdig</label>
                            <input type="checkbox" name="finished" id="cmFin" class="">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" name="">
                            Gem
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        /*henter titel fra kort og tager med i modal*/
            document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('cardModal');

            modalEl.addEventListener('show.bs.modal', (e) => {
            const card = e.relatedTarget;
            if (!card) return;

            document.getElementById('cmPromptLabel').textContent = card.dataset.promptLabel || '';
            document.getElementById('cmPromptId').value = card.dataset.promptId || '';

            // Prefill
            document.getElementById('cmTitle').value  = card.dataset.title  ?? '';
            document.getElementById('cmAuthor').value = card.dataset.author ?? '';
            document.getElementById('cmNotes').value  = '';
            document.getElementById('cmFin').checked  = card.dataset.finished === '1';
        });

    const form = document.getElementById('modalForm');
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                let titel = document.getElementsByName('titel'); //er lets fordi de kan udfyldes og ændres
                let forfatter = document.getElementsByName('forfatter');
                let cover = document.getElementsByName('cover');
                let noter = document.getElementsByName('noter');
                let finished = document.getElementsByName('finished');

                //gem til database


            })
        })
    </script>
</body>
</html>
