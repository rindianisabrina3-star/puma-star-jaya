// assets/js/main.js - Interactive Animations & Micro-Interactions for PT. Puma Star Jaya Tour
document.addEventListener('DOMContentLoaded', () => {
    // 1. Number Counter Animation for Dashboard Stats
    const statCounters = document.querySelectorAll('.stat-value');
    statCounters.forEach(counter => {
        const text = counter.innerText.trim();
        const num = parseInt(text.replace(/[^0-9]/g, ''));
        if (!isNaN(num) && num > 0 && num < 100000) {
            let start = 0;
            const duration = 1200;
            const stepTime = Math.max(Math.floor(duration / num), 20);
            const stepValue = Math.max(1, Math.floor(num / (duration / stepTime)));
            
            counter.innerText = '0';
            const timer = setInterval(() => {
                start += stepValue;
                if (start >= num) {
                    start = num;
                    clearInterval(timer);
                    counter.innerText = text.startsWith('0') && num < 10 ? String(num).padStart(2, '0') : num.toLocaleString('id-ID');
                } else {
                    counter.innerText = text.startsWith('0') && num < 10 ? String(start).padStart(2, '0') : start.toLocaleString('id-ID');
                }
            }, stepTime);
        }
    });

    // 2. Ripple click effect on buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('btn-ripple');
            this.appendChild(ripple);
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // 3. Staggered fade in for table rows
    const tableRows = document.querySelectorAll('.custom-table tbody tr');
    tableRows.forEach((row, i) => {
        row.style.animation = `fadeInUp 0.4s ease forwards ${i * 0.05}s`;
    });

    // 4. Glassmorphic Sticky Navbar Transition
    const navbar = document.querySelector('.main-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 45) {
                navbar.style.position = 'sticky';
                navbar.style.top = '0';
                navbar.style.zIndex = '1000';
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.94)';
                navbar.style.backdropFilter = 'blur(12px)';
                navbar.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.08)';
                navbar.style.transition = 'all 0.3s ease';
            } else {
                navbar.style.backgroundColor = '';
                navbar.style.backdropFilter = '';
                navbar.style.boxShadow = '';
            }
        });
    }

    // 5. 3D Tilt Micro-Interaction on Hover for Cards
    const cards = document.querySelectorAll('.paket-card, .stat-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -4;
            const rotateY = ((x - centerX) / centerX) * 4;
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

    // 6. Celebratory Confetti Animation on Success or E-Tiket Page
    const isEtiket = window.location.pathname.includes('etiket.php');
    const hasSuccessAlert = document.querySelector('.alert-success, [style*="#d1fae5"], [style*="#dcfce7"]');
    
    if (isEtiket || hasSuccessAlert) {
        setTimeout(triggerConfettiShower, 400);
    }
});

/**
 * Lightweight Standalone Canvas Confetti Explosion
 */
function triggerConfettiShower() {
    const canvas = document.createElement('canvas');
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100vw';
    canvas.style.height = '100vh';
    canvas.style.pointerEvents = 'none';
    canvas.style.zIndex = '99999';
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const colors = ['#ff5722', '#0284c7', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];
    const particles = [];

    for (let i = 0; i < 90; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * -canvas.height * 0.5,
            w: Math.random() * 9 + 5,
            h: Math.random() * 7 + 4,
            color: colors[Math.floor(Math.random() * colors.length)],
            speedY: Math.random() * 3 + 2.5,
            speedX: (Math.random() - 0.5) * 3,
            rot: Math.random() * 360,
            rotSpeed: (Math.random() - 0.5) * 6,
            opacity: 1
        });
    }

    let startTime = Date.now();
    function renderConfetti() {
        const elapsed = Date.now() - startTime;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach(p => {
            p.y += p.speedY;
            p.x += p.speedX;
            p.rot += p.rotSpeed;
            if (elapsed > 2500) p.opacity -= 0.015;

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate((p.rot * Math.PI) / 180);
            ctx.globalAlpha = Math.max(0, p.opacity);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            ctx.restore();
        });

        if (elapsed < 3800) {
            requestAnimationFrame(renderConfetti);
        } else {
            canvas.remove();
        }
    }

    requestAnimationFrame(renderConfetti);
}
