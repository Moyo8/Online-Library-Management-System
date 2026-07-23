<?php
/**
 * Validate user credentials against the database
 * @param string $email
 * @param string $password
 * @param PDO $pdo
 * @return array|false User data on success, false on failure
 */
function validateUserCredentials($email, $password, $pdo) {
    $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Remove password from returned data for security
        unset($user['password']);
        return $user;
    }
    return false;
}
