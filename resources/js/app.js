document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card');
    const container = document.getElementById('cards3d');

    function updateCards() {
        if (!container) return;
        const rect = container.getBoundingClientRect();
        const centerY = rect.top + rect.height / 2;
        cards.forEach(card => {
            const r = card.getBoundingClientRect();
            const dy = (r.top + r.height / 2) - centerY;
            const offset = Math.max(-1, Math.min(1, dy / (rect.height / 2)));
            const rotateX = offset * 8; // tilt
            const translateZ = -Math.abs(offset) * 20; // depth
            card.style.transform = `perspective(1200px) translateZ(${translateZ}px) rotateX(${rotateX}deg)`;
        });
    }

    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                updateCards();
                ticking = false;
            });
        }
        ticking = true;
    }, { passive: true });

    // hover tilt
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const r = card.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            const rx = -py * 10;
            const ry = px * 10;
            card.style.transform = `perspective(1200px) rotateX(${rx}deg) rotateY(${ry}deg) translateZ(12px)`;
            card.style.transition = 'transform 0.08s ease';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.transition = 'transform 0.35s cubic-bezier(.2,.9,.2,1)';
        });
    });

    // mobile nav toggle
    const toggle = document.querySelector('.mobile-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const side = document.querySelector('.side-panel');
            if (side) side.classList.toggle('open');
        });
    }

    // initial run
    updateCards();
});
