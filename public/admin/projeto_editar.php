<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/upload.php';
admin_require();
csrf_check();

$projeto = null;
if (!empty($_GET['id'])) {
    $stmt = db()->prepare('SELECT * FROM projetos_publicados WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $projeto = $stmt->fetch();
    if (!$projeto) {
        http_response_code(404);
        exit('Projeto não encontrado.');
    }
    $pedidoId = (int)$projeto['pedido_id'];
} else {
    $pedidoId = (int)($_GET['pedido_id'] ?? 0);
}
if (!$pedidoId) exit('Pedido não informado.');

$stmt = db()->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$pedidoId]);
$pedido = $stmt->fetch();
if (!$pedido) exit('Pedido não encontrado.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $publicar  = !empty($_POST['publicar']);

    if ($descricao === '') {
        flash('err', 'Descrição obrigatória.');
        redirect('admin/projeto_editar.php?' . ($projeto ? 'id=' . $projeto['id'] : 'pedido_id=' . $pedidoId));
    }

    if ($projeto) {
        db()->prepare('UPDATE projetos_publicados SET descricao = ? WHERE id = ?')
            ->execute([$descricao, $projeto['id']]);
        $projetoId = (int)$projeto['id'];
    } else {
        db()->prepare('INSERT INTO projetos_publicados (pedido_id, descricao, ativo) VALUES (?,?,1)')
            ->execute([$pedidoId, $descricao]);
        $projetoId = (int)db()->lastInsertId();
    }

    // Upload de fotos (multiple)
    if (!empty($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
        $n = count($_FILES['fotos']['name']);
        for ($i = 0; $i < $n; $i++) {
            if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $single = [
                'name'     => $_FILES['fotos']['name'][$i],
                'type'     => $_FILES['fotos']['type'][$i],
                'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
                'error'    => $_FILES['fotos']['error'][$i],
                'size'     => $_FILES['fotos']['size'][$i],
            ];
            try {
                $path = upload_file(
                    $single,
                    'projetos',
                    cfg()['upload']['image_exts'],
                    cfg()['upload']['max_image_bytes']
                );
                db()->prepare('INSERT INTO projeto_midias (projeto_id, tipo, arquivo, ordem) VALUES (?,?,?,0)')
                    ->execute([$projetoId, 'foto', $path]);
            } catch (Throwable $e) {
                flash('err', 'Foto ignorada: ' . $e->getMessage());
            }
        }
    }

    // Upload de vídeo (único)
    if (!empty($_FILES['video']['name']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $path = upload_file(
                $_FILES['video'],
                'projetos',
                cfg()['upload']['video_exts'],
                cfg()['upload']['max_video_bytes']
            );
            db()->prepare('INSERT INTO projeto_midias (projeto_id, tipo, arquivo, ordem) VALUES (?,?,?,0)')
                ->execute([$projetoId, 'video', $path]);
        } catch (Throwable $e) {
            flash('err', 'Vídeo não enviado: ' . $e->getMessage());
        }
    }

    // Publicar de fato
    if ($publicar) {
        db()->prepare("UPDATE projetos_publicados SET ativo = 1, publicado_em = COALESCE(publicado_em, NOW()) WHERE id = ?")
            ->execute([$projetoId]);
        db()->prepare("UPDATE pedidos SET status='publicado', publicado_em=NOW() WHERE id = ?")
            ->execute([$pedidoId]);
    }

    flash('ok', 'Projeto salvo.');
    redirect('admin/projeto_editar.php?id=' . $projetoId);
}

$midias = [];
if ($projeto) {
    $m = db()->prepare('SELECT * FROM projeto_midias WHERE projeto_id = ? ORDER BY ordem, id');
    $m->execute([$projeto['id']]);
    $midias = $m->fetchAll();
}

$title = 'Editar projeto';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">
    <a href="<?= e(base('admin/pedido_detalhe.php?id=' . $pedidoId)) ?>">← Pedido <?= e($pedido['codigo']) ?></a>
    <h1><?= $projeto ? 'Editar projeto' : 'Criar projeto publicado' ?></h1>

    <form method="post" enctype="multipart/form-data" class="form-grid">
        <?= csrf_field() ?>
        <label>Descrição pública (sem dados sensíveis)
            <textarea name="descricao" rows="5" required><?= e($projeto['descricao'] ?? '') ?></textarea>
        </label>
        <label>Fotos (pode selecionar várias)
            <input type="file" name="fotos[]" accept=".jpg,.jpeg,.png,.webp" multiple>
        </label>
        <label>Vídeo (opcional, mp4/webm/mov)
            <input type="file" name="video" accept=".mp4,.webm,.mov">
        </label>
        <label class="checkbox">
            <input type="checkbox" name="publicar" value="1">
            Publicar imediatamente na galeria
        </label>
        <button class="btn btn-primary" type="submit">Salvar</button>
    </form>

    <?php if ($midias): ?>
        <h3>Mídias atuais</h3>
        <div class="galeria-admin">
            <?php foreach ($midias as $m): ?>
                <?php $url = base(cfg()['app']['upload_url'] . '/' . $m['arquivo']); ?>
                <div class="midia-item">
                    <?php if ($m['tipo'] === 'foto'): ?>
                        <img src="<?= e($url) ?>" alt="">
                    <?php else: ?>
                        <video controls src="<?= e($url) ?>"></video>
                    <?php endif; ?>
                    <a class="btn btn-ghost" href="<?= e(base('admin/projeto_midia_remover.php?id=' . $m['id'])) ?>"
                        onclick="return confirm('Remover mídia?')">Remover</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>