<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" action="">
            <div class="input-group">
                <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search by title, author, or ISBN..." required>
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
    </div>
    <div class="col-md-4">
        <?php if (!empty($search_query)): ?>
            <p>Showing results for "<strong><?= htmlspecialchars($search_query) ?></strong>"</p>
        <?php else: ?>
            <p>Showing all books</p>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($books)): ?>
    <?php if (!empty($search_query)): ?>
        <div class="alert alert-info">No books found matching your search.</div>
    <?php else: ?>
        <div class="alert alert-info">No books in the library yet.</div>
    <?php endif; ?>
<?php else: ?>
    <div class="row">
        <?php foreach ($books as $book): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="card-text">
                        <strong>Author:</strong> <?= htmlspecialchars($book['author']) ?><br>
                        <strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'] ?? 'N/A') ?><br>
                        <strong>Availability:</strong>
                        <span class="badge bg-<?= ($book['quantity'] - $book['issued_count']) > 0 ? 'success' : 'danger' ?>">
                            <?= max(0, $book['quantity'] - $book['issued_count']) ?>/<?= $book['quantity'] ?> copies
                        </span>
                    </p>
                    <?php if (($book['quantity'] - $book['issued_count']) > 0): ?>
                        <a href="<?= url('/user/borrow') ?>?book_id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary me-2">Borrow</a>
                    <?php endif; ?>
                    <a href="<?= url('/user/reservation/create') ?>?book_id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-secondary">Reserve</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?q=<?= urlencode($search_query) ?>&page=<?= $currentPage - 1 ?>">Previous</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
                    <?php else: ?>
                        <li class="page-item"><a class="page-link" href="?q=<?= urlencode($search_query) ?>&page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?q=<?= urlencode($search_query) ?>&page=<?= $currentPage + 1 ?>">Next</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>