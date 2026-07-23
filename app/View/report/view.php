<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>
            <?php
            switch ($report_type) {
                case 'book_circulation':
                    echo 'Book Circulation Report';
                    break;
                case 'user_activity':
                    echo 'User Activity Report';
                    break;
                case 'fines':
                    echo 'Fines Report';
                    break;
                case 'overdue':
                    echo 'Overdue Books Report';
                    break;
                case 'reservation':
                    echo 'Reservation Report';
                    break;
                default:
                    echo 'Report';
            }
            ?>
        </h5>
        <div>
            <?php if (in_array($report_type, ['book_circulation', 'user_activity', 'fines'])): ?>
                <a href="<?= url('/reports/export-csv') ?>?type=<?= urlencode($report_type) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-sm btn-outline-success me-1">Export CSV</a>
                <a href="<?= url('/reports/export-json') ?>?type=<?= urlencode($report_type) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-sm btn-outline-info">Export JSON</a>
            <?php else: ?>
                <a href="<?= url('/reports/export-csv') ?>?type=<?= urlencode($report_type) ?>" class="btn btn-sm btn-outline-success me-1">Export CSV</a>
                <a href="<?= url('/reports/export-json') ?>?type=<?= urlencode($report_type) ?>" class="btn btn-sm btn-outline-info">Export JSON</a>
            <?php endif; ?>
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

        <?php if (in_array($report_type, ['book_circulation', 'user_activity', 'fines'])): ?>
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Start Date:</label>
                        <input type="date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date:</label>
                        <input type="date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" disabled>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($data)): ?>
            <div class="text-center py-4">
                <p>No data found for the selected criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <?php
                            // Define headers based on report type
                            switch ($report_type) {
                                case 'book_circulation':
                                    ?>
                                    <th>#</th>
                                    <th>Book ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Circulation Count</th>
                                    <?php
                                    break;
                                case 'user_activity':
                                    ?>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Activity Count</th>
                                    <?php
                                    break;
                                case 'fines':
                                    ?>
                                    <th>Fines Issued</th>
                                    <th>Fines Collected</th>
                                    <th>Fines Outstanding</th>
                                    <?php
                                    break;
                                case 'overdue':
                                    ?>
                                    <th>#</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>User Name</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Fine Amount ($)</th>
                                    <?php
                                    break;
                                case 'reservation':
                                    ?>
                                    <th>#</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>User Name</th>
                                    <th>Reservation Date</th>
                                    <th>Status</th>
                                    <?php
                                    break;
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($report_type === 'fines' && !empty($data[0])): ?>
                            <!-- Special handling for fines report -->
                            <tr>
                                <td>$<?= number_format((float)($data[0]['fines_issued'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)($data[0]['fines_collected'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)($data[0]['fines_outstanding'] ?? 0), 2) ?></td>
                            </tr>
                        <?php elseif ($report_type === 'fines'): ?>
                            <tr>
                                <td colspan="3">No data available</td>
                            </tr>
                        <?php else: ?>
                            <!-- Standard handling for other reports -->
                            <?php foreach ($data as $index => $row): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <?php
                                    switch ($report_type) {
                                        case 'book_circulation':
                                            ?>
                                            <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['author'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['circulation_count'] ?? 0) ?></td>
                                            <?php
                                            break;
                                        case 'user_activity':
                                            ?>
                                            <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['activity_count'] ?? 0) ?></td>
                                            <?php
                                            break;
                                        case 'overdue':
                                            ?>
                                            <td><?= htmlspecialchars($row['book_title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['author'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['user_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['issue_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['due_date'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['days_overdue'] ?? 0) ?></td>
                                            <td>$<?= number_format((float)($row['fine_amount'] ?? 0), 2) ?></td>
                                            <?php
                                            break;
                                        case 'reservation':
                                            ?>
                                            <td><?= htmlspecialchars($row['book_title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['author'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['user_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['reservation_date'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-<?=
                                                    $row['status'] === 'pending' ? 'warning' :
                                                    (($row['status'] === 'fulfilled') ? 'success' : 'secondary')
                                                ?>">
                                                    <?= ucfirst($row['status']) ?>
                                                </span>
                                            </td>
                                            <?php
                                            break;
                                    }
                                    ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>