<div class="card">
    <div class="card-header">
        <h5>Fulfill Reservation</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <p><strong>Book:</strong> <?= htmlspecialchars($reservation['book_title']) ?> by <?= htmlspecialchars($reservation['book_author']) ?></p>
            <p><strong>User:</strong> <?= htmlspecialchars($reservation['user_name']) ?> (<?= htmlspecialchars($reservation['user_email']) ?>)</p>
            <p><strong>Reservation Date:</strong> <?= htmlspecialchars($reservation['reservation_date']) ?></p>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="issue_book" name="issue_book" checked>
            <label class="form-check-label" for="issue_book">
                Automatically issue the book to the user after fulfilling this reservation
            </label>
        </div>

        <form method="POST" action="<?= url('/reservations/fulfill/' . $reservation['id']) ?>">
            <?= csrf_field() ?>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-success me-2">Fulfill Reservation</button>
                <a href="<?= url('/reservations') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>