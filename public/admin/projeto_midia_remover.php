<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM projeto_midias WHERE id = ?');
$stmt->execute([$id]);
$m = $stmt->fetch();
if (!$m) {
    flash('err', 'Mídia não encontrada.');
    redirect('admin/projetos_admin.php');
}

$projetoStmt = db()->prepare('SELECT id FROM projetos_publicados WHERE id = ?');
$projetoStmt->execute([$m['projeto_id']]);
$projetoId = (int)$projetoStmt->fetch()['id'];

$full = cfg()['app']['upload_dir'] . '/' . $m['arquivo'];
if (is_file($full)) @unlink($full);
db()->prepare('DELETE FROM projeto_midias WHERE id = ?')->execute([$id]);

flash('ok', 'Mídia removida.');
redirect('admin/projeto_editar.php?id=' . $projetoId);
