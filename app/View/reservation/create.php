<div class="card">
    <div class="card-header">
        <h5>Create New Reservation</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('/reservations/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select User <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="book_id" class="form-label">Select Book <span class="text-danger">*</span></label>
                        <select class="form-select" id="book_id" name="book_id" required>
                            <option value="">-- Select Book --</option>
                            <?php foreach ($books as $book): ?>
                                <option value="<?= $book['id'] ?>">
                                    <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary me-2">Create Reservation</button>
                <a href="<?= url('/reservations') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>