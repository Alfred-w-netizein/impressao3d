<?php
require_once __DIR__ . '/helpers.php';

function admin_logged(): bool
{
    return !empty($_SESSION['admin_id']);
}
function admin_require(): void
{
    if (!admin_logged()) redirect('admin/login.php');
}
function admin_login(string $email, string $senha): bool
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $a = $stmt->fetch();
    if ($a && password_verify($senha, $a['senha_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int)$a['id'];
        $_SESSION['admin_nome'] = $a['nome'];
        return true;
    }
    return false;
}
