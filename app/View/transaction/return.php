<div class="card">
    <div class="card-header">
        <h5>Return Book</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <p><strong>Book:</strong> <?= htmlspecialchars($transaction['book_title']) ?> by <?= htmlspecialchars($transaction['book_author']) ?></p>
            <p><strong>User:</strong> <?= htmlspecialchars($transaction['user_name']) ?> (<?= htmlspecialchars($transaction['user_email']) ?>)</p>
            <p><strong>Issue Date:</strong> <?= htmlspecialchars($transaction['issue_date']) ?></p>
            <p><strong>Due Date:</strong> <?= htmlspecialchars($transaction['due_date']) ?></p>
        </div>

        <?php
        // Calculate overdue fine if applicable
        $dueDate = new DateTime($transaction['due_date']);
        $today = new DateTime();
        $overdueDays = $dueDate->diff($today)->days;
        $overdueFine = max(0, $overdueDays) * 0.10; // $0.10 per day
        ?>

        <?php if ($overdueFine > 0): ?>
            <div class="alert alert-warning">
                <h6>Overdue Notice</h6>
                <p>This book is <strong><?= $overdueDays ?></strong> day(s) overdue.</p>
                <p>Overdue fine: <strong>$<?= number_format($overdueFine, 2) ?></strong> (at $0.10 per day)</p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/transactions/return-book/' . $transaction['id']) ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="return_date" class="form-label">Return Date</label>
                        <input type="date" class="form-control" id="return_date" name="return_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fine_paid" class="form-label">Amount Paid ($)</label>
                        <input type="number" class="form-control" id="fine_paid" name="fine_paid" min="0" step="0.01"
                               value="<?= $overdueFine > 0 ? number_format($overdueFine, 2) : '0.00' ?>">
                        <div class="form-text">Enter amount paid towards any overdue fines</div>
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="calculate_fine" name="calculate_fine" checked>
                <label class="form-check-label" for="calculate_fine">
                    Automatically calculate and apply overdue fine
                </label>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-success me-2">Return Book</button>
                <a href="<?= url('/transactions') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>