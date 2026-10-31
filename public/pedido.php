<?php
require_once __DIR__ . '/../app/helpers.php';

$codigo = $_GET['codigo'] ?? '';
$nome   = trim($_GET['nome'] ?? '');
if (!nome_valido($nome)) { flash('err','Informe nome completo.'); redirect('acompanhar.php'); }

$norm = normalize_nome($nome);
$stmt = db()->prepare("
  SELECT p.*, c.nome_completo
  FROM pedidos p JOIN clientes c ON c.id = p.cliente_id
  WHERE p.codigo = ? AND c.nome_normalizado = ?
");
$stmt->execute([$codigo, $norm]);
$pedido = $stmt->fetch();
if (!$pedido) { http_response_code(404); flash('err','Pedido não encontrado para este nome.'); redirect('acompanhar.php'); }

$comprovantes = db()->prepare("SELECT * FROM comprovantes WHERE pedido_id = ? ORDER BY criado_em DESC");
$comprovantes->execute([$pedido['id']]);
$comprovantes = $comprovantes->fetchAll();

$title = 'Pedido ' . $pedido['codigo'];
include __DIR__ . '/../app/header.php';
?>
<section class="bloco">
    <p class="muted">Pedido</p>
    <h2><?= e($pedido['codigo']) ?></h2>
    <span class="badge status-<?= e($pedido['status']) ?>"><?= e(status_label($pedido['status'])) ?></span>

    <div class="pedido-grid">
        <div>
            <h3>Descrição</h3>
            <p><?= nl2br(e($pedido['descricao'])) ?></p>
            <h3>Total</h3>
            <p class="preco"><?= e(brl($pedido['valor_total'])) ?></p>
            <h3>Chave Pix</h3>
            <div class="pix-box">
                <code id="pix-key"><?= e($pedido['chave_pix']) ?></code>
                <button class="btn btn-ghost" type="button" data-copy="#pix-key">Copiar</button>
            </div>
        </div>

        <div>
            <h3>Comprovante</h3>
            <?php if ($comprovantes): ?>
            <ul class="lista-arquivos">
                <?php foreach ($comprovantes as $c): ?>
                <li>
                    <span class="muted"><?= e($c['enviado_por']) ?> ·
                        <?= e(date('d/m/Y H:i', strtotime($c['criado_em']))) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="muted">Nenhum comprovante enviado.</p>
            <?php endif; ?>

            <form method="post" action="<?= e(base('upload_comprovante.php')) ?>" enctype="multipart/form-data"
                class="form-upload">
                <?= csrf_field() ?>
                <input type="hidden" name="pedido_codigo" value="<?= e($pedido['codigo']) ?>">
                <input type="hidden" name="nome" value="<?= e($nome) ?>">
                <label>Anexar comprovante (jpg, png, webp ou pdf — até 5 MB)
                    <input type="file" name="comprovante" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                </label>
                <button class="btn btn-primary" type="submit">Enviar comprovante</button>
            </form>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../app/footer.php'; ?>