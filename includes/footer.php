<?php

/**
 * includes/footer.php
 * Bagian bawah layout Annakoot Enterprise & tema Refract.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */
?>
</main>

<!-- Annakoot Minimalist Footer -->
<footer class="footer mt-auto py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
        <span>&copy; <?= date('Y') ?> <strong style="font-family: var(--font-display); color: var(--color-ink);">PutPutMart</strong> </span>
    </div>
</footer>
</div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sidebar Toggle & Persistence Script (Annakoot Style) -->
<script>
    (function() {
        const layout = document.getElementById('appLayout');
        const toggleBtn = document.getElementById('sidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');

        // Restore collapsed state on desktop
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            layout.classList.add('sidebar-collapsed');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    layout.classList.toggle('sidebar-mobile-open');
                } else {
                    layout.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebar_collapsed', layout.classList.contains('sidebar-collapsed'));
                }
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                layout.classList.remove('sidebar-mobile-open');
            });
        }
    })();
</script>
</body>

</html>
