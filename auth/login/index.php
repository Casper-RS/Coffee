<?php
session_start();

// Redirect if already logged in
if (!empty($_SESSION['logged_in'])) {
    header("Location: ../../dashboard/");
    exit;
}

// Handle AJAX Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");
    require_once __DIR__ . '/../../src/partials/dbConnectie.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['fgebruikersnaam'] ?? '');
    $password = $input['fwachtwoord'] ?? '';

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vul alle velden in.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT userID, username, password_hash FROM users WHERE username = :u LIMIT 1");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['logged_in'] = [
                'id'       => $user['userID'],
                'username' => $user['username']
            ];

            echo json_encode(['status' => 'ok']);
            exit;
        }

        http_response_code(401);
        echo json_encode(['error' => 'Ongeldige inloggegevens.']);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Databasefout bij inloggen.']);
        exit;
    }
}

$pageTitle = "CoffeeSaver | Login";
$includeParticles = true;
include '../../src/partials/header.php';
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

<script>
    document.getElementById("loginForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        const btn = document.getElementById("loginBtn");
        btn.disabled = true;
        btn.textContent = "Bezig met inloggen...";

        const username = document.getElementById("gebruikersnaam").value.trim();
        const pw = document.getElementById("pw").value;

        try {
            const res = await fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    fgebruikersnaam: username,
                    fwachtwoord: pw
                })
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data.status === "ok") {
                toast.show("Inloggen succesvol! Welkom terug.", "success");

                setTimeout(() => window.location.href = "../../dashboard/", 1200);
            } else {
                toast.show(data.error || "Inloggen mislukt.", "error");
            }

        } catch {
            toast.show("Verbindingsfout. Probeer opnieuw.", "error");
        }

        btn.disabled = false;
        btn.textContent = "Inloggen";
    });
</script>

<?php include '../../src/partials/footer.php'; ?>