<?php
/**
 * includes/footer.php
 * Bagian bawah halaman admin/penjual.
 * Proyek: PutPutMart - Praktikum Pemrograman Basis Data
 */
?>
        </main>

        <!-- Footer -->
        <footer class="footer d-flex justify-content-between align-items-center">
            <span>&copy; <?= date('Y') ?> <strong>PutPutMart</strong> - Pemrograman Basis Data (Praktikum)</span>
            <span class="badge bg-light text-secondary border">Role: <?= htmlspecialchars(auth_role()) ?> &bull; Native PHP + PDO Active</span>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
