<?php
// app/config.php  (copie de config.example.php e ajuste)
return [
    'db' => [
        'host'     => 'localhost',
        'name'     => 'impressao3d',
        'user'     => 'root',
        'pass'     => '',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'base_url'   => 'https://',   // sem barra final
        'timezone'   => 'America/Sao_Paulo',
        'upload_dir' => __DIR__ . '/../public/uploads',
        'upload_url' => '/uploads',                 // caminho web
    ],
    'admins' => [
        'dono1' => [
            'nome'   => 'DONO_1',
            'whats'  => '5511999999991',  // DDI+DDD+número, só dígitos
        ],
        'dono2' => [
            'nome'   => 'DONO_2',
            'whats'  => '5511999999992',
        ],
    ],
    'upload' => [
        'max_image_bytes' => 5 * 1024 * 1024,   // 5 MB
        'max_video_bytes' => 40 * 1024 * 1024,  // 40 MB
        'image_exts'      => ['jpg', 'jpeg', 'png', 'webp'],
        'video_exts'      => ['mp4', 'webm', 'mov'],
        'doc_exts'        => ['jpg', 'jpeg', 'png', 'webp', 'pdf'], // comprovante
    ],
];
