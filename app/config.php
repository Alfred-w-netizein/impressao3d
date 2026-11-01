<?php
return [
    'db' => [
        'host'     => 'localhost',
        'name'     => 'impressao3d',
        'user'     => 'root',       // Usuário padrão no Fedora
        'pass'     => '',           // Deixe vazio se não tem senha no MySQL
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'base_url'   => 'http://localhost/impressao3d/public',
        'timezone'   => 'America/Sao_Paulo',
        'upload_dir' => __DIR__ . '/../public/uploads',
        'upload_url' => '/impressao3d/public/uploads', // Isso vai arrumar o CSS!
    ],
    'admins' => [
        'dono1' => [
            'nome'   => 'Lucas Antunes',
            'whats'  => '5511999999991',
        ],
        'dono2' => [
            'nome'   => 'Alfred Giulian',
            'whats'  => '5538998358004',
        ],
    ],
    'upload' => [
        'max_image_bytes' => 5 * 1024 * 1024,
        'max_video_bytes' => 40 * 1024 * 1024,
        'image_exts'      => ['jpg', 'jpeg', 'png', 'webp'],
        'video_exts'      => ['mp4', 'webm', 'mov'],
        'doc_exts'        => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
    ],
];
