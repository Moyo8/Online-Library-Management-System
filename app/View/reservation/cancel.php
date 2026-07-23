<div class="card">
    <div class="card-header">
        <h5>Cancel Reservation</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <p><strong>Book:</strong> <?= htmlspecialchars($reservation['book_title']) ?> by <?= htmlspecialchars($reservation['book_author']) ?></p>
            <p><strong>User:</strong> <?= htmlspecialchars($reservation['user_name']) ?> (<?= htmlspecialchars($reservation['user_email']) ?>)</p>
            <p><strong>Reservation Date:</strong> <?= htmlspecialchars($reservation['reservation_date']) ?></p>
        </div>

        <div class="alert alert-warning">
            <h6>Confirm Cancellation</h6>
            <p>Are you sure you want to cancel this reservation? This action cannot be undone.</p>
        </div>

        <form method="POST" action="<?= url('/reservations/cancel/' . $reservation['id']) ?>">
            <?= csrf_field() ?>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-danger me-2">Yes, Cancel Reservation</button>
                <a href="<?= url('/reservations') ?>" class="btn btn-outline-secondary">Keep Reservation</a>
            </div>
        </form>
    </div>
</div>