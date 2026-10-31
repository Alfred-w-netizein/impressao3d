<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/upload.php';
admin_require();
csrf_check();

$id = (int)($_POST['pedido_id'] ?? 0);
if (!$id) {
    flash('err', 'Pedido inválido.');
    redirect('admin/pedidos.php');
}

try {
    $path = upload_file(
        $_FILES['comprovante'],
        'comprovantes',
        cfg()['upload']['doc_exts'],
        cfg()['upload']['max_image_bytes']
    );
} catch (Throwable $e) {
    flash('err', 'Falha no upload: ' . $e->getMessage());
    redirect('admin/pedido_detalhe.php?id=' . $id);
}

db()->prepare('INSERT INTO comprovantes (pedido_id, arquivo, enviado_por) VALUES (?,?,"admin")')
    ->execute([$id, $path]);

flash('ok', 'Comprovante anexado.');
redirect('admin/pedido_detalhe.php?id=' . $id);
