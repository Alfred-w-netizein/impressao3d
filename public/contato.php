<?php
require_once __DIR__ . '/../app/helpers.php';
csrf_check();

$donoKey = $_POST['dono'] ?? '';
$donos   = cfg()['admins'];
if (!isset($donos[$donoKey])) {
    flash('err', 'Dono inválido.');
    redirect('');
}

$nome = trim($_POST['nome'] ?? '');
if (!nome_valido($nome)) {
    flash('err', 'Informe nome completo (mínimo duas palavras).');
    redirect('');
}

$cliente = find_or_create_cliente($nome);

db()->prepare('INSERT INTO contatos_whatsapp (cliente_id, dono_escolhido) VALUES (?,?)')
    ->execute([$cliente['id'], $donos[$donoKey]['nome']]);

$msg = rawurlencode("Olá {$donos[$donoKey]['nome']}! Sou {$cliente['nome_completo']} e vim pelo site da PRINT3D. Gostaria de fazer um orçamento.");
$url = "https://wa.me/{$donos[$donoKey]['whats']}?text={$msg}";
header('Location: ' . $url);
exit;
