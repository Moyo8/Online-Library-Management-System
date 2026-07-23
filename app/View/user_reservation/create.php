<?php if ($book): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <img src="https://via.placeholder.com/150x200?=Book+Cover" class="img-fluid rounded" alt="Book Cover">
    </div>
    <div class="col-md-8">
        <h2><?= htmlspecialchars($book['title']) ?></h2>
        <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
        <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?></p>
        <p>
            <strong>Availability:</strong>
            <span class="badge bg-<?= $available_copies > 0 ? 'success' : 'danger' ?>">
                <?= $available_copies ?>/<?= $book['quantity'] ?> copies available
            </span>
        </p>
    </div>
</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="alert alert-<?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($book && $available_copies == 0 && !$existing_reservation): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Place Reservation</h5>
    </div>
    <div class="card-body">
        <p>All copies of this book are currently checked out. You can reserve a copy and will be notified when it becomes available.</p>
        <form method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="book_id" value="<?= $_GET['book_id'] ?>">
            <button type="submit" class="btn btn-primary">Reserve This Book</button>
            <a href="<?= url('/search') ?>?q=&nbsp;" class="btn btn-outline-secondary">Back to Search</a>
        </form>
    </div>
</div>
<?php elseif ($book && $available_copies > 0): ?>
<div class="alert alert-info">
    This book is currently available (<strong><?= $available_copies ?>/<?= $book['quantity'] ?> copies</strong>). Would you prefer to <a href="<?= url('/transactions/issue') ?>?user_id=<?= $_SESSION['user_id'] ?>&book_id=<?= $_GET['book_id'] ?>" class="alert-link">borrow it now</a> instead of reserving?
</div>
<?php endif; ?>

<?php if ($existing_reservation): ?>
<div class="alert alert-warning">
    You already have a pending reservation for this book. You can view your reservations in <a href="<?= url('/my/books') ?>" class="alert-link">My Books</a>.
</div>
<?php endif; ?>