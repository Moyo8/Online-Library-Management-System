<?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>💰 My Fines</h2>
    <a href="<?= url('/my/books') ?>" class="btn btn-outline-primary">← Back to My Books</a>
</div>

<?php if (empty($fines)): ?>
    <div class="alert alert-info">
        <div class="d-flex align-items-center">
            <div style="font-size: 3rem; margin-right: 1rem;">✅</div>
            <div>
                <h5 class="mb-0">No outstanding fines!</h5>
                <p class="mb-0">You have no fines to pay at the moment.</p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Loan Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Fine Amount</th>
                    <th>Amount Paid</th>
                    <th>Balance Due</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fines as $fine): ?>
                <?php
                    $balanceDue = $fine['balance_due'];
                    $isPaidInFull = $balanceDue <= 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($fine['book_title']) ?></td>
                    <td><?= htmlspecialchars($fine['book_author']) ?></td>
                    <td><?= htmlspecialchars($fine['issue_date']) ?></td>
                    <td><?= htmlspecialchars($fine['due_date']) ?></td>
                    <td><?= htmlspecialchars($fine['return_date']) ?></td>
                    <td>$<?= number_format($fine['fine'], 2) ?></td>
                    <td>$<?= number_format($fine['fine_paid'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $isPaidInFull ? 'success' : 'warning' ?>">
                            $<?= number_format($balanceDue, 2) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>