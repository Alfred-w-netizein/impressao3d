<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$status = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($status !== '' && array_key_exists($status, [
    'aguardando_pagamento' => 1,
    'comprovante_enviado' => 1,
    'em_revisao' => 1,
    'pago' => 1,
    'em_producao' => 1,
    'finalizado' => 1,
    'publicado' => 1,
    'cancelado' => 1
])) {
    $where = 'WHERE p.status = ?';
    $params[] = $status;
}

$stmt = db()->prepare("
  SELECT p.*, c.nome_completo
  FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
  $where
  ORDER BY p.criado_em DESC
  LIMIT 300
");
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

$title = 'Pedidos';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">

    <h1>Pedidos</h1>
    <a class="btn btn-primary" href="<?= e(base('admin/pedido_novo.php')) ?>">+ Novo pedido</a>
    <form method="get" class="form-inline">
        <label>Filtrar por status
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach (['aguardando_pagamento', 'comprovante_enviado', 'em_revisao', 'pago', 'em_producao', 'finalizado', 'publicado', 'cancelado'] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <div class="tabela-wrap">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Criado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><strong><?= e($p['codigo']) ?></strong></td>
                        <td><?= e($p['nome_completo']) ?></td>
                        <td><?= e(brl($p['valor_total'])) ?></td>
                        <td><span class="badge status-<?= e($p['status']) ?>"><?= e(status_label($p['status'])) ?></span>
                        </td>
                        <td><?= e(date('d/m/Y H:i', strtotime($p['criado_em']))) ?></td>
                        <td><a class="btn btn-ghost"
                                href="<?= e(base('admin/pedido_detalhe.php?id=' . $p['id'])) ?>">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pedidos): ?>
                    <tr>
                        <td colspan="6" class="muted">Nenhum pedido.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>