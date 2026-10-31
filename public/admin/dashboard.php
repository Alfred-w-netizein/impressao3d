<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$counts = db()->query("SELECT status, COUNT(*) n FROM pedidos GROUP BY status")->fetchAll();
$map = [];
foreach ($counts as $c) $map[$c['status']] = (int)$c['n'];

$totalPago = db()->query("SELECT COALESCE(SUM(valor),0) v FROM pagamentos_extrato")->fetch()['v'];
$pendentes = (int)($map['comprovante_enviado'] ?? 0) + (int)($map['em_revisao'] ?? 0);

$title = 'Dashboard';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">
    <h1>Dashboard</h1>
    <div class="grid-status">
        <?php foreach (['aguardando_pagamento', 'comprovante_enviado', 'em_revisao', 'pago', 'em_producao', 'finalizado', 'publicado', 'cancelado'] as $s): ?>
            <div class="status-card status-<?= e($s) ?>">
                <span><?= e(status_label($s)) ?></span>
                <strong><?= (int)($map[$s] ?? 0) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="resumo">
        <div class="resumo-item">
            <span>Total confirmado em pagamentos</span>
            <strong><?= e(brl($totalPago)) ?></strong>
        </div>
        <div class="resumo-item">
            <span>Comprovantes aguardando revisão</span>
            <strong><?= $pendentes ?></strong>
        </div>
    </div>

    <a class="btn btn-primary" href="<?= e(base('admin/pedido_novo.php')) ?>">+ Novo pedido</a>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>