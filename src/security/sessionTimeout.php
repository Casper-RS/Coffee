<?php

/**
 * Session timeout protection
 * Automatically logs out users after 10 minutes of inactivity
 */

/**
 * Check and enforce session timeout
 * Call this function at the start of any protected page
 * 
 * @param int $timeoutMinutes Number of minutes before timeout (default: 10)
 * @return bool Returns true if session is still valid, false if timed out
 */
function checkSessionTimeout($timeoutMinutes = 10)
{
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // If user is not logged in, no timeout check needed
    if (empty($_SESSION['logged_in'])) {
        return false;
    }

    $timeoutSeconds = $timeoutMinutes * 60;
    $currentTime = time();

    // Initialize last activity time if not set
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = $currentTime;
        return true;
    }

    // Check if session has timed out
    $timeSinceLastActivity = $currentTime - $_SESSION['last_activity'];

    if ($timeSinceLastActivity > $timeoutSeconds) {
        // Session has timed out - destroy session and redirect
        session_unset();
        session_destroy();

        // Return false to indicate timeout
        return false;
    }

    // Update last activity time
    $_SESSION['last_activity'] = $currentTime;

    return true;
}

/**
 * Initialize session activity tracking
 * Call this after successful login to set the initial activity time
 */
function initializeSessionActivity()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['last_activity'] = time();
}

/**
 * Get remaining session time in seconds
 * 
 * @param int $timeoutMinutes Number of minutes before timeout (default: 10)
 * @return int|null Remaining seconds or null if not logged in
 */
function getRemainingSessionTime($timeoutMinutes = 10)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['logged_in']) || !isset($_SESSION['last_activity'])) {
        return null;
    }

    $timeoutSeconds = $timeoutMinutes * 60;
    $currentTime = time();
    $timeSinceLastActivity = $currentTime - $_SESSION['last_activity'];
    $remaining = $timeoutSeconds - $timeSinceLastActivity;

    return max(0, $remaining);
}
