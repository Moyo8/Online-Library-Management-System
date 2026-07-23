<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions/ai_assistant.php';

// Start session
session_start();

// Check if user is logged in (admin, librarian, or user)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['user', 'admin', 'librarian'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Fallback to $_POST for form data
    $input = $_POST;
}

$message = $input['message'] ?? '';
$context = $input['context'] ?? $_SESSION['user_role']; // Default to user's role

// Validate message
if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Validate context
if (!in_array($context, ['user', 'admin', 'librarian'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid context']);
    exit;
}

// Get AI response
$ai_response = getClaudeResponse($message, $context);

// Return response
http_response_code(200);
echo json_encode([
    'response' => $ai_response,
    'context' => $context,
    'timestamp' => date('H:i')
]);
?>