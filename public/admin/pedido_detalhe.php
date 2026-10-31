<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("
  SELECT p.*, c.nome_completo, c.id AS cliente_id
  FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
  WHERE p.id = ?
");
$stmt->execute([$id]);
$pedido = $stmt->fetch();
if (!$pedido) { http_response_code(404); exit('Pedido não encontrado.'); }

$comprovantes = db()->prepare("SELECT * FROM comprovantes WHERE pedido_id = ? ORDER BY criado_em DESC");
$comprovantes->execute([$id]);
$comprovantes = $comprovantes->fetchAll();

$publicado = db()->prepare("SELECT * FROM projetos_publicados WHERE pedido_id = ?");
$publicado->execute([$id]);
$publicado = $publicado->fetch();

$pagoExtrato = db()->prepare("SELECT * FROM pagamentos_extrato WHERE pedido_id = ?");
$pagoExtrato->execute([$id]);
$pagoExtrato = $pagoExtrato->fetch();

$midias = [];
if ($publicado) {
    $m = db()->prepare("SELECT * FROM projeto_midias WHERE projeto_id = ? ORDER BY ordem, id");
    $m->execute([$publicado['id']]);
    $midias = $m->fetchAll();
}

$title = 'Pedido ' . $pedido['codigo'];
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">
    <a href="<?= e(base('admin/pedidos.php')) ?>">← Pedidos</a>
    <h1><?= e($pedido['codigo']) ?></h1>
    <span class="badge status-<?= e($pedido['status']) ?>"><?= e(status_label($pedido['status'])) ?></span>
    <p class="muted">Atualizado em <?= e(date('d/m/Y H:i', strtotime($pedido['atualizado_em']))) ?></p>

    <div class="pedido-grid">
        <div>
            <h3>Cliente</h3>
            <p><?= e($pedido['nome_completo']) ?></p>

            <h3>Descrição</h3>
            <p><?= nl2br(e($pedido['descricao'])) ?></p>

            <h3>Total</h3>
            <p class="preco"><?= e(brl($pedido['valor_total'])) ?></p>

            <h3>Chave Pix</h3>
            <div class="pix-box">
                <code id="pix-key-admin"><?= e($pedido['chave_pix']) ?></code>
                <button class="btn btn-ghost" type="button" data-copy="#pix-key-admin">Copiar</button>
            </div>
        </div>

        <div>
            <h3>Ações</h3>
            <form method="post" action="<?= e(base('admin/pedido_status.php')) ?>" class="form-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>">
                <label>Alterar status
                    <select name="status">
                        <?php foreach (['aguardando_pagamento','comprovante_enviado','em_revisao','pago','em_producao','finalizado','cancelado'] as $s): ?>
                        <option value="<?= e($s) ?>" <?= $pedido['status']===$s?'selected':'' ?>>
                            <?= e(status_label($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Salvar</button>
            </form>

            <?php if (!$pagoExtrato): ?>
            <form method="post" action="<?= e(base('admin/pedido_pago.php')) ?>"
                onsubmit="return confirm('Confirmar pagamento deste pedido?')">
                <?= csrf_field() ?>
                <input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>">
                <button class="btn btn-primary" type="submit">Confirmar como pago</button>
            </form>
            <?php else: ?>
            <p class="badge status-pago">Pagamento confirmado em
                <?= e(date('d/m/Y H:i', strtotime($pagoExtrato['confirmado_em']))) ?></p>
            <?php endif; ?>

            <h3>Anexar comprovante (manual)</h3>
            <form method="post" action="<?= e(base('admin/pedido_comprovante.php')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>">
                <input type="file" name="comprovante" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                <button class="btn btn-ghost" type="submit">Enviar</button>
            </form>

            <h3>Comprovantes anexados</h3>
            <?php if (!$comprovantes): ?>
            <p class="muted">Nenhum.</p>
            <?php else: ?>
            <ul class="lista-arquivos">
                <?php foreach ($comprovantes as $c): ?>
                <li>
                    <a href="<?= e(base('admin/ver_comprovante.php?id='.$c['id'])) ?>" target="_blank" rel="noopener">
                        Abrir comprovante #<?= (int)$c['id'] ?>
                    </a>
                    <span class="muted"> (<?= e($c['enviado_por']) ?> ·
                        <?= e(date('d/m/Y H:i', strtotime($c['criado_em']))) ?>)</span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if ($pedido['status'] === 'finalizado' || $publicado): ?>
            <h3>Projeto publicado</h3>
            <?php if ($publicado): ?>
            <p>
                Status:
                <strong><?= $publicado['ativo'] ? 'Publicado' : 'Despublicado' ?></strong>
                — <a href="<?= e(base('admin/projeto_editar.php?id='.$publicado['id'])) ?>">Editar</a>
                &nbsp;|&nbsp;
                <a href="<?= e(base('admin/pedido_publicar.php?toggle='.(int)$publicado['id'])) ?>"
                    onclick="return confirm('Alternar publicação?')">
                    <?= $publicado['ativo'] ? 'Despublicar' : 'Republicar' ?>
                </a>
            </p>
            <div class="galeria-mini">
                <?php foreach ($midias as $m): ?>
                <span class="tag"><?= e($m['tipo']) ?></span>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="muted">Este pedido está finalizado. Você pode publicá-lo como projeto.</p>
            <?php endif; ?>

            <a class="btn btn-primary" href="<?= e(base('admin/projeto_editar.php?pedido_id='.$pedido['id'])) ?>">
                <?= $publicado ? 'Editar projeto' : 'Criar projeto publicado' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>