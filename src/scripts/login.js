document.getElementById("loginForm").addEventListener("submit", async (e) => {
	e.preventDefault();

	const btn = document.getElementById("loginBtn");
	btn.disabled = true;
	btn.textContent = "Bezig met inloggen...";

	const username = document.getElementById("gebruikersnaam").value.trim();
	const pw = document.getElementById("pw").value;

	try {
		console.log("Starting login request...");

		const res = await fetch("", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({
				fgebruikersnaam: username,
				fwachtwoord: pw,
			}),
		});

		console.log("Login response status:", res.status);
		console.log("Login response headers:", res.headers);

		// Get raw response text first
		const responseText = await res.text();
		console.log("Login raw response:", responseText);

		// Try to parse as JSON
		let data = {};
		try {
			data = JSON.parse(responseText);
			console.log("Login parsed JSON data:", data);
		} catch (parseError) {
			console.error("Failed to parse JSON:", parseError);
			console.error("Response was:", responseText);
			data = {
				error:
					"Invalid JSON response: " + responseText.substring(0, 200),
			};
		}

		if (res.ok && data.status === "ok") {
			toast.show("Inloggen succesvol! Welkom terug.", "success");

			setTimeout(() => (window.location.href = "../../dashboard/"), 1200);
		} else {
			console.error("Login failed - Status:", res.status);
			console.error("Login failed - Data:", data);
			toast.show(data.error || "Inloggen mislukt.", "error");
		}
	} catch (error) {
		console.error("Login fetch error:", error);
		console.error("Error stack:", error.stack);
		toast.show("Verbindingsfout. Probeer opnieuw.", "error");
	}

	btn.disabled = false;
	btn.textContent = "Inloggen";
});
