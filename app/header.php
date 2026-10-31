<?php require_once __DIR__ . '/helpers.php'; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title ?? 'PRINT3D — impressão sob demanda') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Archivo+Black&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base('assets/css/style.css')) ?>">
</head>

<body>
    <header class="site-header">
        <a class="logo" href="<?= e(base()) ?>">PRINT<span>3D</span></a>
        <nav>
            <a href="<?= e(base('projetos.php')) ?>">Projetos</a>
            <a href="<?= e(base('acompanhar.php')) ?>">Acompanhar</a>
            <a class="cta" href="#contato">Contato</a>
        </nav>
    </header>
    <main class="wrap">
        <?= flash_render() ?>