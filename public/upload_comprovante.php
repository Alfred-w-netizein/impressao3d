<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/upload.php';
csrf_check();

$codigo = $_POST['pedido_codigo'] ?? '';
$nome   = trim($_POST['nome'] ?? '');
if (!nome_valido($nome)) {
    flash('err', 'Nome inválido.');
    redirect('acompanhar.php');
}

$norm = normalize_nome($nome);
$stmt = db()->prepare("
  SELECT p.id FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
  WHERE p.codigo = ? AND c.nome_normalizado = ?
");
$stmt->execute([$codigo, $norm]);
$pedido = $stmt->fetch();
if (!$pedido) {
    flash('err', 'Pedido não encontrado.');
    redirect('acompanhar.php?nome=' . urlencode($nome));
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
    redirect('pedido.php?codigo=' . urlencode($codigo) . '&nome=' . urlencode($nome));
}

db()->prepare('INSERT INTO comprovantes (pedido_id, arquivo, enviado_por) VALUES (?,?,"cliente")')
    ->execute([$pedido['id'], $path]);

db()->prepare("UPDATE pedidos SET status='comprovante_enviado' WHERE id = ? AND status = 'aguardando_pagamento'")
    ->execute([$pedido['id']]);

flash('ok', 'Comprovante enviado. Aguarde a revisão.');
redirect('pedido.php?codigo=' . urlencode($codigo) . '&nome=' . urlencode($nome));
