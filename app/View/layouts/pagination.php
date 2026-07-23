<?php if (!empty($totalPages) && $totalPages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-4">
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $urlBase . ($urlBase ? '&' : '?') . 'page=' . ($currentPage - 1) ?>" aria-label="Previous">
                    ← Previous
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link" aria-hidden="true">← Previous</span></li>
        <?php endif; ?>

        <?php
        // Determine which page numbers to show
        $range = 2; // Number of pages to show on each side of current page
        $start = max(1, $currentPage - $range);
        $end = min($totalPages, $currentPage + $range);

        // Adjust if we're near the beginning or end
        if ($start > 1) {
            $start = max(1, $start - 1);
        }
        if ($end < $totalPages) {
            $end = min($totalPages, $end + 1);
        }
        ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $currentPage): ?>
                <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $urlBase . ($urlBase ? '&' : '?') . '?') . 'page=' . $i ?>"><?= $i ?></a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $urlBase . ($urlBase ? '&' : '?') . 'page=' . ($currentPage + 1) ?>" aria-label="Next">
                    Next →
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link" aria-hidden="true">Next →</span></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>