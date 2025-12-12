<?php
require_once __DIR__ . '/../src/partials/bootstrap.php';

requireAuthAPI(10);
requireDatabase();

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

// Add timezone info to datetime strings so JavaScript interprets them correctly
// Database stores in Europe/Amsterdam timezone, so we append that timezone
foreach ($recent as &$entry) {
    if (isset($entry['created_at'])) {
        // MySQL datetime format: YYYY-MM-DD HH:MM:SS
        // Add timezone offset for Europe/Amsterdam (currently +01:00 for CET, +02:00 for CEST)
        // For simplicity, we'll use +01:00 (adjust if you're in summer time)
        $dt = new DateTime($entry['created_at'], new DateTimeZone('Europe/Amsterdam'));
        $entry['created_at'] = $dt->format('Y-m-d\TH:i:sP'); // ISO 8601 format with timezone
    }
}
unset($entry);

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
