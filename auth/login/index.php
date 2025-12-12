<?php
// Start output buffering immediately to catch any output
ob_start();

// Function to send response and exit (define early, before any requires)
function sendLoginResponse($statusCode, $data)
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

// Error handler for fatal errors
register_shutdown_function(function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(['error' => 'Fatal error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']]);
            exit;
        }
    }
});

try {
    require_once __DIR__ . '/../../src/partials/bootstrap.php';

    startSession();
} catch (Throwable $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        sendLoginResponse(500, ['error' => 'Bootstrap error: ' . $e->getMessage()]);
    }
    throw $e;
}

// Redirect if already logged in
if (!empty($_SESSION['logged_in'])) {
    ob_end_clean();
    header("Location: ../../dashboard/");
    exit;
}

// Handle AJAX Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any existing output
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();

    // Set JSON header immediately
    header("Content-Type: application/json");

    try {
        requireDatabase();

        // Get $pdo from global scope
        global $pdo;
        if (!isset($pdo) && isset($GLOBALS['pdo'])) {
            $pdo = $GLOBALS['pdo'];
        }

        // Check if $pdo was created
        if (!isset($pdo) || $pdo === null) {
            sendLoginResponse(500, ['error' => 'Database verbinding mislukt: $pdo is niet geïnitialiseerd. Check database credentials en server logs.']);
        }

        requireBruteForceProtection();

        // Ensure login_attempts table exists
        ensureLoginAttemptsTable($pdo);
    } catch (PDOException $e) {
        sendLoginResponse(500, ['error' => 'Database verbinding mislukt: ' . $e->getMessage()]);
    } catch (Throwable $e) {
        sendLoginResponse(500, ['error' => 'Initialisatiefout bij inloggen: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['fgebruikersnaam'] ?? '');
    $password = $input['fwachtwoord'] ?? '';

    if ($username === '' || $password === '') {
        sendLoginResponse(400, ['error' => 'Vul alle velden in.']);
    }

    // Get client IP
    try {
        $clientIP = getClientIP();
    } catch (Exception $e) {
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // Check brute-force protection by IP (silently fail if there's an error)
    try {
        $ipCheck = checkBruteForce($pdo, $clientIP, 'ip');
        if ($ipCheck['locked']) {
            sendLoginResponse(429, ['error' => $ipCheck['message']]);
        }
    } catch (Exception $e) {
        // Log but continue - don't block login if brute-force check fails
        error_log('[Coffee] Brute-force IP check error: ' . $e->getMessage());
    }

    // Check brute-force protection by username (silently fail if there's an error)
    try {
        $usernameCheck = checkBruteForce($pdo, $username, 'username');
        if ($usernameCheck['locked']) {
            sendLoginResponse(429, ['error' => $usernameCheck['message']]);
        }
    } catch (Exception $e) {
        // Log but continue - don't block login if brute-force check fails
        error_log('[Coffee] Brute-force username check error: ' . $e->getMessage());
    }

    try {
        $stmt = $pdo->prepare("SELECT userID, username, password_hash FROM users WHERE username = :u LIMIT 1");
        if (!$stmt) {
            sendLoginResponse(500, ['error' => 'Database query voorbereiding mislukt: ' . implode(', ', $pdo->errorInfo())]);
        }

        $stmt->execute(['u' => $username]);
        if ($stmt->errorCode() !== '00000') {
            $errorInfo = $stmt->errorInfo();
            sendLoginResponse(500, ['error' => 'Database query fout: ' . $errorInfo[2]]);
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login - clear all attempts
            clearLoginAttempts($pdo, $clientIP, 'ip');
            clearLoginAttempts($pdo, $username, 'username');

            $_SESSION['logged_in'] = [
                'id'       => $user['userID'],
                'username' => $user['username']
            ];

            // Initialize session activity tracking
            requireSrc('security/sessionTimeout.php');
            initializeSessionActivity();

            sendLoginResponse(200, ['status' => 'ok']);
        }

        // Failed login - record attempts (silently fail if there's an error)
        try {
            recordFailedAttempt($pdo, $clientIP, 'ip');
            recordFailedAttempt($pdo, $username, 'username');

            // Check if we've now exceeded the limit AFTER recording this attempt
            $ipCheckAfter = checkBruteForce($pdo, $clientIP, 'ip');
            $usernameCheckAfter = checkBruteForce($pdo, $username, 'username');

            // If locked, return lockout message
            if ($ipCheckAfter['locked']) {
                sendLoginResponse(429, ['error' => $ipCheckAfter['message']]);
            }

            if ($usernameCheckAfter['locked']) {
                sendLoginResponse(429, ['error' => $usernameCheckAfter['message']]);
            }
        } catch (Exception $e) {
            // Log but continue - don't block error message if recording fails
            error_log('[Coffee] Error recording failed attempt: ' . $e->getMessage());
        }

        // Normal failed login response
        sendLoginResponse(401, ['error' => 'Ongeldige inloggegevens.']);
    } catch (PDOException $e) {
        error_log('[Coffee] Login database error: ' . $e->getMessage());
        // Tijdelijk: toon SQL error voor debugging
        sendLoginResponse(500, ['error' => 'Databasefout bij inloggen: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
    } catch (Throwable $e) {
        error_log('[Coffee] Login error: ' . $e->getMessage());
        // Tijdelijk: toon error voor debugging
        sendLoginResponse(500, ['error' => 'Er ging iets mis bij het inloggen: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
    }
}

includeHeader("CoffeeSaver | Login", true);
?>

<div id="particles"></div>

<div class="min-h-screen flex items-center justify-center p-4 relative z-10">
    <div class="bg-[#130c08]/80 backdrop-blur-xl border border-[#2b1a13] p-8 rounded-2xl shadow-xl max-w-sm w-full">

        <h1 class="text-3xl font-bold text-center text-amber-100">Inloggen</h1>
        <p class="text-center text-amber-300/80 mb-6">Bekijk je verslavings statistieken!</p>

        <form id="loginForm" class="space-y-4">

            <div>
                <label class="text-gray-400 text-sm mt-3">Gebruikersnaam</label>
                <input type="text" id="gebruikersnaam"
                    class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded mt-1 mb-2"
                    required autocomplete="username">
            </div>

            <div>
                <label class="text-gray-400 text-sm">Wachtwoord</label>
                <input type="password" id="pw"
                    class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded mt-1 mb-5"
                    required autocomplete="current-password">
            </div>

            <button type="submit" id="loginBtn"
                class="w-full bg-[#c47a3d] text-[#1b0f08] p-3 rounded font-semibold transition hover:bg-[#d18a51] disabled:opacity-50">
                Inloggen
            </button>

            <p class="text-center text-sm text-amber-300 mt-3">
                Nog geen account?
                <a href="../register/" class="text-amber-200 underline">Registreren</a>
            </p>

        </form>

    </div>
</div>

<script src="/src/scripts/login.js"></script>

<?php includeFooter(); ?>