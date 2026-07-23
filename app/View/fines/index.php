<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Book</th>
                <th>Fine Amount</th>
                <th>Amount Paid</th>
                <th>Balance Due</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($fines)): ?>
                <tr>
                    <td colspan="9" class="text-center">No fines found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($fines as $fine): ?>
                <tr>
                    <td><?= htmlspecialchars($fine['user_name']) ?></td>
                    <td><?= htmlspecialchars($fine['book_title']) ?></td>
                    <td>$<?= number_format($fine['fine'], 2) ?></td>
                    <td>$<?= number_format($fine['fine_paid'], 2) ?></td>
                    <td>$<?= number_format($fine['balance_due'], 2) ?></td>
                    <td><?= htmlspecialchars($fine['issue_date']) ?></td>
                    <td><?= htmlspecialchars($fine['due_date']) ?></td>
                    <td><?= htmlspecialchars($fine['return_date']) ?></td>
                    <td>
                        <?php if ($fine['balance_due'] > 0): ?>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#payFineModal"
                                data-id="<?= $fine['id'] ?>"
                                data-balance="<?= $fine['balance_due'] ?>">
                            Pay Fine
                        </button>
                        <?php else: ?>
                        <span class="bg-success">Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pay Fine Modal -->
<div class="modal fade" id="payFineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Pay Fine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="transaction_id" id="fineId">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Balance Due</label>
                        <input type="text" class="form-control" id="fineBalance" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount to Pay</label>
                        <input type="number" class="form-control" name="amount_paid" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-text">Minimum payment: $0.01</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Process Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fill pay fine modal when shown
    const payFineModal = document.getElementById('payFineModal');
    payFineModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        document.getElementById('fineId').value = button.getAttribute('data-id');
        document.getElementById('fineBalance').value = '$' + parseFloat(button.getAttribute('data-balance')).toFixed(2);
    });
</script>