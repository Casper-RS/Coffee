<?php
require_once __DIR__ . '/../src/partials/bootstrap.php';

requireAuth(10, '../auth/login/');

includeHeader("CoffeeSaver | Dashboard", true);
?>

<header
    class="sticky top-0 z-50 backdrop-blur-xl bg-[#0b0705]/70 border-b border-[#2b1a13] shadow-lg">
    <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-0 rounded-xl md:rounded-2xl shadow-xl overflow-hidden">
            <button onclick="toggleCard('paidCard')"
                class="w-full flex items-center justify-between px-4 py-3 bg-[#1a1a1a]/60 hover:bg-[#1f1f1f] transition">

                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-coins text-red-400"></i>
                    <h2 class="text-lg md:text-xl font-semibold text-red-400">Betaalde kopjes</h2>
                    <span id="paidMini" class="text-xs md:text-sm text-red-300 font-semibold opacity-80 ml-2 hidden">
                        €0.00
                    </span>
                </div>
                <i id="paidCardIcon" class="fa-solid fa-chevron-down text-amber-200 transition-transform"></i>
            </button>
            <div id="paidCard" class="px-4 md:px-6 py-4 hidden">
                <p class="text-4xl md:text-5xl font-bold text-amber-100" id="paidCount">0</p>
                <hr class="border-[#2b1a1a] my-3 md:my-4">
                <p class="text-xs md:text-sm text-amber-200">Totaal uitgegeven:</p>
                <p class="text-lg md:text-2xl font-bold text-red-400 mt-1" id="paidSum">€0.00</p>
            </div>
        </div>
        <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-0 rounded-xl md:rounded-2xl shadow-xl overflow-hidden">
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

            <div id="freeCard" class="px-4 md:px-6 py-4 hidden">

                <p class="text-4xl md:text-5xl font-bold text-amber-100" id="freeCount">0</p>

                <hr class="border-[#2b1a13] my-3 md:my-4">

                <p class="text-xs md:text-sm text-amber-200">Totaal bespaard:</p>
                <p class="text-lg md:text-2xl font-bold text-green-400 mt-1" id="freeSum">€0.00</p>

            </div>
        </div>

    </div>

    <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-0 rounded-xl md:rounded-2xl shadow-xl mb-10 overflow-hidden">
        <button onclick="toggleCard('recentCard')"
            class="w-full flex items-center justify-between px-4 py-3 bg-[#1a1a1a]/60 hover:bg-[#1f1f1f] transition">

            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-300"></i>

                <h2 class="text-lg md:text-xl font-semibold text-amber-200">Recente kopjes</h2>

            </div>

            <i id="recentCardIcon" class="fa-solid fa-chevron-down text-amber-200 transition-transform"></i>
        </button>   
        <div id="recentCard" class="px-4 md:px-6 py-4 hidden">
            <div id="recentList"
                class="max-h-80 overflow-y-auto pr-2 space-y-3 scrollbar-thin scrollbar-thumb-[#3a2418] scrollbar-track-transparent">
            </div>
        </div>

    </div>


    <div class="bg-[#130c08]/80 border border-[#2b1a13] backdrop-blur-xl p-4 md:p-6 rounded-xl md:rounded-2xl shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4">
            <h2 class="text-lg md:text-xl font-semibold text-amber-200">Kopje koffie toevoegen</h2>

            <div class="flex items-center justify-center sm:justify-end gap-2 sm:gap-3">
                <span id="toggleLabel" class="text-sm sm:text-base text-amber-300 whitespace-nowrap font-medium">Betaald</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="coffeeTypeToggle" class="sr-only peer">
                    <div class="w-10 h-5 sm:w-11 sm:h-6 bg-[#3a2418] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 sm:after:h-5 sm:after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                </label>
                <span id="toggleLabelRight" class="text-sm sm:text-base text-amber-300 whitespace-nowrap font-medium">Gratis</span>
            </div>
        </div>

        <div id="amountButtons" class="flex flex-col sm:grid sm:grid-cols-4 gap-2 sm:gap-3">
            <button
                type="button"
                onclick="addCoffeeQuick(0.75)"
                class="p-2.5 sm:p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded hover:bg-[#2a2a2a] transition font-semibold text-sm sm:text-base">
                €0.75
            </button>
            <button
                type="button"
                onclick="addCoffeeQuick(1.5)"
                class="p-2.5 sm:p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded hover:bg-[#2a2a2a] transition font-semibold text-sm sm:text-base">
                €1.50
            </button>
            <button
                type="button"
                onclick="addCoffeeQuick(3)"
                class="p-2.5 sm:p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded hover:bg-[#2a2a2a] transition font-semibold text-sm sm:text-base">
                €3.00
            </button>
            <button
                type="button"
                onclick="openCustomAmountModal()"
                class="p-2.5 sm:p-3 bg-[#c47a3d] text-black border border-[#3a2418] rounded hover:bg-[#d18a51] transition font-semibold text-sm sm:text-base">
                Custom
            </button>
        </div>
    </div>
</div>

<div id="customAmountModal"
    class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur flex items-center justify-center pt-10 pb-6 md:pt-0 md:pb-0
           transition-opacity duration-300 opacity-0">

    <div id="customAmountModalBox"
        class="bg-[#130c08] border border-[#2b1a13] rounded-2xl p-4 md:p-6 w-full max-w-md shadow-xl
            transform translate-y-10 opacity-0 transition-all duration-300">

        <h2 id="modalTitle" class="text-xl font-bold text-amber-100 mb-3">Custom bedrag</h2>
        <p id="modalDescription" class="text-amber-300 mb-4">
            Voer het bedrag in:
        </p>

        <input
            type="text"
            id="customAmountInput"
            class="w-full p-3 bg-[#1a1a1a] text-amber-100 border border-[#3a2418] rounded transition-all"
            placeholder="€0.00">
        <p id="customAmountError" class="text-red-400 text-sm mt-1 hidden">Voer een geldig bedrag in.</p>

        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeCustomAmountModal()"
                class="px-4 py-2 rounded bg-gray-700 text-gray-200 hover:bg-gray-600">
                Annuleren
            </button>

            <button onclick="confirmCustomAmount()"
                class="px-4 py-2 rounded bg-[#c47a3d] text-black hover:bg-[#d18a51]">
                Toevoegen
            </button>
        </div>
    </div>
</div>

<script src="/src/scripts/dashboard.js?v=<?php echo time(); ?>"></script>

<?php includeFooter(); ?>