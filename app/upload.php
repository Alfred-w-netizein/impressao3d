<?php
require_once __DIR__ . '/helpers.php';

function upload_file(array $file, string $subdir, array $allowedExts, int $maxBytes): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload (' . ($file['error'] ?? '?') . ').');
    }
    if ($file['size'] > $maxBytes) throw new RuntimeException('Arquivo grande demais.');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) throw new RuntimeException('Extensão não permitida.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $okMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
    ];
    if (!isset($okMimes[$ext]) || $okMimes[$ext] !== $mime) {
        throw new RuntimeException('MIME inválido para a extensão.');
    }
    $dir = cfg()['app']['upload_dir'] . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Não foi possível salvar.');
    }
    return $subdir . '/' . $name;
}
