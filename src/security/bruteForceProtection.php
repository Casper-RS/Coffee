<?php

/**
 * Brute-force protection functions for login security
 */

/**
 * Get the client's IP address, handling proxies and load balancers
 * 
 * @return string The client's IP address
 */
function getClientIP()
{
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            return trim($ip);
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Check if an identifier (IP or username) is locked out due to brute-force attempts
 * 
 * @param PDO $pdo Database connection
 * @param string $identifier IP address or username to check
 * @param string $type Type of identifier: 'ip' or 'username'
 * @return array ['locked' => bool, 'message' => string|null]
 */
function checkBruteForce($pdo, $identifier, $type = 'ip')
{
    $maxAttempts = 5;
    $lockoutTime = 15 * 60; // 15 minutes in seconds

    // Check if $pdo is valid
    if ($pdo === null) {
        error_log('[Coffee] checkBruteForce called with null $pdo');
        return ['locked' => false];
    }

    try {
        // Ensure table exists first (silently - don't fail if it already exists)
        ensureLoginAttemptsTable($pdo);

        // Clean old attempts (older than lockout time)
        try {
            $pdo->exec("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL $lockoutTime SECOND)");
        } catch (PDOException $e) {
            // Table might not exist yet, that's okay
            error_log('[Coffee] Error cleaning old attempts: ' . $e->getMessage());
        }

        // Check current attempts
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt 
                FROM login_attempts 
                WHERE identifier = :id AND type = :type 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL $lockoutTime SECOND)
            ");
            $stmt->execute(['id' => $identifier, 'type' => $type]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $attempts = (int)($result['attempts'] ?? 0);
            $lastAttempt = $result['last_attempt'] ?? null;

            if ($attempts >= $maxAttempts && $lastAttempt) {
                $lockoutUntil = strtotime($lastAttempt) + $lockoutTime;
                $remaining = $lockoutUntil - time();

                if ($remaining > 0) {
                    $minutes = ceil($remaining / 60);
                    return [
                        'locked' => true,
                        'message' => "Te veel mislukte inlogpogingen. Probeer over $minutes minuten opnieuw."
                    ];
                }
            }
        } catch (PDOException $e) {
            // Table might not exist yet, that's okay - don't block login
            error_log('[Coffee] Error checking brute-force attempts: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        // Log error but don't block login if there's any issue
        error_log('[Coffee] Brute-force check error: ' . $e->getMessage());
    }

    return ['locked' => false];
}

/**
 * Record a failed login attempt
 * 
 * @param PDO $pdo Database connection
 * @param string $identifier IP address or username
 * @param string $type Type of identifier: 'ip' or 'username'
 */
function recordFailedAttempt($pdo, $identifier, $type = 'ip')
{
    // Check if $pdo is valid
    if ($pdo === null) {
        error_log('[Coffee] recordFailedAttempt called with null $pdo');
        return;
    }

    try {
        // Ensure table exists
        ensureLoginAttemptsTable($pdo);

        $stmt = $pdo->prepare("
            INSERT INTO login_attempts (identifier, type, attempt_time) 
            VALUES (:id, :type, NOW())
        ");
        $stmt->execute(['id' => $identifier, 'type' => $type]);
    } catch (PDOException $e) {
        // Log error but don't break login flow
        error_log('[Coffee] Failed to record login attempt: ' . $e->getMessage());
    }
}

/**
 * Clear all login attempts for an identifier (called on successful login)
 * 
 * @param PDO $pdo Database connection
 * @param string $identifier IP address or username
 * @param string $type Type of identifier: 'ip' or 'username'
 */
function clearLoginAttempts($pdo, $identifier, $type = 'ip')
{
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE identifier = :id AND type = :type");
    $stmt->execute(['id' => $identifier, 'type' => $type]);
}

/**
 * Ensure the login_attempts table exists in the database
 * 
 * @param PDO $pdo Database connection
 */
function ensureLoginAttemptsTable($pdo)
{
    // Check if $pdo is valid
    if ($pdo === null) {
        error_log('[Coffee] ensureLoginAttemptsTable called with null $pdo');
        return;
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(255) NOT NULL,
                type ENUM('ip', 'username') NOT NULL,
                attempt_time DATETIME NOT NULL,
                INDEX idx_identifier_type (identifier, type),
                INDEX idx_attempt_time (attempt_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (PDOException $e) {
        // Log error but don't throw - table might already exist
        error_log('[Coffee] Error ensuring login_attempts table: ' . $e->getMessage());
    }
}
