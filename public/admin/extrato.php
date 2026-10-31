<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$periodo = $_GET['periodo'] ?? 'mes'; // semana | mes
$inicio = $periodo === 'semana'
    ? date('Y-m-d', strtotime('monday this week'))
    : date('Y-m-01');
$fim = $periodo === 'semana'
    ? date('Y-m-d', strtotime('sunday this week'))
    : date('Y-m-t');

$stmt = db()->prepare("
  SELECT pe.*, p.codigo, c.nome_completo, p.status, p.id AS pedido_ref
  FROM pagamentos_extrato pe
  JOIN pedidos p ON p.id = pe.pedido_id
  JOIN clientes c ON c.id = p.cliente_id
  WHERE DATE(pe.confirmado_em) BETWEEN ? AND ?
  ORDER BY pe.confirmado_em DESC
");
$stmt->execute([$inicio, $fim]);
$rows = $stmt->fetchAll();

$total = array_sum(array_column($rows, 'valor'));

$title = 'Extrato de pagamentos';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">
    <h1>Extrato de pagamentos</h1>
    <form method="get" class="form-inline">
        <label>Período
            <select name="periodo" onchange="this.form.submit()">
                <option value="semana" <?= $periodo==='semana'?'selected':'' ?>>Semana atual</option>
                <option value="mes" <?= $periodo==='mes'?'selected':'' ?>>Mês atual</option>
            </select>
        </label>
        <a class="btn btn-primary" href="<?= e(base('admin/extrato_exportar.php?periodo='.$periodo)) ?>">Exportar
            CSV</a>
    </form>
    <p class="muted">Período: <?= e(date('d/m/Y', strtotime($inicio))) ?> a <?= e(date('d/m/Y', strtotime($fim))) ?> —
        Total: <strong><?= e(brl($total)) ?></strong></p>

    <div class="tabela-wrap">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Comprovante</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <?php
        $comp = db()->prepare('SELECT id FROM comprovantes WHERE pedido_id = ? ORDER BY criado_em DESC LIMIT 1');
        $comp->execute([$r['pedido_ref']]);
        $compId = $comp->fetch()['id'] ?? null;
        ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i', strtotime($r['confirmado_em']))) ?></td>
                    <td><?= e($r['codigo']) ?></td>
                    <td><?= e($r['nome_completo']) ?></td>
                    <td><?= e(brl($r['valor'])) ?></td>
                    <td><?= e(status_label($r['status'])) ?></td>
                    <td>
                        <?php if ($compId): ?>
                        <a href="<?= e(base('admin/ver_comprovante.php?id='.$compId)) ?>" target="_blank"
                            rel="noopener">Ver</a>
                        <?php else: ?><span class="muted">—</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" class="muted">Nenhum pagamento confirmado no período.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>