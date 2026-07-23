<?php
/**
 * User Registration View
 */
?>
<div class="row g-0">
    <div class="col-md-10 ms-md-auto">
        <div class="content-wrapper py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h1 class="h3 mb-3 fw-normal">Create Account</h1>
                                <p class="text-muted">Join our library community today!</p>
                            </div>

                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($_SESSION['message']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                            <?php endif; ?>

                            <form method="POST" action="<?= url('/home/register') ?>" class="row g-3">
                                <?= csrf_field() ?>
                                <div class="col-12">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-12">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">Register</button>
                                </div>
                            </form>

                            <div class="col-12 text-center mt-4">
                                <p class="mb-0">Already have an account? <a href="<?= url('/home/login') ?>" class="text-decoration-none">Login here</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>