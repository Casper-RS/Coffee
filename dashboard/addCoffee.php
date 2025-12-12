<?php
require_once __DIR__ . '/../src/partials/bootstrap.php';

requireAuthAPI(10);
requireDatabase();

$uid = $_SESSION['logged_in']['id'];
$input = json_decode(file_get_contents('php://input'), true);

$amount = floatval($input['amount'] ?? 0);
$type = $input['type'] ?? '';

if (!in_array($type, ['paid', 'free'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ongeldig type']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO coffee_entries (userID, amount, type)
    VALUES (:uid, :amount, :type)
");
$stmt->execute([
    'uid' => $uid,
    'amount' => $amount,
    'type' => $type,
]);

echo json_encode(['status' => 'ok']);
