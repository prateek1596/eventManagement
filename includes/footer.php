<?php
$includeCharts = $includeCharts ?? false;
$includeAi = $includeAi ?? false;
?>
</main>
<footer class="app-footer">EventSpace <span aria-hidden="true">·</span> Make room for better moments.</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(eventspace_url('assets/js/app.js')) ?>"></script>
<?php if ($includeCharts): ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script><script src="<?= e(eventspace_url('assets/js/charts.js')) ?>"></script><?php endif; ?>
<?php if ($includeAi): ?><script src="<?= e(eventspace_url('assets/js/ai.js')) ?>"></script><?php endif; ?>
</body>
</html>
