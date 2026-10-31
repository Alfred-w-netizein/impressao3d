<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function base($path = '')
{
    return rtrim(cfg()['app']['base_url'], '/') . '/' . ltrim($path, '/');
}
function redirect($path)
{
    header('Location: ' . base($path));
    exit;
}
function normalize_nome(string $n): string
{
    $n = trim(preg_replace('/\s+/', ' ', $n));
    $n = mb_strtolower($n, 'UTF-8');
    $tr = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $n);
    return $tr ?: $n;
}
function nome_valido(string $n): bool
{
    $parts = preg_split('/\s+/', trim($n));
    return count($parts) >= 2 && mb_strlen(trim($n)) >= 5;
}
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            http_response_code(419);
            exit('CSRF inválido.');
        }
    }
}
function flash(string $k, ?string $v = null)
{
    if ($v !== null) {
        $_SESSION['flash'][$k] = $v;
        return null;
    }
    $r = $_SESSION['flash'][$k] ?? null;
    unset($_SESSION['flash'][$k]);
    return $r;
}
function status_label(string $s): string
{
    $m = [
        'aguardando_pagamento' => 'Aguardando pagamento',
        'comprovante_enviado'  => 'Comprovante enviado',
        'em_revisao'           => 'Em revisão',
        'pago'                 => 'Pago',
        'em_producao'          => 'Em produção',
        'finalizado'           => 'Finalizado',
        'publicado'            => 'Publicado nos projetos',
        'cancelado'            => 'Cancelado',
    ];
    return $m[$s] ?? $s;
}
function brl($v)
{
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}
function find_or_create_cliente(string $nome): array
{
    $norm = normalize_nome($nome);
    $stmt = db()->prepare('SELECT * FROM clientes WHERE nome_normalizado = ?');
    $stmt->execute([$norm]);
    $c = $stmt->fetch();
    if ($c) return $c;
    $stmt = db()->prepare('INSERT INTO clientes (nome_completo, nome_normalizado) VALUES (?,?)');
    $stmt->execute([trim($nome), $norm]);
    return ['id' => (int)db()->lastInsertId(), 'nome_completo' => trim($nome), 'nome_normalizado' => $norm];
}
function next_codigo(): string
{
    $y = date('Y');
    $stmt = db()->prepare("SELECT COUNT(*) AS n FROM pedidos WHERE codigo LIKE ?");
    $stmt->execute(["ORD-$y-%"]);
    $n = (int)$stmt->fetch()['n'] + 1;
    return sprintf('ORD-%s-%04d', $y, $n);
}
function flash_render(): string
{
    $out = '';
    foreach (['ok', 'err'] as $t) {
        $m = flash($t);
        if ($m) $out .= '<div class="flash flash-' . $t . '">' . e($m) . '</div>';
    }
    return $out;
}
