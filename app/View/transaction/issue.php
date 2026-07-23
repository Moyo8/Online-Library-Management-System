<div class="card">
    <div class="card-header">
        <h5>Issue Book to User</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('/transactions/issue-book') ?>">
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
                                <?php
                                $bookModel = new App\Models\Book();
                                $available = $bookModel->getAvailableCopies($book['id']);
                                $disabled = $available <= 0 ? 'disabled' : '';
                                $availabilityText = $available > 0 ? "($available available)" : "(Out of stock)";
                                ?>
                                <option value="<?= $book['id'] ?>" <?= $disabled ?>>
                                    <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?> <?= $availabilityText ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="issue_date" class="form-label">Issue Date</label>
                        <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary me-2">Issue Book</button>
                <a href="<?= url('/transactions') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>