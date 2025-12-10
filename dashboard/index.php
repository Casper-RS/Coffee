<?php
session_start();

// Redirect naar login als niet ingelogd
if (empty($_SESSION['logged_in'])) {
    header("Location: ../login/");
    exit;
}

$pageTitle = "CoffeeSaver | Dashboard";
$includeParticles = true;
include '../src/partials/header.php';
?>

<!-- Fancy CoffeeSaver Header -->
<header
    class="sticky top-0 z-50 backdrop-blur-xl bg-[#0b0705]/70 border-b border-[#2b1a13] shadow-lg">
    <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">

        <!-- Left: Branding -->
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-[#c47a3d] flex items-center justify-center shadow-lg shadow-[#c47a3d]/30">
                <span class="text-[#1b0f08] font-bold text-xl">☕</span>
            </div>

            <div>
                <h1 class="text-xl md:text-2xl font-bold text-amber-100">
                    CoffeeSaver
                </h1>
                <p class="text-md md:text-lg font-bold text-amber-300">Welkom, <?php echo $_SESSION['logged_in']['username']; ?></p>
            </div>
        </div>

        <!-- Right: Logout -->
        <form action="../auth/logout/" method="post">
            <button
                type="submit"
                class="px-3 py-1.5 md:px-4 md:py-2 bg-[#c47a3d] text-black font-semibold rounded-lg hover:bg-[#d18a51] transition shadow-md shadow-[#c47a3d]/20 text-sm md:text-base">
                Uitloggen
            </button>

        </form>

    </div>
</header>

<div id="particles"></div>

<div class="p-4 md:p-6 max-w-4xl mx-auto relative z-10">

    <!-- TOP ROW: mirrored cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

        <!-- PAID CARD -->
        <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-0 rounded-xl md:rounded-2xl shadow-xl overflow-hidden">

            <!-- HEADER -->
            <button onclick="toggleCard('paidCard')"
                class="w-full flex items-center justify-between px-4 py-3 bg-[#1a1a1a]/60 hover:bg-[#1f1f1f] transition">

                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-coins text-red-400"></i>

                    <h2 class="text-lg md:text-xl font-semibold text-red-400">Betaalde kopjes</h2>

                    <!-- Mini totaal bedrag -->
                    <span id="paidMini" class="text-xs md:text-sm text-red-300 font-semibold opacity-80 ml-2 hidden">
                        €0.00
                    </span>
                </div>


                <i id="paidCardIcon" class="fa-solid fa-chevron-down text-amber-200 transition-transform"></i>
            </button>

            <!-- COLLAPSE BODY -->
            <div id="paidCard" class="px-4 md:px-6 py-4 hidden">

                <p class="text-4xl md:text-5xl font-bold text-amber-100" id="paidCount">0</p>

                <hr class="border-[#2b1a1a] my-3 md:my-4">

                <p class="text-xs md:text-sm text-amber-200">Totaal uitgegeven:</p>
                <p class="text-lg md:text-2xl font-bold text-red-400 mt-1" id="paidSum">€0.00</p>

            </div>
        </div>




        <!-- FREE CARD -->
        <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-0 rounded-xl md:rounded-2xl shadow-xl overflow-hidden">

            <!-- HEADER -->
            <button onclick="toggleCard('freeCard')"
                class="w-full flex items-center justify-between px-4 py-3 bg-[#1a1a1a]/60 hover:bg-[#1f1f1f] transition">

                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-mug-hot text-green-400"></i>

                    <h2 class="text-lg md:text-xl font-semibold text-green-400">Gratis kopjes</h2>

                    <span id="freeMini" class="text-xs md:text-sm text-green-300 font-semibold opacity-80 ml-2 hidden">
                        €0.00
                    </span>
                </div>


                <i id="freeCardIcon" class="fa-solid fa-chevron-down text-amber-200 transition-transform"></i>
            </button>

            <!-- COLLAPSE BODY -->
            <div id="freeCard" class="px-4 md:px-6 py-4 hidden">

                <p class="text-4xl md:text-5xl font-bold text-amber-100" id="freeCount">0</p>

                <hr class="border-[#2b1a13] my-3 md:my-4">

                <p class="text-xs md:text-sm text-amber-200">Totaal bespaard:</p>
                <p class="text-lg md:text-2xl font-bold text-green-400 mt-1" id="freeSum">€0.00</p>

            </div>
        </div>

    </div>



    <!-- RECENT COFFEE LIST -->
    <!-- RECENT COFFEE LIST (COLLAPSIBLE) -->
    <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-0 rounded-xl md:rounded-2xl shadow-xl mb-10 overflow-hidden">

        <!-- HEADER -->
        <button onclick="toggleCard('recentCard')"
            class="w-full flex items-center justify-between px-4 py-3 bg-[#1a1a1a]/60 hover:bg-[#1f1f1f] transition">

            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-300"></i>

                <h2 class="text-lg md:text-xl font-semibold text-amber-200">Recente kopjes</h2>

            </div>

            <i id="recentCardIcon" class="fa-solid fa-chevron-down text-amber-200 transition-transform"></i>
        </button>

        <!-- BODY -->
        <div id="recentCard" class="px-4 md:px-6 py-4 hidden">

            <div id="recentList"
                class="max-h-80 overflow-y-auto pr-2 space-y-3 scrollbar-thin scrollbar-thumb-[#3a2418] scrollbar-track-transparent">
                <!-- JS injects items here -->
            </div>

        </div>

    </div>


    <!-- Add new coffee -->
    <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-6 rounded-2xl shadow-xl">
        <h2 class="text-xl font-semibold text-amber-200 mb-4">Kopje koffie toevoegen</h2>

        <form id="addCoffeeForm" class="space-y-4">
            <input
                type="number"
                step="0.01"
                min="0"
                id="amount"
                class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded"
                placeholder="Bedrag (leeg = gratis kopje)">
            <p id="amountError" class="text-red-400 text-sm mt-1 hidden">Voer een geldig bedrag in.</p>

            <button
                type="submit"
                class="w-full p-3 bg-[#c47a3d] text-black font-semibold rounded hover:bg-[#d18a51] transition disabled:opacity-40"
                id="addBtn">
                Toevoegen
            </button>
        </form>
    </div>
</div>

<!-- FREE COST MODAL -->
<div id="freeModal"
    class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur flex items-center justify-center pt-10 pb-6 md:pt-0 md:pb-0
           transition-opacity duration-300 opacity-0">

    <div id="freeModalBox"
        class="bg-[#130c08] border border-[#2b1a13] rounded-2xl p-4 md:p-6 w-full max-w-md shadow-xl
            transform translate-y-10 opacity-0 transition-all duration-300">

        <h2 class="text-xl font-bold text-amber-100 mb-3">Gratis kopje koffie</h2>
        <p class="text-amber-300 mb-4">
            Wat had dit kopje normaal gekost?
        </p>

        <input
            type="text"
            id="freeCostInput"
            class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded transition-all"
            placeholder="€0.00">
        <p id="freeCostError" class="text-red-400 text-sm mt-1 hidden">Voer een geldig bedrag in.</p>


        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeFreeModal()"
                class="px-4 py-2 rounded bg-gray-700 text-gray-200 hover:bg-gray-600">
                Annuleren
            </button>

            <button onclick="confirmFreeCost()"
                class="px-4 py-2 rounded bg-[#c47a3d] text-black hover:bg-[#d18a51]">
                Toevoegen
            </button>
        </div>
    </div>
</div>

<script>
    // ---------- Date formatter ----------
    function formatDate(dateString) {
        const d = new Date(dateString);
        return d.toLocaleString("nl-NL", {
            year: "numeric",
            month: "long",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    // ---------- DASHBOARD LOAD ----------
    // ---------- DASHBOARD LOAD ----------
    async function loadDashboard() {
        const res = await fetch("getStats.php");
        const data = await res.json();

        if (!res.ok) {
            toast.show("Kon statistieken niet ophalen", "error");
            return;
        }

        // Zorg dat we echte nummers hebben
        const paidCount = Number(data.paidCount ?? 0);
        const paidSum = Number(data.paidSum ?? 0);
        const freeCount = Number(data.freeCount ?? 0);
        const freeSum = Number(data.freeSum ?? 0);

        // Grote waarden in de bodies
        document.getElementById("paidCount").textContent = paidCount;
        document.getElementById("paidSum").textContent = "€" + paidSum.toFixed(2);

        document.getElementById("freeCount").textContent = freeCount;
        document.getElementById("freeSum").textContent = "€" + freeSum.toFixed(2);

        // Mini totals in de headers
        const paidMiniEl = document.getElementById("paidMini");
        const freeMiniEl = document.getElementById("freeMini");

        paidMiniEl.textContent = "€" + paidSum.toFixed(2);
        freeMiniEl.textContent = "€" + freeSum.toFixed(2);

        // Omdat cards standaard dicht zijn, mini labels direct tonen
        paidMiniEl.classList.remove("hidden");
        freeMiniEl.classList.remove("hidden");

        // ----- Recent entries -----
        const list = document.getElementById("recentList");
        list.innerHTML = "";

        if (!data.recent || data.recent.length === 0) {
            list.innerHTML = `<p class="text-amber-300">Nog geen koffies geregistreerd.</p>`;
            return;
        }

        data.recent.forEach(entry => {
            const div = document.createElement("div");
            div.className =
                "flex justify-between items-center bg-[#1a1a1a] border border-[#3a2418] rounded p-3";

            const amount = parseFloat(entry.amount ?? 0).toFixed(2);
            const colorClass = entry.type === "free" ? 'text-green-400' : 'text-red-400';

            div.innerHTML = `
            <div>
                <p class="text-amber-100 font-medium text-sm md:text-base">
                    ${entry.type === "free" ? "Gratis kopje" : "Betaald kopje"}
                </p>
                <p class="text-xs md:text-sm text-amber-300">${formatDate(entry.created_at)}</p>
            </div>

            <div class="flex items-center gap-3">
                <p class="text-right font-bold text-base md:text-lg ${colorClass}">
                    €${amount}
                </p>
                <button 
                    onclick="deleteCoffee(${entry.id})"
                    class="text-red-500 hover:text-red-300 transition"
                >
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;

            list.appendChild(div);
        });
    }


    loadDashboard();

    // ---------- ADD COFFEE ----------
    document.getElementById("addCoffeeForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        const btn = document.getElementById("addBtn");
        btn.disabled = true;
        btn.textContent = "Bezig...";

        const amountField = document.getElementById("amount");
        const amount = parseEuroInput(amountField.value);

        // CASE 1: User entered an amount → normal paid coffee
        if (amount !== null) {
            await submitCoffee(amount, "paid");
            amountField.value = "";
            btn.disabled = false;
            btn.textContent = "Toevoegen";
            return;
        }

        // CASE 2: User did NOT enter an amount → open modal
        openFreeModal();

        btn.disabled = false;
        btn.textContent = "Toevoegen";
    });

    function openFreeModal() {
        const modal = document.getElementById("freeModal");
        const box = document.getElementById("freeModalBox");

        modal.classList.remove("hidden");

        // Delay zodat CSS transition kan starten
        setTimeout(() => {
            modal.classList.remove("opacity-0");
            box.classList.remove("translate-y-10", "opacity-0");
        }, 10);

        document.getElementById("freeCostInput").value = "";
        document.getElementById("freeCostInput").focus();
    }


    function closeFreeModal() {
        const modal = document.getElementById("freeModal");
        const box = document.getElementById("freeModalBox");

        // Run closing animation
        modal.classList.add("opacity-0");
        box.classList.add("translate-y-10", "opacity-0");

        // Hide modal AFTER animation completes
        setTimeout(() => {
            modal.classList.add("hidden");
        }, 300);
    }


    async function confirmFreeCost() {
        const input = document.getElementById("freeCostInput");
        const error = document.getElementById("freeCostError");

        const amount = parseEuroInput(input.value);

        if (amount === null) {
            input.classList.add("border-red-500");
            input.classList.remove("border-green-500");
            error.classList.remove("hidden");
            toast.show("Voer een geldig bedrag in.", "error");
            return;
        }

        // Clear UI
        input.classList.remove("border-red-500", "border-green-500");
        error.classList.add("hidden");

        closeFreeModal();
        await submitCoffee(amount, "free");
    }


    async function submitCoffee(amount, type) {
        const res = await fetch("addCoffee.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                amount,
                type
            })
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            toast.show("Kopje toegevoegd!", "success");
            loadDashboard();
        } else {
            toast.show(data.error || "Kon kopje niet toevoegen.", "error");
        }
    }


    function toggleCard(id) {
        const body = document.getElementById(id);
        const icon = document.getElementById(id + "Icon");

        // Zoek bijbehorende mini-span
        let miniId = null;
        if (id === "paidCard") miniId = "paidMini";
        if (id === "freeCard") miniId = "freeMini";

        const mini = miniId ? document.getElementById(miniId) : null;

        const isOpen = !body.classList.contains("hidden");

        if (isOpen) {
            // dichtklappen
            body.classList.add("hidden");
            icon.classList.remove("rotate-180");
            if (mini) mini.classList.remove("hidden");
        } else {
            // openklappen
            body.classList.remove("hidden");
            icon.classList.add("rotate-180");
            if (mini) mini.classList.add("hidden");
        }
    }



    async function deleteCoffee(id) {
        if (!confirm("Weet je zeker dat je dit kopje wilt verwijderen?")) return;

        const res = await fetch("deleteCoffee.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id
            })
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            toast.show("Kopje verwijderd.", "success");
            loadDashboard();
        } else {
            toast.show(data.error || "Kon kopje niet verwijderen.", "error");
        }
    }

    function parseEuroInput(value) {
        if (!value) return null;

        // Remove € and spaces
        value = value.replace("€", "").trim();

        // Replace comma with dot
        value = value.replace(",", ".");

        // Must be a number
        const num = Number(value);
        if (isNaN(num) || num <= 0) return null;

        return Number(num.toFixed(2));
    }

    document.getElementById("freeCostInput").addEventListener("input", () => {
        const input = document.getElementById("freeCostInput");
        const error = document.getElementById("freeCostError");

        const amount = parseEuroInput(input.value);

        if (amount === null) {
            input.classList.add("border-red-500");
            input.classList.remove("border-green-500");
            error.classList.remove("hidden");
        } else {
            input.classList.remove("border-red-500");
            input.classList.add("border-green-500");
            error.classList.add("hidden");
        }
    });

    document.getElementById("amount").addEventListener("input", () => {
        const input = document.getElementById("amount");
        const error = document.getElementById("amountError");

        const amount = parseEuroInput(input.value);

        if (input.value.trim() === "") {
            // empty is allowed → is gratis
            input.classList.remove("border-red-500");
            error.classList.add("hidden");
            return;
        }

        if (amount === null) {
            input.classList.add("border-red-500");
            error.classList.remove("hidden");
        } else {
            input.classList.remove("border-red-500");
            error.classList.add("hidden");
            input.value = amount; // auto-format
        }
    });
</script>

<?php include '../src/partials/footer.php'; ?>