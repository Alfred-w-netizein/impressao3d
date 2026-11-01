<?php
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';

echo "<h3>Teste de Login</h3>";

// 1. Testa a conexão com o banco
try {
    $stmt = db()->query('SELECT COUNT(*) FROM admins');
    $total = $stmt->fetchColumn();
    echo "✅ Banco de dados conectado. Total de admins: " . $total . "<br><br>";
} catch (Exception $e) {
    die("❌ ERRO de Banco de Dados: " . $e->getMessage());
}

// 2. Tenta validar a senha Admin123
$email = 'dono1@loja.local';
$senha = 'Admin123';

$stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin) {
    echo "❌ ERRO: O e-mail '$email' não foi encontrado na tabela admins!";
    db()->query("UPDATE admins SET senha_hash = '" . password_hash('Admin123', PASSWORD_DEFAULT) . "' WHERE email = 'dono1@loja.local'");
    echo "Senha resetada com sucesso pelo PHP!<br><br>";
} else {
    echo "✅ E-mail encontrado no banco.<br>";
    echo "Hash salvo no banco: <code>" . $admin['senha_hash'] . "</code><br>";
    echo "Tamanho do hash: " . strlen($admin['senha_hash']) . " caracteres.<br><br>";

    if (password_verify($senha, $admin['senha_hash'])) {
        echo "<h2 style='color:green;'>✅ SUCESSO! A senha 'Admin123' está correta para este hash.</h2>";
    } else {
        echo "<h2 style='color:red;'>❌ FALHOU: O hash do banco não corresponde à senha 'Admin123'.</h2>";
    }
}
