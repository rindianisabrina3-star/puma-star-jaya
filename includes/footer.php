<?php
// includes/footer.php
?>
    <!-- FOOTER -->
    <footer style="background: var(--primary-dark); color: #94a3b8; padding: 60px 0 24px; margin-top: 80px; border-top: 1px solid rgba(255,255,255,0.08);" class="no-print">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 40px; margin-bottom: 40px;">
                <!-- Kolom 1: Profil Perusahaan -->
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div class="brand-badge" style="font-size: 1rem; padding: 6px 10px;">PUMA</div>
                        <h3 style="color: #ffffff; font-size: 1.2rem; margin: 0;">PT. PUMA STAR JAYA</h3>
                    </div>
                    <p style="font-size: 0.9rem; line-height: 1.7; color: #94a3b8;">
                        Penyedia jasa transportasi bus pariwisata eksekutif dan paket wisata terbaik di Indonesia. Memberikan kenyamanan, keamanan, dan pengalaman wisata tak terlupakan.
                    </p>
                </div>

                <!-- Kolom 2: Tautan Cepat -->
                <div>
                    <h4 style="color: #ffffff; font-size: 1rem; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 1px;">Navigasi</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem;">
                        <li><a href="<?= base_url('index.php') ?>" style="color: #cbd5e1; hover: color: var(--accent);"><i class="fa-solid fa-angle-right" style="color: var(--accent); margin-right: 6px;"></i> Home</a></li>
                        <li><a href="<?= base_url('index.php#tentang') ?>" style="color: #cbd5e1;"><i class="fa-solid fa-angle-right" style="color: var(--accent); margin-right: 6px;"></i> Tentang Kami</a></li>
                        <li><a href="<?= base_url('paket.php') ?>" style="color: #cbd5e1;"><i class="fa-solid fa-angle-right" style="color: var(--accent); margin-right: 6px;"></i> Paket Wisata</a></li>
                        <li><a href="<?= base_url('index.php#galeri') ?>" style="color: #cbd5e1;"><i class="fa-solid fa-angle-right" style="color: var(--accent); margin-right: 6px;"></i> Galeri Perjalanan</a></li>
                        <li><a href="<?= base_url('index.php#kontak') ?>" style="color: #cbd5e1;"><i class="fa-solid fa-angle-right" style="color: var(--accent); margin-right: 6px;"></i> Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Kontak Resmi -->
                <div>
                    <h4 style="color: #ffffff; font-size: 1rem; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 1px;">Kontak & Layanan</h4>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <i class="fa-solid fa-location-dot" style="color: var(--accent); margin-top: 4px;"></i>
                            <span>Jl. Raya Pariwisata No. 88, Pusat Layanan Tour & Travel</span>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <i class="fa-solid fa-phone" style="color: var(--accent);"></i>
                            <span>085835688224</span>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <i class="fa-solid fa-envelope" style="color: var(--accent);"></i>
                            <span>pumatour@gmail.com</span>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <i class="fa-solid fa-clock" style="color: var(--accent);"></i>
                            <span>Senin - Minggu: 08.00 - 21.00 WIB</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 24px; text-align: center; font-size: 0.85rem; color: #64748b;">
                <p>&copy; <?= date('Y') ?> PT. Puma Star Jaya Tour & Travel. All Rights Reserved. (Sumber: Penulis, 2025)</p>
            </div>
        </div>
    </footer>

    <!-- FLOATING WHATSAPP BUTTON (Animasi Wave Pulse + Float) -->
    <div class="floating-wa no-print">
        <span class="floating-wa-tooltip"><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> Chat CS Puma Tour</span>
        <a href="https://wa.me/6285835688224?text=Halo%20Admin%20PT%20Puma%20Star%20Jaya%20Tour,%20saya%20ingin%20bertanya%20informasi%20paket%20wisata" target="_blank" class="floating-wa-btn" title="Hubungi Kami di WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    </div>

    <!-- Interactive Scripts & Animations -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Hero Slider Logic (Gambar 24)
            const slides = document.querySelectorAll('.slide-item');
            const dots = document.querySelectorAll('.slider-dots .dot');
            const prevBtn = document.querySelector('.slider-prev');
            const nextBtn = document.querySelector('.slider-next');
            
            if (slides.length > 0) {
                let currentSlide = 0;
                let slideInterval;

                function showSlide(index) {
                    slides.forEach(s => s.classList.remove('active'));
                    dots.forEach(d => d.classList.remove('active'));
                    currentSlide = (index + slides.length) % slides.length;
                    slides[currentSlide].classList.add('active');
                    if (dots[currentSlide]) dots[currentSlide].classList.add('active');
                }

                function nextSlide() {
                    showSlide(currentSlide + 1);
                }

                function prevSlide() {
                    showSlide(currentSlide - 1);
                }

                if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });
                if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });

                dots.forEach((dot, idx) => {
                    dot.addEventListener('click', () => {
                        showSlide(idx);
                        resetInterval();
                    });
                });

                function resetInterval() {
                    clearInterval(slideInterval);
                    slideInterval = setInterval(nextSlide, 6000);
                }

                resetInterval();
            }

            // 2. Scroll Reveal Animations
            const revealElements = document.querySelectorAll('.paket-card, .section-title-wrap, .stat-card, .order-package-summary, .card-table');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry, idx) => {
                        if (entry.isIntersecting) {
                            setTimeout(() => {
                                entry.target.classList.add('revealed');
                            }, idx * 75);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });

                revealElements.forEach(el => {
                    el.classList.add('reveal-init');
                    observer.observe(el);
                });
            } else {
                revealElements.forEach(el => el.classList.add('revealed'));
            }
        });
    </script>
</body>
</html>
