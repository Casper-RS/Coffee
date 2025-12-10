<?php
session_start();
header("Content-Type: application/json");

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Niet ingelogd']);
    exit;
}

require_once __DIR__ . '/../src/partials/dbConnectie.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$uid = $_SESSION['logged_in']['id'];

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ongeldige ID']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM coffee_entries WHERE id = :id AND userID = :uid");
$stmt->execute(['id' => $id, 'uid' => $uid]);

echo json_encode(["status" => "ok"]);
