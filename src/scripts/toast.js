class Toast {
	constructor() {
		this.container = document.createElement("div");
		// Responsive positioning: top center on mobile, top right on desktop
		this.container.className =
			"fixed top-4 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-4 z-50 flex flex-col gap-2 w-full max-w-[calc(100%-2rem)] md:max-w-none md:w-auto px-4 md:px-0";
		document.body.appendChild(this.container);
	}

	show(message, type = "success", duration = 3000) {
		const toast = document.createElement("div");

		// Styling based on type
		const bgColors = {
			success: "bg-[#1a110d] border-[#c47a3d]", // Coffee dark + accent border
			error: "bg-[#1a0505] border-red-800", // Dark red
			info: "bg-[#0a1a2f] border-blue-800", // Dark blue
		};

		const textColors = {
			success: "text-[#c47a3d]",
			error: "text-red-400",
			info: "text-blue-400",
		};

		const icons = {
			success: "☕",
			error: "⚠️",
			info: "ℹ️",
		};

		// Check if mobile (screen width < 768px)
		const isMobile = window.innerWidth < 768;

		// Animation: translate-y (from top) on mobile, translate-x (from right) on desktop
		const initialTransform = isMobile
			? "-translate-y-full"
			: "translate-x-full";
		const removeTransform = isMobile
			? "-translate-y-full"
			: "translate-x-full";

		toast.className = `
            ${bgColors[type] || bgColors.success} 
            border 
            ${textColors[type] || textColors.success}
            px-4 py-3 md:px-6 md:py-4 rounded-lg md:rounded-xl shadow-2xl shadow-black/50
            flex items-center gap-2 md:gap-3 
            transform ${initialTransform} opacity-0 transition-all duration-300 ease-out
            w-full max-w-full md:w-auto md:min-w-[300px] backdrop-blur-md
            text-sm md:text-base mx-auto md:mx-0
        `;

		toast.innerHTML = `
            <span class="text-lg md:text-xl flex-shrink-0">${
				icons[type] || icons.success
			}</span>
            <span class="font-medium tracking-wide break-words">${message}</span>
        `;

		this.container.appendChild(toast);

		// Animate in
		requestAnimationFrame(() => {
			toast.classList.remove(initialTransform, "opacity-0");
			if (isMobile) {
				toast.classList.add("translate-y-0");
			} else {
				toast.classList.add("translate-x-0");
			}
		});

		// Remove after duration
		setTimeout(() => {
			if (isMobile) {
				toast.classList.remove("translate-y-0");
			} else {
				toast.classList.remove("translate-x-0");
			}
			toast.classList.add(removeTransform, "opacity-0");
			setTimeout(() => {
				toast.remove();
			}, 300);
		}, duration);
	}
}

window.toast = new Toast();
