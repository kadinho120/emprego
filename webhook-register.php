<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Config\Database;

// 1. Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON or missing email field']);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// 2. Security Check (Secret Token)
$webhookSecret = getenv('WEBHOOK_SECRET');
$receivedSecret = $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '';

if (!$webhookSecret || $receivedSecret !== $webhookSecret) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance();

    // 3. Insert User (ON CONFLICT DO NOTHING for PostgreSQL)
    $stmt = $db->prepare("INSERT INTO users (email) VALUES (?) ON CONFLICT (email) DO NOTHING");
    $stmt->execute([$email]);

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'User processed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
