<?php
require_once __DIR__ . '/../app/helpers.php';

$projetos = db()->query("
  SELECT * FROM projetos_publicados WHERE ativo = 1 ORDER BY publicado_em DESC
")->fetchAll();

$midias = [];
if ($projetos) {
    $ids  = array_column($projetos, 'id');
    $in   = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM projeto_midias WHERE projeto_id IN ($in) ORDER BY ordem, id");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $m) $midias[$m['projeto_id']][] = $m;
}

$title = 'Projetos já feitos';
include __DIR__ . '/../app/header.php';
?>
<section class="bloco">
    <h2>Projetos já feitos</h2>
    <?php if (!$projetos): ?>
        <p class="muted">Em breve novos projetos.</p>
    <?php endif; ?>

    <?php foreach ($projetos as $p): ?>
        <article class="projeto-detalhe">
            <h3>Projeto #<?= (int)$p['pedido_id'] ?></h3>
            <p><?= nl2br(e($p['descricao'])) ?></p>
            <div class="galeria">
                <?php foreach (($midias[$p['id']] ?? []) as $m): ?>
                    <?php $url = base(cfg()['app']['upload_url'] . '/' . $m['arquivo']); ?>
                    <?php if ($m['tipo'] === 'foto'): ?>
                        <a href="<?= e($url) ?>" target="_blank" rel="noopener">
                            <img src="<?= e($url) ?>" alt="foto do projeto" loading="lazy">
                        </a>
                    <?php else: ?>
                        <video controls preload="metadata" src="<?= e($url) ?>"></video>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php include __DIR__ . '/../app/footer.php'; ?>