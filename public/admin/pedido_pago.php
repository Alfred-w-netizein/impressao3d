<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();
csrf_check();

$id = (int)($_POST['pedido_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM pedidos WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) {
    flash('err', 'Pedido não encontrado.');
    redirect('admin/pedidos.php');
}

$ja = db()->prepare('SELECT id FROM pagamentos_extrato WHERE pedido_id = ?');
$ja->execute([$id]);
if ($ja->fetch()) {
    flash('err', 'Pagamento já confirmado.');
    redirect('admin/pedido_detalhe.php?id=' . $id);
}

db()->prepare('INSERT INTO pagamentos_extrato (pedido_id, valor, confirmado_por_admin_id) VALUES (?,?,?)')
    ->execute([$id, $p['valor_total'], $_SESSION['admin_id']]);

db()->prepare("UPDATE pedidos SET status='pago', pago_em=NOW() WHERE id = ?")->execute([$id]);

flash('ok', 'Pagamento confirmado e lançado no extrato.');
redirect('admin/pedido_detalhe.php?id=' . $id);
