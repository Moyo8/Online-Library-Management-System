<?php
require 'config.php';
$email = 'admin@olms.com';
$password = 'admin123'; // Try common default

$stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "User found: {$user['name']} ({$user['email']})" . PHP_EOL;
    if (password_verify($password, $user['password'])) {
        echo "Password verification SUCCESS!" . PHP_EOL;
    } else {
        echo "Password verification FAILED." . PHP_EOL;
        echo "Stored hash: {$user['password']}" . PHP_EOL;
    }
} else {
    echo "User not found with email: $email" . PHP_EOL;
}
?>