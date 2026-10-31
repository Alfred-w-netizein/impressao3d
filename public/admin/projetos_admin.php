<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$rows = db()->query("
  SELECT pr.*, p.codigo
  FROM projetos_publicados pr JOIN pedidos p ON p.id = pr.pedido_id
  ORDER BY pr.publicado_em DESC
")->fetchAll();

$title = 'Projetos publicados';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">
    <h1>Projetos publicados</h1>
    <div class="tabela-wrap">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Publicado em</th>
                    <th>Ativo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['codigo']) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($r['publicado_em']))) ?></td>
                        <td><?= $r['ativo'] ? 'Sim' : 'Não' ?></td>
                        <td>
                            <a class="btn btn-ghost"
                                href="<?= e(base('admin/projeto_editar.php?id=' . $r['id'])) ?>">Editar</a>
                            <a class="btn btn-ghost"
                                href="<?= e(base('admin/pedido_publicar.php?toggle=' . (int)$r['id'])) ?>"
                                onclick="return confirm('Alternar publicação?')">
                                <?= $r['ativo'] ? 'Despublicar' : 'Republicar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="4" class="muted">Nenhum projeto publicado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>