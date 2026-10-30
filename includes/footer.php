<?php

/**
 * includes/footer.php
 * Bagian bawah halaman admin/penjual.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */
?>
</main>

<!-- Refract Minimalist Footer -->
<footer class="footer mt-auto py-4">
    <div class="refract-container d-flex flex-wrap justify-content-between align-items-center mb-0 pb-0" style="padding-bottom: 0 !important;">
        <span>&copy; <?= date('Y') ?> <strong style="font-family: var(--font-display); color: var(--color-ink);">PutPutMart</strong></span>
        <span class="badge badge-refract-blue"><?= htmlspecialchars(auth_role()) ?></span>
    </div>
</footer>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
