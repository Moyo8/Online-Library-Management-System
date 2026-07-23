<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Book Inventory</h5>
        <div>
            <a href="<?= url('/books/create') ?>" class="btn btn-sm btn-outline-primary me-2">Add New Book</a>
            <form method="GET" action="<?= url('/books') ?>" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= url('/books') ?>" class="btn btn-sm btn-outline-danger ms-1">Clear</a>
                <?php endif; ?>
                <input type="checkbox" name="available" id="available-filter" class="form-check-input me-1" <?= $available ? 'checked' : '' ?>>
                <label for="available-filter" class="form-check-label mb-0 small">Available only</label>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <?php if (empty($books)): ?>
            <div class="text-center py-4">
                <p>No books found.</p>
                <?php if (!empty($search) || $available): ?>
                    <p class="text-muted">Try adjusting your search filters.</p>
                <?php endif; ?>
                <a href="<?= url('/books/create') ?>" class="btn btn-primary">Add First Book</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Publisher</th>
                            <th>Published Year</th>
                            <th>ISBN</th>
                            <th>Total Copies</th>
                            <th>Available</th>
                            <th>Issued</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $index => $book): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['category'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['publisher'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['published_year'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['isbn'] ?? 'N/A') ?></td>
                            <td><?= $book['quantity'] ?></td>
                            <td>
                                <span class="badge bg-<?= ($book['quantity'] - $book['issued_count']) > 0 ? 'success' : 'secondary' ?>">
                                    <?= max(0, $book['quantity'] - $book['issued_count']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="bg-<?= $book['issued_count'] > 0 ? 'info' : 'secondary' ?>">
                                    <?= $book['issued_count'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#editBookModal"
                                            data-id="<?= $book['id'] ?>"
                                            data-title="<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>"
                                            data-author="<?= htmlspecialchars($book['author'], ENT_QUOTES) ?>"
                                            data-isbn="<?= htmlspecialchars($book['isbn'] ?? '', ENT_QUOTES) ?>"
                                            data-category="<?= htmlspecialchars($book['category'] ?? '', ENT_QUOTES) ?>"
                                            data-publisher="<?= htmlspecialchars($book['publisher'] ?? '', ENT_QUOTES) ?>"
                                            data-published_year="<?= htmlspecialchars($book['published_year'] ?? '', ENT_QUOTES) ?>"
                                            data-quantity="<?= $book['quantity'] ?>">
                                        Edit
                                    </button>
                                    <form method="POST" action="<?= url('/books/delete/' . $book['id']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($pagination)): ?>
                <nav>
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($pagination['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= url('/books') . '?' . http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">&laquo;</span>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i == $pagination['page']): ?>
                                <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link" href="<?= url('/books') . '?' . http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= url('/books') . '?' . http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])) ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">&raquo;</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/books/update/0') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editBookId">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAuthor" class="form-label">Author</label>
                        <input type="text" class="form-control" id="editAuthor" name="author" required>
                    </div>
                    <div class="mb-3">
                        <label for="editIsbn" class="form-label">ISBN (Optional)</label>
                        <input type="text" class="form-control" id="editIsbn" name="isbn" placeholder="Enter ISBN">
                    </div>
                    <div class="mb-3">
                        <label for="editQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="editQuantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <input type="text" class="form-control" id="editCategory" name="category">
                    </div>
                    <div class="mb-3">
                        <label for="editPublisher" class="form-label">Publisher</label>
                        <input type="text" class="form-control" id="editPublisher" name="publisher">
                    </div>
                    <div class="mb-3">
                        <label for="editPublishedYear" class="form-label">Published Year</label>
                        <input type="number" class="form-control" id="editPublishedYear" name="published_year" min="1000" max="2099" placeholder="YYYY">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Fill edit book modal when shown
    const editBookModal = document.getElementById('editBookModal');
    editBookModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        document.getElementById('editBookId').value = button.getAttribute('data-id');
        document.getElementById('editTitle').value = button.getAttribute('data-title');
        document.getElementById('editAuthor').value = button.getAttribute('data-author');
        document.getElementById('editIsbn').value = button.getAttribute('data-isbn');
        document.getElementById('editCategory').value = button.getAttribute('data-category');
        document.getElementById('editPublisher').value = button.getAttribute('data-publisher');
        document.getElementById('editPublishedYear').value = button.getAttribute('data-published_year');
        document.getElementById('editQuantity').value = button.getAttribute('data-quantity');

        // Update form action with correct ID
        const form = editBookModal.querySelector('form');
        form.action = '<?= url('/books/update/') ?>' + button.getAttribute('data-id');
    });
</script>