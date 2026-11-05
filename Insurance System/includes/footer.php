<?php
// includes/footer.php
$inDash = strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false
       || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
?>
<?php if ($inDash): ?>
  </main>
</div>
<?php endif; ?>
<footer class="text-center text-muted py-3 small">
  &copy; <?= date('Y') ?> InsureAI – Student Prototype
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(base_url('assets/js/script.js')) ?>"></script>
</body>
</html>