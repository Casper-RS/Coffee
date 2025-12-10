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
} catch (PDOException $e) {
	// Log exact fout naar server logs, toon generieke melding aan de gebruiker

	error_log('[Coffee] DB-verbinding mislukt: ' . $e->getMessage());
	echo $e->getMessage();
	http_response_code(500);
	exit('Databaseverbinding mislukt (zie server error log).');
}
