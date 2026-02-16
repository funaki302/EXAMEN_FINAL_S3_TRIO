<?php
require_once __DIR__ . '/vendor/autoload.php';
$config = require_once __DIR__ . '/app/config/config.php';
$dsn = 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['dbname'] . ';charset=utf8mb4';
$pdo = new PDO($dsn, $config['database']['user'], $config['database']['password']);
$stmt = $pdo->query('SELECT id_user, pwd FROM user');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!password_get_info($row['pwd'])['algo']) {
        $hashed = password_hash($row['pwd'], PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE user SET pwd = ? WHERE id_user = ?')->execute([$hashed, $row['id_user']]);
        echo 'Updated user ' . $row['id_user'] . PHP_EOL;
    }
}
echo 'Done';
?>