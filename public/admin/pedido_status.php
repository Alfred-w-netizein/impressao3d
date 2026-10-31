<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();
csrf_check();

$id     = (int)($_POST['pedido_id'] ?? 0);
$status = $_POST['status'] ?? '';

$validos = ['aguardando_pagamento', 'comprovante_enviado', 'em_revisao', 'pago', 'em_producao', 'finalizado', 'cancelado'];
if (!$id || !in_array($status, $validos, true)) {
    flash('err', 'Status inválido.');
    redirect('admin/pedidos.php');
}

$extra = '';
if ($status === 'finalizado') {
    $extra = ', finalizado_em = COALESCE(finalizado_em, NOW())';
}
db()->prepare("UPDATE pedidos SET status = ? $extra WHERE id = ?")->execute([$status, $id]);

flash('ok', 'Status atualizado.');
redirect('admin/pedido_detalhe.php?id=' . $id);
