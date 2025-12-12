<?php

$DB_HOST = 'localhost:3306';
$DB_NAME = 'Projects';
$DB_USER = 'Casper';
$DB_PASS = 'Lotr2019!';

$connection = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$options = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
	$pdo = new PDO($connection, $DB_USER, $DB_PASS, $options);
	// Make sure $pdo is available globally
	$GLOBALS['pdo'] = $pdo;

	// Set MySQL session timezone to Europe/Amsterdam
	try {
		$pdo->exec("SET time_zone = '+01:00'"); // CET (winter time) - adjust to +02:00 for summer time if needed
	} catch (PDOException $tzError) {
		// If timezone setting fails, continue anyway
		error_log('[Coffee] Could not set MySQL timezone: ' . $tzError->getMessage());
	}
} catch (PDOException $e) {
	// Log exact fout naar server logs
	error_log('[Coffee] DB-verbinding mislukt: ' . $e->getMessage());

	// Set $pdo to null so we can check for it
	$pdo = null;

	// Als we in een AJAX/JSON request zitten, gooi exception zodat try-catch het kan opvangen
	if (
		!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
		(!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
		($_SERVER['REQUEST_METHOD'] === 'POST')
	) {
		// Don't exit here - let the calling code handle it
		// This allows the error to be caught by try-catch in login.php
		throw $e;
	}

	// Anders normale HTML error
	http_response_code(500);
	exit('Databaseverbinding mislukt (zie server error log).');
}
