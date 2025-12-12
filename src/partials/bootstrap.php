<?php

/**
 * Bootstrap file for common includes and helper functions
 * This file should be included at the start of any PHP file
 * It defines the project root and provides helper functions for common includes
 */

// Set timezone to Europe/Amsterdam (Dutch timezone)
date_default_timezone_set('Europe/Amsterdam');

// Define project root if not already defined
if (!defined('PROJECT_ROOT')) {
    // Calculate project root based on this file's location
    // This file is in src/partials/, so go up 2 levels
    define('PROJECT_ROOT', dirname(dirname(__DIR__)));
}

/**
 * Get the path to a file in the src directory
 * 
 * @param string $path Path relative to src/ directory
 * @return string Full path to the file
 */
function srcPath($path)
{
    return PROJECT_ROOT . '/src/' . ltrim($path, '/');
}

/**
 * Include a file from the src directory
 * 
 * @param string $path Path relative to src/ directory
 */
function requireSrc($path)
{
    require_once srcPath($path);
}

/**
 * Include a file from the src directory (include, not require)
 * 
 * @param string $path Path relative to src/ directory
 */
function includeSrc($path)
{
    include srcPath($path);
}

/**
 * Start session if not already started
 */
function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Require database connection
 * Sets up $pdo variable in global scope
 */
function requireDatabase()
{
    global $pdo;
    requireSrc('partials/dbConnectie.php');
    // Make sure $pdo is available in current scope
    if (isset($GLOBALS['pdo'])) {
        $pdo = $GLOBALS['pdo'];
    }
}

/**
 * Require session timeout protection
 * 
 * @param int $timeoutMinutes Minutes before timeout (default: 10)
 * @return bool True if session is valid, false if timed out
 */
function requireSessionTimeout($timeoutMinutes = 10)
{
    requireSrc('security/sessionTimeout.php');
    return checkSessionTimeout($timeoutMinutes);
}

/**
 * Require brute force protection
 */
function requireBruteForceProtection()
{
    requireSrc('security/bruteForceProtection.php');
}

/**
 * Require authentication (checks session timeout and login status)
 * For protected pages - redirects to login if not authenticated
 * 
 * @param int $timeoutMinutes Minutes before timeout (default: 10)
 * @param string $redirectPath Path to redirect to if not authenticated (default: '../auth/login/')
 */
function requireAuth($timeoutMinutes = 10, $redirectPath = '../auth/login/')
{
    startSession();

    if (!requireSessionTimeout($timeoutMinutes) || empty($_SESSION['logged_in'])) {
        header("Location: $redirectPath");
        exit;
    }
}

/**
 * Require authentication for API endpoints
 * Returns JSON error if not authenticated
 * 
 * @param int $timeoutMinutes Minutes before timeout (default: 10)
 */
function requireAuthAPI($timeoutMinutes = 10)
{
    startSession();
    header("Content-Type: application/json");

    if (!requireSessionTimeout($timeoutMinutes)) {
        http_response_code(401);
        echo json_encode(['error' => 'Sessie verlopen. Log opnieuw in.']);
        exit;
    }

    if (empty($_SESSION['logged_in'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Niet ingelogd']);
        exit;
    }
}

/**
 * Include header partial
 * 
 * @param string $pageTitle Page title
 * @param bool $includeParticles Whether to include particles effect
 * @param string $pageDescription Optional page description for meta tags
 * @param string $pageImage Optional image URL for social media embeds
 */
function includeHeader(
    string $pageTitle = "CoffeeSaver",
    bool $includeParticles = false,
    ?string $pageDescription = null,
    ?string $pageImage = null,
    ?string $canonical = null
) {
    $GLOBALS['pageTitle'] = $pageTitle;

    $GLOBALS['pageDescription'] = $pageDescription
        ?? "CoffeeSaver helpt je inzicht krijgen in je koffie uitgaves en besparingen.";

    $GLOBALS['pageImage'] = $pageImage
        ?? "https://coffee.casper-rs.dev/src/images/og-image.png";

    // Canonical: automatisch huidige URL indien niet opgegeven
    if ($canonical === null) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $canonical = $scheme . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
    }

    $GLOBALS['canonicalUrl'] = $canonical;
    $GLOBALS['includeParticles'] = $includeParticles;

    includeSrc('partials/header.php');
}


/**
 * Include footer partial
 */
function includeFooter()
{
    includeSrc('partials/footer.php');
}
