<div class="card">
    <div class="card-header">
        <h5>Library Reports</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Books</h5>
                        <p class="card-text display-4"><?= $stats['total_books'] ?? 0 ?></p>
                        <a href="<?= url('/reports/book-circulation') ?>" class="btn btn-sm btn-outline-primary">View Circulation Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text display-4"><?= $stats['total_users'] ?? 0 ?></p>
                        <a href="<?= url('/reports/user-activity') ?>" class="btn btn-sm btn-outline-primary">View Activity Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Active Loans</h5>
                        <p class="card-text display-4"><?= $stats['active_loans'] ?? 0 ?></p>
                        <a href="<?= url('/reports/book-circulation') ?>" class="btn btn-sm btn-outline-primary">View Circulation Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Overdue Books</h5>
                        <p class="card-text display-4 text-danger"><?= $stats['overdue_books'] ?? 0 ?></p>
                        <a href="<?= url('/reports/overdue') ?>" class="btn btn-sm btn-outline-danger">View Overdue Report</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h6>Quick Links</h6>
                    <p>
                        <a href="<?= url('/reports/book-circulation') ?>" class="alert-link">Book Circulation Report</a><br>
                        <a href="<?= url('/reports/user-activity') ?>" class="alert-link">User Activity Report</a><br>
                        <a href="<?= url('/reports/fines') ?>" class="alert-link">Fines Report</a><br>
                        <a href="<?= url('/reports/overdue') ?>" class="alert-link">Overdue Books Report</a><br>
                        <a href="<?= url('/reports/reservation') ?>" class="alert-link">Reservation Report</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>