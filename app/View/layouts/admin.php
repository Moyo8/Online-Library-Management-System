<?php $this->assign('title', 'OLMS Admin Dashboard'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'OLMS Admin Dashboard') ?></title>
    <meta name="description" content="OLMS Admin Dashboard — Manage your library, users, books, and transactions.">
    <?php require APP . 'View/layouts/partials/theme-head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= url('/css/style.css') ?>" rel="stylesheet">
    <link href="<?= url('/css/overrides.css') ?>" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='75' font-size='75'>📚</text></svg>">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= url('/admin/dashboard') ?>">📚 OLMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <?php require APP . 'View/layouts/partials/theme-switcher.php'; ?>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">👤 <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/home/logout') ?>">🚪 Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid">
        <div class="row g-0">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar d-none d-md-block">
                <div class="sidebar-header">
                    <h6 class="px-4 pt-4 pb-2 text-muted fw-bold">MENU</h6>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="<?= url('/admin/dashboard') ?>">📊 Dashboard</a></li>
                    <li><a href="<?= url('/users') ?>">👥 Users</a></li>
                    <li><a href="<?= url('/books') ?>">📖 Books</a></li>
                    <li><a href="<?= url('/transactions') ?>">💳 Transactions</a></li>
                    <li><a href="<?= url('/fines') ?>">💰 Fines</a></li>
                    <li><a href="<?= url('/reports') ?>">📈 Reports</a></li>
                    <li><a href="<?= url('/reservations') ?>">📋 Reservations</a></li>
                    <li><a href="<?= url('/ai/insights') ?>">🤖 AI Insights</a></li>
                </ul>
            </nav>

            <!-- Content Area -->
            <div class="col-md-10 ms-md-auto">
                <div class="content-wrapper">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    <?php endif; ?>

                    <?= $content ?? ''; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <h6>About OLMS</h6>
                    <p>Online Library Management System</p>
                </div>
                <div class="col-md-4 text-center">
                    <p>&copy; 2026 OLMS. All rights reserved.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?= url('/js/app.js') ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const path = window.location.pathname;
        const links = document.querySelectorAll('.sidebar-menu a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (path === href) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>