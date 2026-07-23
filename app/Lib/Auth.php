<?php
namespace App\Lib;

/**
 * Simple Token Authentication (JWT-like)
 */
class Auth {
    private $secret_key;
    private $expire_hours = 24;

    public function __construct() {
        $this->secret_key = getenv('API_SECRET_KEY') ?: 'default_secret_key_change_in_production';
    }

    /**
     * Generate token for user
     * @param array $user_data User data (id, email, role)
     * @return string Token
     */
    public function generateToken($user_data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user_data['id'],
            'email' => $user_data['email'],
            'role' => $user_data['role'],
            'iat' => time(),
            'exp' => time() + ($this->expire_hours * 3600)
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     * Validate token and return payload if valid
     * @param string $token Token
     * @return array|false Payload or false if invalid
     */
    public function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        // Decode
        $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Header)), true);
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);

        if (!$header || !$payload) {
            return false;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $this->secret_key, true);
        $providedSignature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Signature));

        if ($expectedSignature !== $providedSignature) {
            return false;
        }

        return $payload;
    }
}
?>