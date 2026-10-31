<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM comprovantes WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
    http_response_code(404);
    exit('Não encontrado.');
}

$full = cfg()['app']['upload_dir'] . '/' . $c['arquivo'];
if (!is_file($full)) {
    http_response_code(404);
    exit('Arquivo ausente.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($full);
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($full) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($full);
exit;
