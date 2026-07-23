<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Transaction Management</h5>
        <div>
            <a href="<?= url('/transactions/issue') ?>" class="btn btn-sm btn-outline-primary me-2">Issue Book</a>
            <form method="GET" action="<?= url('/transactions') ?>" class="d-flex">
                <div class="me-2">
                    <label for="user-filter" class="form-label small mb-0">User:</label>
                    <select name="user_id" id="user-filter" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= !empty($filters['user_id']) && $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="me-2">
                    <label for="book-filter" class="form-label small mb-0">Book:</label>
                    <select name="book_id" id="book-filter" class="form-select form-select-sm">
                        <option value="">All Books</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book['id'] ?>" <?= !empty($filters['book_id']) && $filters['book_id'] == $book['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($book['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="me-2">
                    <label for="status-filter" class="form-label small mb-0">Status:</label>
                    <select name="status" id="status-filter" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active Loans</option>
                        <option value="returned" <?= $filters['status'] === 'returned' ? 'selected' : '' ?>>Returned</option>
                        <option value="overdue" <?= $filters['status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                <?php if (!empty($filters['user_id']) || !empty($filters['book_id']) || !empty($filters['status'])): ?>
                    <a href="<?= url('/transactions') ?>" class="btn btn-sm btn-outline-danger ms-1">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <?php if (!empty($stats)): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Total Transactions</h6>
                            <p class="card-text display-6"><?= $stats['total_transactions'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Active Loans</h6>
                            <p class="card-text display-6"><?= $stats['active_loans'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Overdue Books</h6>
                            <p class="card-text display-6 text-danger"><?= $stats['overdue_books'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Today's Issues</h6>
                            <p class="card-text display-6"><?= $stats['todays_issues'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($transactions)): ?>
            <div class="text-center py-4">
                <p>No transactions found.</p>
                <?php if (!empty($filters['user_id']) || !empty($filters['book_id']) || !empty($filters['status'])): ?>
                    <p class="text-muted">Try adjusting your search filters.</p>
                <?php endif; ?>
                <a href="<?= url('/transactions/issue') ?>" class="btn btn-primary">Issue First Book</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Book</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Fine</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $index => $transaction): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($transaction['user_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['book_title']) ?></td>
                            <td><?= htmlspecialchars($transaction['issue_date']) ?></td>
                            <td><?= htmlspecialchars($transaction['due_date']) ?></td>
                            <td>
                                <?= !empty($transaction['return_date']) ?
                                    htmlspecialchars($transaction['return_date']) :
                                    '<span class="text-danger">-</span>' ?>
                            </td>
                            <td>
                                $<?= number_format((float)$transaction['fine'] - (float)$transaction['fine_paid'], 2) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?=
                                    !empty($transaction['return_date']) ? 'success' :
                                    (($transaction['due_date'] < date('Y-m-d')) ? 'danger' : 'info')
                                ?>">
                                    <?= !empty($transaction['return_date']) ? 'Returned' :
                                        (($transaction['due_date'] < date('Y-m-d')) ? 'Overdue' : 'Active')
                                    ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <?php if (empty($transaction['return_date'])): ?>
                                        <a href="<?= url('/transactions/view/' . $transaction['id']) ?>" class="btn btn-sm btn-outline-info me-1">Details</a>
                                        <a href="<?= url('/transactions/return/' . $transaction['id']) ?>" class="btn btn-sm btn-outline-primary">Return Book</a>
                                    <?php else: ?>
                                        <a href="<?= url('/transactions/view/' . $transaction['id']) ?>" class="btn btn-sm btn-outline-info">Details</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <!-- Pagination -->
        <?php if (!empty($pagination)): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    // Preserve current GET parameters except page
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $baseUrl = url('/transactions') . '?' . http_build_query($queryParams);
                    ?>
                    <?php if ($pagination['page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl . '&page=' . ($pagination['page'] - 1) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i == $pagination['page']): ?>
                            <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="<?= $baseUrl . '&page=' . $i ?>"><?= $i ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl . '&page=' . ($pagination['page'] + 1) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">&raquo;</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>