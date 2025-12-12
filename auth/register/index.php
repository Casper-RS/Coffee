<?php
require_once __DIR__ . '/../../src/partials/bootstrap.php';

startSession();

// Redirect if logged in
if (!empty($_SESSION['logged_in'])) {
    header("Location: ../../dashboard/");
    exit;
}

// Handle AJAX Register Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start output buffering to catch any unwanted output
    ob_start();
    header("Content-Type: application/json");

    try {
        requireDatabase();
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Initialisatiefout bij registreren.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $pw = $input['pw'] ?? '';
    $pwConfirm = $input['pwConfirm'] ?? '';

    if ($username === '' || $pw === '' || $pwConfirm === '') {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['error' => 'Vul alle velden in.']);
        exit;
    }

    if ($pw !== $pwConfirm) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['error' => 'Wachtwoorden komen niet overeen.']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT username FROM users WHERE username = :u LIMIT 1");
        if (!$check) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Database query voorbereiding mislukt: ' . implode(', ', $pdo->errorInfo())]);
            exit;
        }

        $check->execute(['u' => $username]);
        if ($check->errorCode() !== '00000') {
            ob_end_clean();
            http_response_code(500);
            $errorInfo = $check->errorInfo();
            echo json_encode(['error' => 'Database query fout: ' . $errorInfo[2]]);
            exit;
        }

        if ($check->fetch()) {
            ob_end_clean();
            http_response_code(409);
            echo json_encode(['error' => 'Deze gebruikersnaam is al in gebruik.']);
            exit;
        }

        $hash = password_hash($pw, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, created_at)
            VALUES (:u, :h, NOW())
        ");

        if (!$stmt) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Database INSERT voorbereiding mislukt: ' . implode(', ', $pdo->errorInfo())]);
            exit;
        }

        $stmt->execute(['u' => $username, 'h' => $hash]);

        if ($stmt->errorCode() !== '00000') {
            ob_end_clean();
            http_response_code(500);
            $errorInfo = $stmt->errorInfo();
            echo json_encode(['error' => 'Database INSERT fout: ' . $errorInfo[2]]);
            exit;
        }

        ob_end_clean();
        echo json_encode(['status' => 'ok']);
        exit;
    } catch (PDOException $e) {
        ob_end_clean();
        error_log('[Coffee] Register database error: ' . $e->getMessage());
        http_response_code(500);
        // Tijdelijk: toon SQL error voor debugging
        echo json_encode(['error' => 'Databasefout bij registreren: ' . $e->getMessage()]);
        exit;
    } catch (Exception $e) {
        ob_end_clean();
        error_log('[Coffee] Register error: ' . $e->getMessage());
        http_response_code(500);
        // Tijdelijk: toon error voor debugging
        echo json_encode(['error' => 'Er ging iets mis bij het registreren: ' . $e->getMessage()]);
        exit;
    }
}

includeHeader("CoffeeSaver | Registreren", true);
?>

<div id="particles"></div>

<div class="min-h-screen flex items-center justify-center p-4 relative z-10">
    <div class="bg-[#130c08]/80 backdrop-blur-xl border border-[#2b1a13] p-8 rounded-2xl shadow-xl max-w-sm w-full">

        <h1 class="text-3xl font-bold text-center text-amber-100">Registreren</h1>
        <p class="text-center text-amber-300/80 mb-6">Registreer als koffie(k)leut(er)</p>

        <form id="registerForm" class="space-y-4">

            <div>
                <label class="text-gray-400 text-sm mt-3">Gebruikersnaam</label>
                <input type="text" id="username"
                    class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded mt-1 mb-2"
                    required autocomplete="username">
            </div>

            <div>
                <label class="text-gray-400 text-sm mt-3 mb-1">Wachtwoord</label>
                <input type="password" id="pw"
                    class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded mt-1 mb-2"
                    required autocomplete="new-password">
            </div>

            <div>
                <label class="text-gray-400 text-sm mt-3 mb-1">Herhaal wachtwoord</label>
                <input type="password" id="pwConfirm"
                    class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded mt-1 mb-5"
                    required autocomplete="new-password">
            </div>

            <button type="submit" id="submitBtn"
                class="w-full bg-[#c47a3d] text-[#1b0f08] p-3 rounded font-semibold transition hover:bg-[#d18a51] disabled:opacity-50">
                Registreren
            </button>

            <p class="text-center text-sm text-amber-300 mt-3">
                Al een account?
                <a href="../login/" class="text-amber-200 underline">Inloggen</a>
            </p>

        </form>

    </div>
</div>

<script>
    document.getElementById("registerForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        const btn = document.getElementById("submitBtn");
        btn.disabled = true;
        btn.textContent = "Even geduld...";

        const username = document.getElementById("username").value.trim();
        const pw = document.getElementById("pw").value;
        const pwConfirm = document.getElementById("pwConfirm").value;

        // Client-side validation
        if (pw !== pwConfirm) {
            toast.show("Wachtwoorden komen niet overeen.", "error");
            btn.disabled = false;
            btn.textContent = "Registreren";
            return;
        }

        try {
            const res = await fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    username,
                    pw,
                    pwConfirm
                })
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data.status === "ok") {
                toast.show("Registratie succesvol!", "success");

                setTimeout(() => window.location.href = "../login/", 1200);
            } else {
                toast.show(data.error || "Registratie mislukt.", "error");
            }

        } catch {
            toast.show("Verbindingsfout. Probeer opnieuw.", "error");
        }

        btn.disabled = false;
        btn.textContent = "Registreren";
    });
</script>

<?php includeFooter(); ?>