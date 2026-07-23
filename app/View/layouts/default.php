<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'OLMS - Library Management System') ?></title>
    <meta name="description" content="OLMS — Online Library Management System. Search, borrow, and manage your library books.">
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
            <a class="navbar-brand" href="<?= url('/') ?>">📚 OLMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/home/profile') ?>">👤 My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/my_books') ?>">📚 My Books</a>
                        </li>
                        <li class="nav-item">
                            <?php require APP . 'View/layouts/partials/theme-switcher.php'; ?>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/home/logout') ?>">🚪 Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <?php require APP . 'View/layouts/partials/theme-switcher.php'; ?>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/home/login') ?>">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= htmlspecialchars($_SESSION['login_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?= $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <h6>About OLMS</h6>
                    <p>Your Online Library Management System</p>
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
    <script src="<?= url('/js/app.js') ?>"></script>
</body>
</html>