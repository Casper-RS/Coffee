<?php
session_start();

// Redirect if logged in
if (!empty($_SESSION['logged_in'])) {
    header("Location: ../../dashboard/");
    exit;
}

// Handle AJAX Register Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");
    require_once __DIR__ . '/../../src/partials/dbConnectie.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $pw = $input['pw'] ?? '';

    if ($username === '' || $pw === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vul alle velden in.']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT username FROM users WHERE username = :u LIMIT 1");
        $check->execute(['u' => $username]);

        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Deze gebruikersnaam is al in gebruik.']);
            exit;
        }

        $hash = password_hash($pw, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, created_at)
            VALUES (:u, :h, NOW())
        ");
        $stmt->execute(['u' => $username, 'h' => $hash]);

        echo json_encode(['status' => 'ok']);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Er ging iets mis bij het registreren.']);
        exit;
    }
}

$pageTitle = "CoffeeSaver | Registreren";
$includeParticles = true;
include '../../src/partials/header.php';
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

        try {
            const res = await fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    username,
                    pw
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

<?php include '../../src/partials/footer.php'; ?>