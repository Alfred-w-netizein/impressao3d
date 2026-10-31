<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();

$toggle = (int)($_GET['toggle'] ?? 0);
if ($toggle) {
    $stmt = db()->prepare('SELECT * FROM projetos_publicados WHERE id = ?');
    $stmt->execute([$toggle]);
    $proj = $stmt->fetch();
    if ($proj) {
        $novoAtivo = $proj['ativo'] ? 0 : 1;
        db()->prepare('UPDATE projetos_publicados SET ativo = ?, publicado_em = COALESCE(publicado_em, NOW()) WHERE id = ?')
            ->execute([$novoAtivo, $toggle]);
        // se ativou, marca pedido como publicado
        if ($novoAtivo) {
            db()->prepare("UPDATE pedidos SET status='publicado', publicado_em = COALESCE(publicado_em, NOW()) WHERE id = ?")
                ->execute([$proj['pedido_id']]);
        }
        flash('ok', 'Publicação atualizada.');
        redirect('admin/projetos_admin.php');
    }
}
flash('err', 'Projeto não encontrado.');
redirect('admin/projetos_admin.php');
