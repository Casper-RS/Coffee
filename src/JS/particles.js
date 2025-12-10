// --- Particles Logic ---
const container = document.getElementById('particles');
const particleCount = window.innerWidth < 600 ? 30 : 50;
const particles = [];

function createParticles() {
    for (let i = 0; i < particleCount; i++) {
        const size = Math.random() * 6 + 3;
        const div = document.createElement('div');
        div.classList.add('particle');
        div.style.width = `${size}px`;
        div.style.height = `${size}px`;
        div.style.opacity = Math.random() * 0.4 + 0.2;

        // Initial position
        const x = Math.random() * 100;
        const y = Math.random() * 100;
        div.style.left = `${x}%`;
        div.style.top = `${y}%`;

        container.appendChild(div);

        particles.push({
            el: div,
            x: x,
            y: y,
            speedX: (Math.random() - 0.5) * 0.05,
            speedY: (Math.random() - 0.5) * 0.05
        });
    }
}

function animateParticles() {
    particles.forEach(p => {
        p.x += p.speedX;
        p.y += p.speedY;

        // Bounce off edges
        if (p.x < 0 || p.x > 100) p.speedX *= -1;
        if (p.y < 0 || p.y > 100) p.speedY *= -1;

        p.el.style.left = `${p.x}%`;
        p.el.style.top = `${p.y}%`;
    });
    requestAnimationFrame(animateParticles);
}

createParticles();
animateParticles();