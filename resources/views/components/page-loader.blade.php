<!-- Universal Page Loader & Interaction Feedback -->
<div id="page-loader-overlay"
     class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-950/20 backdrop-blur-[3px] opacity-0 pointer-events-none transition-opacity duration-300 ease-out"
     role="status"
     aria-live="polite"
     aria-hidden="true">

    <!-- Top Neon Ambient Progress Bar -->
    <div class="fixed top-0 inset-x-0 h-1 z-[10000] overflow-hidden bg-transparent">
        <div id="page-loader-progress-bar"
             class="h-full w-0 bg-gradient-to-r from-teal-400 via-emerald-400 to-cyan-300 shadow-[0_0_12px_rgba(20,184,166,0.8)] transition-all duration-300 ease-out">
        </div>
    </div>

    <!-- Center Floating Glassmorphic Status Pill -->
    <div class="relative mx-4 flex flex-col items-center p-6 sm:p-7 rounded-3xl bg-white/95 backdrop-blur-md border border-white/80 shadow-2xl shadow-emerald-950/15 max-w-xs w-full text-center transition-all duration-300 transform scale-95"
         id="page-loader-card">
        
        <!-- Glowing Ambient Ring with Telehealth Spinner -->
        <div class="relative w-16 h-16 flex items-center justify-center mb-3.5">
            <!-- Ambient breathing halo -->
            <div class="absolute -inset-2 rounded-full bg-gradient-to-tr from-emerald-400 via-teal-300 to-green-400 opacity-60 blur-lg animate-pulse"></div>
            
            <!-- Outer spinning gradient ring -->
            <div class="w-16 h-16 rounded-full border-3 border-teal-100 border-t-teal-600 border-r-emerald-500 animate-spin"></div>
            
            <!-- Center Logo / Heart Icon with gentle pulse -->
            <div class="absolute inset-2 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-600 flex items-center justify-center text-white shadow-md shadow-teal-600/30">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
        </div>

        <!-- Animated Status Text -->
        <h4 id="page-loader-title" class="text-sm font-bold text-slate-800 tracking-tight">
            Memproses Respon...
        </h4>
        <p id="page-loader-subtext" class="text-xs text-slate-500 mt-1 leading-relaxed">
            Menyiapkan data & halaman sistem
        </p>

        <!-- Wave Bouncing Dots -->
        <div class="flex items-center gap-1.5 mt-3.5">
            <span class="w-2 h-2 rounded-full bg-teal-500 animate-bounce [animation-delay:-0.3s]"></span>
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce [animation-delay:-0.15s]"></span>
            <span class="w-2 h-2 rounded-full bg-teal-400 animate-bounce"></span>
        </div>
    </div>
</div>

<script>
    (function () {
        const overlay = document.getElementById('page-loader-overlay');
        const progressBar = document.getElementById('page-loader-progress-bar');
        const card = document.getElementById('page-loader-card');
        const titleEl = document.getElementById('page-loader-title');
        const subtextEl = document.getElementById('page-loader-subtext');

        let progressInterval = null;
        let safetyTimeout = null;

        window.showPageLoader = function (title = 'Memproses Respon...', subtext = 'Menyiapkan data & halaman sistem') {
            if (!overlay) return;

            if (titleEl) titleEl.textContent = title;
            if (subtextEl) subtextEl.textContent = subtext;

            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            overlay.setAttribute('aria-hidden', 'false');

            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }

            // Start smooth progress animation
            if (progressBar) {
                progressBar.style.width = '25%';
                clearInterval(progressInterval);
                progressInterval = setInterval(() => {
                    const current = parseFloat(progressBar.style.width) || 25;
                    if (current < 88) {
                        progressBar.style.width = (current + Math.random() * 12) + '%';
                    }
                }, 300);
            }

            // Safety timeout: auto-hide after 15s in case download/cancel occurred
            clearTimeout(safetyTimeout);
            safetyTimeout = setTimeout(() => {
                window.hidePageLoader();
            }, 15000);
        };

        window.hidePageLoader = function () {
            if (!overlay) return;

            if (progressBar) {
                progressBar.style.width = '100%';
            }

            clearInterval(progressInterval);
            clearTimeout(safetyTimeout);

            setTimeout(() => {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.setAttribute('aria-hidden', 'true');

                if (card) {
                    card.classList.remove('scale-100');
                    card.classList.add('scale-95');
                }

                if (progressBar) {
                    setTimeout(() => {
                        progressBar.style.width = '0%';
                    }, 200);
                }
            }, 150);
        };

        // Attach listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function () {
            window.hidePageLoader();

            // 1. Intercept internal link navigation
            document.addEventListener('click', function (e) {
                const target = e.target.closest('a');
                if (!target) return;

                // Skip non-HTTP links, new tabs, downloads, and hash links
                const href = target.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
                if (href.includes('/download') || target.getAttribute('target') === '_blank' || target.hasAttribute('download')) return;
                if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
                if (target.dataset.noLoader !== undefined) return;

                // Check if same-origin URL
                try {
                    const url = new URL(target.href, window.location.href);
                    if (url.origin === window.location.origin && url.pathname !== window.location.pathname) {
                        window.showPageLoader('Memuat Halaman...', 'Menghubungkan ke layanan...');
                    }
                } catch (err) {}
            });

            // 2. Intercept form submissions
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || form.dataset.noLoader !== undefined) return;

                let title = 'Memproses Data...';
                let subtext = 'Menyimpan dan memperbarui sistem...';

                // Customize text based on form action or contents
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    const btnText = (submitBtn.textContent || submitBtn.value || '').trim();
                    if (/cari|filter|terapkan/i.test(btnText)) {
                        title = 'Mencari Data...';
                        subtext = 'Menyaring hasil penelusuran...';
                    } else if (/simpan|buat|daftar|tambah/i.test(btnText)) {
                        title = 'Menyimpan Data...';
                        subtext = 'Memvalidasi dan mencatat informasi...';
                    } else if (/hapus|delete/i.test(btnText)) {
                        title = 'Menghapus Data...';
                        subtext = 'Memperbarui catatan database...';
                    } else if (/masuk|login/i.test(btnText)) {
                        title = 'Verifikasi Kredensial...';
                        subtext = 'Membuka akses dashboard Anda...';
                    }
                }

                window.showPageLoader(title, subtext);
            });
        });

        // 3. Dismiss loader if browser restored page from cache (bfcache)
        window.addEventListener('pageshow', function (event) {
            window.hidePageLoader();
        });

        // 4. Handle Escape key to cancel
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.hidePageLoader();
            }
        });
    })();
</script>

