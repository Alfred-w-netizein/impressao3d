<?php
function cfg(): array
{
    static $c = null;
    if ($c === null) $c = require __DIR__ . '/config.php';
    return $c;
}
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $d = cfg()['db'];
    $dsn = "mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}";
    $pdo = new PDO($dsn, $d['user'], $d['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '-03:00'");
    date_default_timezone_set(cfg()['app']['timezone']);
    return $pdo;
}
