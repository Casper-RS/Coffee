class Toast {
    constructor() {
        this.container = document.createElement('div');
        this.container.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(this.container);
    }

    show(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');

        // Styling based on type
        const bgColors = {
            success: 'bg-[#1a110d] border-[#c47a3d]', // Coffee dark + accent border
            error: 'bg-[#1a0505] border-red-800',     // Dark red
            info: 'bg-[#0a1a2f] border-blue-800'      // Dark blue
        };

        const textColors = {
            success: 'text-[#c47a3d]',
            error: 'text-red-400',
            info: 'text-blue-400'
        };

        const icons = {
            success: '☕',
            error: '⚠️',
            info: 'ℹ️'
        };

        toast.className = `
            ${bgColors[type] || bgColors.success} 
            border 
            ${textColors[type] || textColors.success}
            px-6 py-4 rounded-xl shadow-2xl shadow-black/50
            flex items-center gap-3 
            transform translate-x-full opacity-0 transition-all duration-300 ease-out
            min-w-[300px] backdrop-blur-md
        `;

        toast.innerHTML = `
            <span class="text-xl">${icons[type] || icons.success}</span>
            <span class="font-medium tracking-wide">${message}</span>
        `;

        this.container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });

        // Remove after duration
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    }
}

window.toast = new Toast();

