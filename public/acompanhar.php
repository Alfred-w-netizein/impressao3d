<?php
require_once __DIR__ . '/../app/helpers.php';

$nome = trim($_GET['nome'] ?? '');
$pedidos = [];
if ($nome !== '' && nome_valido($nome)) {
    $norm = normalize_nome($nome);
    $stmt = db()->prepare("
      SELECT p.* FROM pedidos p
      JOIN clientes c ON c.id = p.cliente_id
      WHERE c.nome_normalizado = ?
      ORDER BY p.criado_em DESC
    ");
    $stmt->execute([$norm]);
    $pedidos = $stmt->fetchAll();
}

$title = 'Acompanhar pedido';
include __DIR__ . '/../app/header.php';
?>
<section class="bloco">
    <h2>Acompanhar pedido</h2>
    <form method="get" class="form-inline">
        <label>Nome completo
            <input type="text" name="nome" required minlength="5" value="<?= e($nome) ?>">
        </label>
        <button class="btn btn-primary" type="submit">Ver meus pedidos</button>
    </form>

    <?php if ($nome !== '' && !nome_valido($nome)): ?>
    <p class="err">Informe nome completo com pelo menos duas palavras.</p>
    <?php elseif ($nome !== ''): ?>
    <?php if (!$pedidos): ?>
    <p class="muted">Nenhum pedido encontrado para este nome.</p>
    <?php else: ?>
    <ul class="lista-pedidos">
        <?php foreach ($pedidos as $p): ?>
        <li class="pedido-item">
            <div>
                <strong><?= e($p['codigo']) ?></strong>
                <span class="badge status-<?= e($p['status']) ?>"><?= e(status_label($p['status'])) ?></span>
                <p class="muted"><?= e(mb_strimwidth($p['descricao'],0,100,'…')) ?></p>
            </div>
            <a class="btn btn-ghost"
                href="<?= e(base('pedido.php?codigo='.urlencode($p['codigo']).'&nome='.urlencode($nome))) ?>">Detalhes</a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../app/footer.php'; ?>