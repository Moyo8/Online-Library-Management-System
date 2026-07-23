<?php
// Verify the API key by making a direct HTTP request to Anthropic API

$api_key = getenv('ANTHROPIC_API_KEY');

if (!$api_key) {
    fwrite(STDERR, "ANTHROPIC_API_KEY is not set.\n");
    exit(1);
}

echo "Verifying API key with direct HTTP request...\n";
echo "Key length: " . strlen($api_key) . "\n";
echo "Key preview: " . substr($api_key, 0, 20) . "..." . substr($api_key, -20) . "\n\n";

// Check for any invisible characters
echo "Checking for invisible characters:\n";
$hex = bin2hex($api_key);
echo "First 50 chars as hex: " . substr($hex, 0, 100) . "\n";
echo "Last 50 chars as hex: " . substr($hex, -100) . "\n\n";

// Use cURL to make a direct request to Anthropic API
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'claude-3-5-sonnet-20241022',
        'max_tokens' => 10,
        'messages' => [
            ['role' => 'user', 'content' => 'Hi']
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: ' . $api_key,
        'anthropic-version: 2023-06-01'
    ],
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

curl_close($ch);

echo "HTTP Status Code: $http_code\n";
if ($curl_error) {
    echo "cURL Error: $curl_error\n";
} else {
    echo "Response: $response\n";

    // Try to parse as JSON
    $data = json_decode($response, true);
    if ($data !== null) {
        echo "Parsed JSON:\n";
        print_r($data);
    }
}
?>