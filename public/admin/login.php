<?php
require_once __DIR__ . '/../../app/auth.php';
csrf_check();

if (admin_logged()) redirect('admin/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (admin_login($email, $senha)) redirect('admin/dashboard.php');
    flash('err', 'Credenciais inválidas.');
}

$title = 'Login — Admin';
include __DIR__ . '/../../app/admin_header.php';
?>
<section class="bloco login-box">
    <h1>Entrar no painel</h1>
    <form method="post">
        <?= csrf_field() ?>
        <label>E-mail <input type="email" name="email" required></label>
        <label>Senha <input type="password" name="senha" required></label>
        <button class="btn btn-primary" type="submit">Entrar</button>
    </form>
</section>
<?php include __DIR__ . '/../../app/admin_footer.php'; ?>