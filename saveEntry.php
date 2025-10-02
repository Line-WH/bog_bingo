<?php
require "settings/init.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) { echo json_encode(['status'=>'error','message'=>'Ikke logget ind']); exit; }

$pladeId   = (int)($_POST['plade_id']  ?? 0);
$promptId  = (int)($_POST['prompt_id'] ?? 0);
$titel     = trim($_POST['titel']      ?? '');
$forfatter = trim($_POST['forfatter']  ?? '');
$noter     = trim($_POST['noter']      ?? '');
$finished  = isset($_POST['finished']) ? 1 : 0;

if ($pladeId <= 0 || $promptId <= 0) {
    echo json_encode(['status'=>'error','message'=>"Ugyldig data (plade_id={$pladeId}, prompt_id={$promptId})"]); exit;
}

$own = $db->sql("SELECT 1 FROM bingoPlade WHERE kortId=:pid AND loginId=:uid LIMIT 1", [
    ':pid'=>$pladeId, ':uid'=>$userId
]);
if (!$own) { echo json_encode(['status'=>'error','message'=>'Adgang nægtet']); exit; }

$sql = "
  INSERT INTO bingoKort (pladeId, promptId, titel, forfatter, noter, finished)
  VALUES (:pladeId, :promptId, :titel, :forfatter, :noter, :finished)
  ON DUPLICATE KEY UPDATE
    titel     = VALUES(titel),
    forfatter = VALUES(forfatter),
    noter     = VALUES(noter),
    finished  = VALUES(finished)
";

$params = [
    ':pladeId'=>$pladeId,
    ':promptId'=>$promptId,
    ':titel'=>$titel,
    ':forfatter'=>$forfatter,
    ':noter'=>$noter,
    ':finished'=>$finished
];

try {
    $db->sql($sql, $params);
    echo json_encode([
        'status'=>'success',
        'data'=>[
            'pladeId'=>$pladeId,
            'promptId'=>$promptId,
            'titel'=>$titel,
            'forfatter'=>$forfatter,
            'noter'=>$noter,
            'finished'=>$finished
        ]
    ]);
} catch (Throwable $e) {
    echo json_encode(['status'=>'error','message'=>'DB-fejl: '.$e->getMessage()]);
}

