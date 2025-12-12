// ---------- Date formatter ----------
function formatDate(dateString) {
	// Parse the date string
	let d = new Date(dateString);

	// Subtract 1 hour to fix timezone offset
	d.setHours(d.getHours() - 1);

	return d.toLocaleString("nl-NL", {
		year: "numeric",
		month: "long",
		day: "2-digit",
		hour: "2-digit",
		minute: "2-digit",
	});
}

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

	data.recent.forEach((entry) => {
		const div = document.createElement("div");
		div.className =
			"flex justify-between items-center bg-[#1a1a1a] border border-[#3a2418] rounded p-3";

		const amount = parseFloat(entry.amount ?? 0).toFixed(2);
		const colorClass =
			entry.type === "free" ? "text-green-400" : "text-red-400";

		div.innerHTML = `
            <div>
                <p class="text-amber-100 font-medium text-sm md:text-base">
                    ${entry.type === "free" ? "Gratis kopje" : "Betaald kopje"}
                </p>
                <p class="text-xs md:text-sm text-amber-300">${formatDate(
					entry.created_at
				)}</p>
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

// ---------- TOGGLE FUNCTIONALITY ----------
const coffeeTypeToggle = document.getElementById("coffeeTypeToggle");
const toggleLabel = document.getElementById("toggleLabel");
const toggleLabelRight = document.getElementById("toggleLabelRight");

// Update toggle labels based on state
function updateToggleLabels(isFree) {
	if (isFree) {
		// Toggle AAN = Gratis kopjes (groen highlight rechts)
		toggleLabel.textContent = "Betaald";
		toggleLabelRight.textContent = "Gratis";
		toggleLabel.classList.remove("text-green-400", "font-semibold");
		toggleLabel.classList.add("text-amber-300");
		toggleLabelRight.classList.remove("text-amber-300");
		toggleLabelRight.classList.add("text-green-400", "font-semibold");
	} else {
		// Toggle UIT = Betaalde kopjes (amber highlight links)
		toggleLabel.textContent = "Betaald";
		toggleLabelRight.textContent = "Gratis";
		toggleLabel.classList.remove("text-amber-300");
		toggleLabel.classList.add("text-red-400", "font-semibold");
		toggleLabelRight.classList.remove("text-green-400", "font-semibold");
		toggleLabelRight.classList.add("text-amber-300");
	}
}

// Initialize toggle state (default: paid/uit)
updateToggleLabels(false);

coffeeTypeToggle.addEventListener("change", (e) => {
	updateToggleLabels(e.target.checked);
});

// ---------- QUICK ADD COFFEE ----------
async function addCoffeeQuick(amount) {
	const isFree = coffeeTypeToggle.checked;
	const type = isFree ? "free" : "paid";
	await submitCoffee(amount, type);
}

// ---------- CUSTOM AMOUNT MODAL ----------
function openCustomAmountModal() {
	const modal = document.getElementById("customAmountModal");
	const box = document.getElementById("customAmountModalBox");
	const isFree = coffeeTypeToggle.checked;

	// Update modal title and description based on toggle state
	const modalTitle = document.getElementById("modalTitle");
	const modalDescription = document.getElementById("modalDescription");

	if (isFree) {
		modalTitle.textContent = "Gratis kopje - Custom bedrag";
		modalDescription.textContent = "Wat had dit kopje normaal gekost?";
	} else {
		modalTitle.textContent = "Betaald kopje - Custom bedrag";
		modalDescription.textContent = "Voer het betaalde bedrag in:";
	}

	modal.classList.remove("hidden");

	// Delay zodat CSS transition kan starten
	setTimeout(() => {
		modal.classList.remove("opacity-0");
		box.classList.remove("translate-y-10", "opacity-0");
	}, 10);

	document.getElementById("customAmountInput").value = "";
	document.getElementById("customAmountInput").focus();
}

function closeCustomAmountModal() {
	const modal = document.getElementById("customAmountModal");
	const box = document.getElementById("customAmountModalBox");

	// Run closing animation
	modal.classList.add("opacity-0");
	box.classList.add("translate-y-10", "opacity-0");

	// Hide modal AFTER animation completes
	setTimeout(() => {
		modal.classList.add("hidden");
	}, 300);
}

async function confirmCustomAmount() {
	const input = document.getElementById("customAmountInput");
	const error = document.getElementById("customAmountError");
	const isFree = coffeeTypeToggle.checked;

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

	closeCustomAmountModal();
	const type = isFree ? "free" : "paid";
	await submitCoffee(amount, type);
}

async function submitCoffee(amount, type) {
	const res = await fetch("addCoffee.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify({
			amount,
			type,
		}),
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
	const res = await fetch("deleteCoffee.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify({
			id,
		}),
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

// Custom amount input validation
document.getElementById("customAmountInput").addEventListener("input", () => {
	const input = document.getElementById("customAmountInput");
	const error = document.getElementById("customAmountError");

	const amount = parseEuroInput(input.value);

	if (amount === null && input.value.trim() !== "") {
		input.classList.add("border-red-500");
		input.classList.remove("border-green-500");
		error.classList.remove("hidden");
	} else if (amount !== null) {
		input.classList.remove("border-red-500");
		input.classList.add("border-green-500");
		error.classList.add("hidden");
	} else {
		input.classList.remove("border-red-500", "border-green-500");
		error.classList.add("hidden");
	}
});

// Allow Enter key to submit in custom amount modal
document
	.getElementById("customAmountInput")
	.addEventListener("keypress", (e) => {
		if (e.key === "Enter") {
			e.preventDefault();
			confirmCustomAmount();
		}
	});
