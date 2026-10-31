<?php require_once __DIR__ . '/helpers.php'; require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title ?? 'Admin — PRINT3D') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Archivo+Black&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base('assets/css/style.css')) ?>">
</head>

<body class="admin">
    <header class="admin-header">
        <a class="logo" href="<?= e(base('admin/dashboard.php')) ?>">PRINT<span>3D</span> · ADMIN</a>
        <?php if (admin_logged()): ?>
        <nav>
            <a href="<?= e(base('admin/dashboard.php')) ?>">Dashboard</a>
            <a href="<?= e(base('admin/pedidos.php')) ?>">Pedidos</a>
            <a href="<?= e(base('admin/pedido_novo.php')) ?>">Novo</a>
            <a href="<?= e(base('admin/extrato.php')) ?>">Extrato</a>
            <a href="<?= e(base('admin/projetos_admin.php')) ?>">Projetos</a>
            <a href="<?= e(base('admin/logout.php')) ?>">Sair</a>
        </nav>
        <?php endif; ?>
    </header>
    <main class="wrap-admin">
        <?= flash_render() ?>