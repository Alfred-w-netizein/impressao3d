<?php
require_once __DIR__ . '/../../app/auth.php';
admin_require();
csrf_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = trim($_POST['nome'] ?? '');
    $desc   = trim($_POST['descricao'] ?? '');
    $valor  = str_replace(',', '.', (string)($_POST['valor'] ?? '0'));
    $pix    = trim($_POST['chave_pix'] ?? '');
    $status = $_POST['status'] ?? 'aguardando_pagamento';

    $statusOk = in_array($status, ['aguardando_pagamento', 'em_producao', 'pago', 'cancelado'], true);
    if (!nome_valido($nome) || $desc === '' || !is_numeric($valor) || (float)$valor <= 0 || $pix === '' || !$statusOk) {
        flash('err', 'Preencha todos os campos corretamente.');
    } else {
        $cli    = find_or_create_cliente($nome);
        $codigo = next_codigo();
        $stmt = db()->prepare("
          INSERT INTO pedidos (codigo, cliente_id, descricao, valor_total, chave_pix, status)
          VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([$codigo, $cli['id'], $desc, number_format((float)$valor, 2, '.', ''), $pix, $status]);
        flash('ok', "Pedido {$codigo} criado.");
        redirect('admin/pedido_detalhe.php?id=' . db()->lastInsertId());
    }
}

$title = 'Novo pedido';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco">
    <h1>Novo pedido</h1>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>Nome completo do cliente
            <input type="text" name="nome" required minlength="5">
        </label>
        <label>Descrição
            <textarea name="descricao" rows="4" required></textarea>
        </label>
        <label>Valor total (R$)
            <input type="text" name="valor" required placeholder="0,00" inputmode="decimal">
        </label>
        <label>Chave Pix
            <input type="text" name="chave_pix" required>
        </label>
        <label>Status inicial
            <select name="status">
                <option value="aguardando_pagamento">Aguardando pagamento</option>
                <option value="em_producao">Em produção</option>
                <option value="pago">Pago</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Criar pedido</button>
    </form>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>