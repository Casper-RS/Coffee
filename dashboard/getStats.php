<?php
session_start();
header("Content-Type: application/json");

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Niet ingelogd']);
    exit;
}

require_once __DIR__ . '/../src/partials/dbConnectie.php';

$uid = $_SESSION['logged_in']['id'];

// Recent entries (laatste 20, incl. id!)
$qRecent = $pdo->prepare("
    SELECT id, amount, type, created_at
    FROM coffee_entries
    WHERE userID = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$qRecent->execute([$uid]);
$recent = $qRecent->fetchAll(PDO::FETCH_ASSOC);

// Paid cups + total spent
$qPaid = $pdo->prepare("
    SELECT 
        COUNT(*) AS paidCount, 
        COALESCE(SUM(amount), 0) AS paidSum
    FROM coffee_entries
    WHERE userID = ? AND type = 'paid'
");
$qPaid->execute([$uid]);
$paid = $qPaid->fetch(PDO::FETCH_ASSOC);

// Free cups + total saved
$qFree = $pdo->prepare("
    SELECT 
        COUNT(*) AS freeCount, 
        COALESCE(SUM(amount), 0) AS freeSum
    FROM coffee_entries
    WHERE userID = ? AND type = 'free'
");
$qFree->execute([$uid]);
$free = $qFree->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'paidCount' => (int)($paid['paidCount'] ?? 0),
    'paidSum'   => (float)($paid['paidSum'] ?? 0),

    'freeCount' => (int)($free['freeCount'] ?? 0),
    'freeSum'   => (float)($free['freeSum'] ?? 0),

    'recent'    => $recent,
]);
