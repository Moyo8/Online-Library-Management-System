<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Reservation Management</h5>
        <div>
            <a href="<?= url('/reservations/create') ?>" class="btn btn-sm btn-outline-primary me-2">Create Reservation</a>
            <form method="GET" action="<?= url('/reservations') ?>" class="d-flex">
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
                        <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="fulfilled" <?= $filters['status'] === 'fulfilled' ? 'selected' : '' ?>>Fulfilled</option>
                        <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                <?php if (!empty($filters['user_id']) || !empty($filters['book_id']) || !empty($filters['status'])): ?>
                    <a href="<?= url('/reservations') ?>" class="btn btn-sm btn-outline-danger ms-1">Clear</a>
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
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Total Reservations</h6>
                            <p class="card-text display-6"><?= $stats['total_reservations'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Pending</h6>
                            <p class="card-text display-6"><?= $stats['pending_reservations'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Fulfilled</h6>
                            <p class="card-text display-6"><?= $stats['fulfilled_reservations'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Cancelled</h6>
                            <p class="card-text display-6"><?= $stats['cancelled_reservations'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
            <div class="text-center py-4">
                <p>No reservations found.</p>
                <?php if (!empty($filters['user_id']) || !empty($filters['book_id']) || !empty($filters['status'])): ?>
                    <p class="text-muted">Try adjusting your search filters.</p>
                <?php endif; ?>
                <a href="<?= url('/reservations/create') ?>" class="btn btn-primary">Create First Reservation</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Book</th>
                            <th>Reservation Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $index => $reservation): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($reservation['user_name']) ?></td>
                            <td><?= htmlspecialchars($reservation['book_title']) ?></td>
                            <td><?= htmlspecialchars($reservation['reservation_date']) ?></td>
                            <td>
                                <span class="badge bg-<?=
                                    $reservation['status'] === 'pending' ? 'warning' :
                                    (($reservation['status'] === 'fulfilled') ? 'success' : 'secondary')
                                ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <a href="<?= url('/reservations/view/' . $reservation['id']) ?>" class="btn btn-sm btn-outline-info me-1">Details</a>
                                        <a href="<?= url('/reservations/fulfill/' . $reservation['id']) ?>" class="btn btn-sm btn-success">Fulfill</a>
                                        <a href="<?= url('/reservations/cancel/' . $reservation['id']) ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                                    <?php elseif ($reservation['status'] === 'fulfilled'): ?>
                                        <a href="<?= url('/reservations/view/' . $reservation['id']) ?>" class="btn btn-sm btn-outline-info">Details</a>
                                    <?php else: ?>
                                        <a href="<?= url('/reservations/view/' . $reservation['id']) ?>" class="btn btn-sm btn-outline-info">Details</a>
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
                        $baseUrl = url('/reservations') . '?' . http_build_query($queryParams);
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