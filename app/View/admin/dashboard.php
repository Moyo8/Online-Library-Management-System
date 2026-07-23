<?php
// $stats is assigned by the controller
?>
<div class="mb-5 dashboard-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="dashboard-title"><i class="bi bi-grid-1x2-fill me-2" style="color: var(--primary);"></i>Dashboard</h1>
            <p class="dashboard-subtitle">Welcome back! Monitor your library performance in real-time.</p>
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
                    <li><a class="dropdown-item" href="<?= url('/books/create') ?>"><i class="bi bi-plus-circle me-2"></i>Add New Book</a></li>
                    <li><a class="dropdown-item" href="<?= url('/users/create') ?>"><i class="bi bi-person-plus me-2"></i>Add New User</a></li>
                    <li><a class="dropdown-item" href="<?= url('/transactions/issue') ?>"><i class="bi bi-box-arrow-up-right me-2"></i>Issue Book</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('/reports') ?>"><i class="bi bi-bar-chart-line me-2"></i>View Reports</a></li>
                    <li><a class="dropdown-item" href="<?= url('/ai/insights') ?>"><i class="bi bi-robot me-2"></i>AI Assistant</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if (isset($stats['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> <?= htmlspecialchars($stats['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php else: ?>
    <!-- Main Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-primary h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Total Books</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-book-fill"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['total_books'] ?? 0 ?></div>
                    <small class="text-white-50">Library Collection</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-success h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Available Books</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['available_books'] ?? 0 ?></div>
                    <small class="text-white-50">Ready for Checkout</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-info h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['total_users'] ?? 0 ?></div>
                    <small class="text-white-50">Registered Members</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-warning h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Active Loans</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['active_loans'] ?? 0 ?></div>
                    <small class="text-white-50">Currently Borrowed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Row -->
    <div class="row g-4 mb-5 stat-row-2">
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
                    <div class="stat-value"><?= $stats['overdue_books'] ?? 0 ?></div>
                    <small class="text-white-50">Needs Attention</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-primary h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Today's Issues</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['todays_issues'] ?? 0 ?></div>
                    <small class="text-white-50">Books Issued Today</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-success h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Today's Returns</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-box-arrow-in-down-left"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['todays_returns'] ?? 0 ?></div>
                    <small class="text-white-50">Books Returned Today</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3 stat-card-animated">
            <div class="card stat-card bg-secondary h-100">
                <div class="card-body">
                    <div class="stat-card-top">
                        <div class="stat-card-info">
                            <div class="stat-label">Fines Collected</div>
                        </div>
                        <div class="dashboard-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <div class="stat-value">$<?= number_format($stats['total_fines'] ?? 0, 0) ?></div>
                    <small class="text-white-50">Revenue This Month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Details Section -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="dashboard-section-title"><i class="bi bi-graph-up-arrow"></i> Library Activity Trend</h5>
                    <span class="badge bg-primary" style="font-size: 0.75rem;">Last 7 Days</span>
                </div>
                <div class="card-body p-0">
                    <div style="height: 280px; padding: 1.25rem;">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="dashboard-section-title"><i class="bi bi-speedometer2"></i> Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="quick-stat-label">Avg Daily Issues</div>
                            <div class="quick-stat-value text-primary"><?= round($stats['avg_daily_issues'] ?? 0, 1) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="quick-stat-label">Avg Daily Returns</div>
                            <div class="quick-stat-value text-success"><?= round($stats['avg_daily_returns'] ?? 0, 1) ?></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="quick-stat-label">Occupancy Rate</div>
                            <div class="progress mt-1">
                                <div class="progress-bar bg-success" style="width: <?= $stats['occupancy_rate'] ?? 0 ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $stats['occupancy_rate'] ?? 0 ?>%</small>
                        </div>
                        <div class="col-6">
                            <div class="quick-stat-label">Member Growth</div>
                            <div class="progress mt-1">
                                <div class="progress-bar bg-info" style="width: <?= min(100, max(0, $stats['member_growth_rate'] ?? 0)) ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $stats['member_growth_rate'] ?? 0 ?>%</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="quick-stat-label">Reservation Rate</div>
                            <div class="quick-stat-value"><?= $reservation_rate ?? 0 ?>%</div>
                        </div>
                        <div class="col-6">
                            <div class="quick-stat-label">User Satisfaction</div>
                            <div class="quick-stat-value"><?= $satisfaction_rate ?? 100 ?><small>/100</small></div>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: <?= max(0, min(100, 100 - ($stats['occupancy_rate'] ?? 0))) ?>%"></div>
                    </div>
                    <small class="text-muted">System Availability</small>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Activity Chart — Premium Theme-Aware Config
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('activityChart');
        if (ctx && typeof Chart !== 'undefined') {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';
            const textColor = isDark ? '#9ca3af' : '#64748b';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Books Issued',
                        data: <?= json_encode($stats['weekly_issues_data'] ?? [0,0,0,0,0,0,0]) ?>,
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
                        data: <?= json_encode($stats['weekly_returns_data'] ?? [0,0,0,0,0,0,0]) ?>,
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