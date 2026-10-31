<?php
require_once __DIR__ . '/../app/helpers.php';

$projetos = db()->query("
  SELECT p.id, p.descricao, p.publicado_em,
         (SELECT arquivo FROM projeto_midias m WHERE m.projeto_id = p.id AND m.tipo='foto' ORDER BY ordem LIMIT 1) AS capa
  FROM projetos_publicados p
  WHERE p.ativo = 1
  ORDER BY p.publicado_em DESC
  LIMIT 6
")->fetchAll();

$title = 'PRINT3D — impressão 3D sob demanda';
include __DIR__ . '/../app/header.php';
?>

<section class="hero">
    <h1>IMPRESSÃO<br><span>3D</span><br>SOB DEMANDA</h1>
    <p>Projetos únicos, materiais resistentes, acabamento caprichado. Fale com a gente no WhatsApp e tire sua ideia do
        papel.</p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="#contato">Falar no WhatsApp</a>
        <a class="btn btn-ghost" href="<?= e(base('projetos.php')) ?>">Ver projetos</a>
    </div>
</section>

<section id="possibilidades" class="bloco">
    <h2>Possibilidades de impressão</h2>
    <div class="grid-cards">
        <?php
    $cards = [
      ['PLA', 'Peças decorativas, protótipos e brinquedos.'],
      ['PETG', 'Resistente a impacto e umidade. Ideal para uso externo.'],
      ['ABS', 'Alta resistência térmica para peças funcionais.'],
      ['TPU', 'Flexível, tipo borracha. Capas e vedações.'],
      ['Grandes volumes', 'Peças até 30 cm em impressora FDM.'],
      ['Alta definição', 'Resina SLA com detalhes finos.'],
    ];
    foreach ($cards as [$t,$d]): ?>
        <article class="card">
            <h3><?= e($t) ?></h3>
            <p><?= e($d) ?></p>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<section id="projetos" class="bloco bloco-invertido">
    <h2>Projetos já feitos</h2>
    <?php if (!$projetos): ?>
    <p class="muted">Em breve novos projetos.</p>
    <?php else: ?>
    <div class="grid-projetos">
        <?php foreach ($projetos as $p): ?>
        <article class="projeto-card">
            <?php if ($p['capa']): ?>
            <img src="<?= e(base(cfg()['app']['upload_url'].'/'.$p['capa'])) ?>" alt="">
            <?php endif; ?>
            <p><?= e(mb_strimwidth($p['descricao'], 0, 120, '…')) ?></p>
            <a href="<?= e(base('projetos.php')) ?>">Ver galeria →</a>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section id="contato" class="bloco">
    <h2>Falar com os donos</h2>
    <p>Antes de abrir o WhatsApp, informe seu nome completo (mínimo duas palavras).</p>
    <div class="grid-donos">
        <?php foreach (cfg()['admins'] as $key => $dono): ?>
        <form class="dono-card" method="post" action="<?= e(base('contato.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="dono" value="<?= e($key) ?>">
            <h3><?= e($dono['nome']) ?></h3>
            <label>Seu nome completo
                <input type="text" name="nome" required minlength="5" placeholder="Nome e sobrenome">
            </label>
            <button class="btn btn-primary" type="submit">Abrir WhatsApp</button>
        </form>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/../app/footer.php'; ?>