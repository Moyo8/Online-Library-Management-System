<div class="login-card max-w-md mx-auto mt-10">
    <div class="logo text-center mb-4">
        <img src="https://via.placeholder.com/60x60?text=📚" alt="OLMS Logo" class="h-12 w-12 mx-auto">
        <h3 class="h5 mb-3">Library Management System</h3>
    </div>

    <form method="POST" action="<?= url('/home/login') ?>" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12">
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" name="email" required
                class="form-control form-control-lg"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="col-12">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" required
                class="form-control form-control-lg">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                Login
            </button>
        </div>
        <div class="col-12 text-center mt-3">
            <p class="mb-0">Don't have an account? <a href="<?= url('/register') ?>" class="text-decoration-underline">Register here</a></p>
        </div>
    </form>
</div>