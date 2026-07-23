<?php
// Variables assigned by the controller:
// $borrowed_books_count, $reservations_count, $overdue_books_count, $total_fines_owed, $recent_transactions_data
?>

<!-- Dashboard Header -->
<div class="mb-5 dashboard-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="dashboard-title"><i class="bi bi-grid-1x2-fill me-2" style="color: var(--primary);"></i>My Dashboard</h1>
            <p class="dashboard-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Reader') ?>! Here's your library overview.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-lightning-charge-fill"></i> Quick Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= url('/search') ?>"><i class="bi bi-search me-2"></i>Search Books</a></li>
                    <li><a class="dropdown-item" href="<?= url('/my/books') ?>"><i class="bi bi-book me-2"></i>My Books</a></li>
                    <li><a class="dropdown-item" href="<?= url('/user/reservation/create') ?>"><i class="bi bi-bookmark-plus me-2"></i>Reservations</a></li>
                    <li><a class="dropdown-item" href="<?= url('/user/fines') ?>"><i class="bi bi-credit-card me-2"></i>View Fines</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('/user/ai') ?>"><i class="bi bi-robot me-2"></i>AI Assistant</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Main Stats -->
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-md-3 stat-card-animated">
        <div class="card stat-card bg-primary h-100">
            <div class="card-body">
                <div class="stat-card-top">
                    <div class="stat-card-info">
                        <div class="stat-label">Borrowed Books</div>
                    </div>
                    <div class="dashboard-icon">
                        <i class="bi bi-book-fill"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $borrowed_books_count ?? 0 ?></div>
                <small class="text-white-50">Currently Checked Out</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 stat-card-animated">
        <div class="card stat-card bg-success h-100">
            <div class="card-body">
                <div class="stat-card-top">
                    <div class="stat-card-info">
                        <div class="stat-label">Active Reservations</div>
                    </div>
                    <div class="dashboard-icon">
                        <i class="bi bi-bookmark-check-fill"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $reservations_count ?? 0 ?></div>
                <small class="text-white-50">Pending Pickup</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 stat-card-animated">
        <div class="card stat-card bg-danger h-100">
            <div class="card-body">
                <div class="stat-card-top">
                    <div class="stat-card-info">
                        <div class="stat-label">Overdue Books</div>
                    </div>
                    <div class="dashboard-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $overdue_books_count ?? 0 ?></div>
                <small class="text-white-50">Needs Attention</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 stat-card-animated">
        <a href="<?= url('/user/fines') ?>" class="text-decoration-none">
            <div class="card stat-card bg-warning h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Fines Owed</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <div class="stat-value">$<?= number_format($total_fines_owed ?? 0, 0) ?></div>
                    <small class="text-white-50">Outstanding Balance</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Charts and Details Section -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="dashboard-section-title"><i class="bi bi-graph-up-arrow"></i> My Reading Activity</h5>
                <span class="badge bg-primary" style="font-size: 0.75rem;">Last 7 Days</span>
            </div>
            <div class="card-body p-0">
                <div style="height: 280px; padding: 1.25rem;">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="dashboard-section-title"><i class="bi bi-speedometer2"></i> Library Summary</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="quick-stat-label">Books This Month</div>
                        <div class="quick-stat-value text-primary"><?= $monthly_book_count ?? 0 ?></div>
                    </div>
                    <div class="col-6">
                        <div class="quick-stat-label">On-Time Rate</div>
                        <div class="quick-stat-value text-success"><?= number_format($on_time_rate ?? 100, 1) ?>%</div>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="quick-stat-label">Reading Progress</div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: <?= $on_time_rate ?? 100 ?>%"></div>
                        </div>
                        <small class="text-muted">On-time return rate</small>
                    </div>
                    <div class="col-6">
                        <div class="quick-stat-label">Reservation Fill</div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" style="width: <?= $reservation_pending_rate ?? 0 ?>%"></div>
                        </div>
                        <small class="text-muted">Pending reservations</small>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="quick-stat-label">Reliability Score</div>
                        <div class="quick-stat-value"><i class="bi bi-star-fill" style="color: var(--warning); font-size: 0.9em;"></i> <?= round(($on_time_rate ?? 100) / 20, 1) ?>/5.0</div>
                    </div>
                    <div class="text-end">
                        <div class="quick-stat-label">Status</div>
                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4 mt-2">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="dashboard-section-title"><i class="bi bi-clock-history"></i> Recent Activity</h5>
                <a href="<?= url('/my/books') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_transactions_data)): ?>
                    <div class="text-center py-4">
                        <div style="font-size: 3rem; margin-bottom: 1rem;"><i class="bi bi-book" style="color: var(--primary);"></i></div>
                        <h5>No recent activity</h5>
                        <p class="text-muted">Start exploring our library collection!</p>
                        <a href="<?= url('/search') ?>" class="btn btn-primary"><i class="bi bi-search me-1"></i>Browse Books</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_transactions_data as $transaction): ?>
                        <div class="activity-item">
                            <div>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($transaction['book_title']) ?></h6>
                                <p class="mb-1 text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($transaction['book_author']) ?></p>
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>Issued: <?= htmlspecialchars($transaction['issue_date']) ?></small>
                            </div>
                            <div class="text-end">
                                <?php if ($transaction['return_date']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Returned</span>
                                    <br><small class="text-muted"><?= htmlspecialchars($transaction['return_date']) ?></small>
                                <?php else: ?>
                                    <?php
                                        $due = new DateTime($transaction['due_date']);
                                        $today = new DateTime();
                                        $isOverdue = $today > $due;
                                    ?>
                                    <span class="badge bg-<?= $isOverdue ? 'danger' : 'info' ?>">
                                        <i class="bi bi-<?= $isOverdue ? 'exclamation-circle' : 'hourglass-split' ?> me-1"></i>
                                        <?= $isOverdue ? 'Overdue' : 'Borrowed' ?>
                                    </span>
                                    <br><small class="text-muted">Due: <?= htmlspecialchars($transaction['due_date']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
    // User Activity Chart — Premium Theme-Aware Config
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('userActivityChart');
        if (ctx && typeof Chart !== 'undefined') {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';
            const textColor = isDark ? '#9ca3af' : '#64748b';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Books Borrowed',
                        data: <?= json_encode($weekly_activity_data['borrowed'] ?? [0,0,0,0,0,0,0]) ?>,
                        borderColor: '#7C3AED',
                        backgroundColor: 'rgba(124, 58, 237, 0.15)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointBackgroundColor: '#7C3AED',
                        pointBorderColor: isDark ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    }, {
                        label: 'Books Returned',
                        data: <?= json_encode($weekly_activity_data['returned'] ?? [0,0,0,0,0,0,0]) ?>,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: isDark ? '#1e293b' : '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 20,
                                font: { size: 13, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#374151' : '#0f172a',
                            titleFont: { size: 14, weight: '700' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: textColor, font: { weight: '600' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor, font: { weight: '600' } }
                        }
                    }
                }
            });
        }
    });
</script>