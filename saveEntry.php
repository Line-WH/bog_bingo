<?php
/**
 * @var db $db
 */
require "settings/init.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

// Skal være logget ind for at kunne submitte
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Ikke logget ind']);
    exit;
}

// Input felterne
$pladeId   = isset($_POST['plade_id'])   ? (int)$_POST['plade_id']   : 0;
$promptId  = isset($_POST['prompt_id'])  ? (int)$_POST['prompt_id']  : 0;
$titel     = trim($_POST['titel'] ?? '');
$forfatter = trim($_POST['forfatter'] ?? '');
$noter     = trim($_POST['noter'] ?? '');
$finished  = isset($_POST['finished']) ? 1 : 0;

if ($pladeId <= 0 || $promptId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ugyldig data']);
    exit;
}

// forsikre at pladId tilhøre bruger
$own = $db->sql("SELECT 1 FROM bingoPlade WHERE kortId = :pid AND loginId = :uid LIMIT 1", [
    ':pid' => $pladeId,
    ':uid' => $userId
]);
if (!$own) {
    echo json_encode(['status' => 'error', 'message' => 'Adgang nægtet']);
    exit;
}

// håndtere fil upload af covers
$coverPath = null;
if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['cover']['tmp_name'];
    $orig = basename($_FILES['cover']['name']);
    $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

    // Simple allowlist
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Ugyldigt filformat (tilladt: jpg, jpeg, png, webp, gif)']);
        exit;
    }

    // forsikre at uploads eksistere
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    // giver et unikt navn til filerne
    $newName   = 'cover_' . $pladeId . '_' . $promptId . '_' . time() . '.' . $ext;
    $dest      = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($tmp, $dest)) {
        echo json_encode(['status' => 'error', 'message' => 'Kunne ikke gemme filen']);
        exit;
    }

    // Store relative path for DB
    $coverPath = 'uploads/' . $newName;
}

// UPSERT into bingoKort
// Assumes a schema roughly like:
// bingoKort(pladeId INT, promptId INT, titel TEXT, forfatter TEXT, cover TEXT, noter TEXT, finished TINYINT, PRIMARY KEY(id), UNIQUE(pladeId, promptId))
$params = [
    ':pladeId'   => $pladeId,
    ':promptId'  => $promptId,
    ':titel'     => $titel,
    ':forfatter' => $forfatter,
    ':noter'     => $noter,
    ':finished'  => $finished,
];

// Build SQL depending on whether a new cover was uploaded
if ($coverPath !== null) {
    $sql = "
        INSERT INTO bingoKort (pladeId, promptId, titel, forfatter, cover, noter, finished)
        VALUES (:pladeId, :promptId, :titel, :forfatter, :cover, :noter, :finished)
        ON DUPLICATE KEY UPDATE
          titel = VALUES(titel),
          forfatter = VALUES(forfatter),
          cover = VALUES(cover),
          noter = VALUES(noter),
          finished = VALUES(finished)
    ";
    $params[':cover'] = $coverPath;
} else {
    // Don't overwrite cover if none uploaded this time
    $sql = "
        INSERT INTO bingoKort (pladeId, promptId, titel, forfatter, noter, finished)
        VALUES (:pladeId, :promptId, :titel, :forfatter, :noter, :finished)
        ON DUPLICATE KEY UPDATE
          titel = VALUES(titel),
          forfatter = VALUES(forfatter),
          noter = VALUES(noter),
          finished = VALUES(finished)
    ";
}

try {
    $db->sql($sql, $params);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'pladeId'   => $pladeId,
            'promptId'  => $promptId,
            'titel'     => $titel,
            'forfatter' => $forfatter,
            'noter'     => $noter,
            'finished'  => $finished,
            'cover'     => $coverPath, // may be null if not uploaded this time
        ]
    ]);
} catch (Throwable $e) {
    // If you DON'T have UNIQUE(pladeId, promptId), fall back to manual update/insert:
    // 1) try update; 2) if rowCount == 0, insert. (Uncomment below & remove the try above.)
    //
    // $exists = $db->sql("SELECT 1 FROM bingoKort WHERE pladeId=:pl AND promptId=:pr LIMIT 1", [
    //     ':pl' => $pladeId, ':pr' => $promptId
    // ]);
    // if ($exists) { ...UPDATE... } else { ...INSERT... }

    echo json_encode(['status' => 'error', 'message' => 'DB-fejl: ' . $e->getMessage()]);
}

