<div class="card">
    <div class="card-header">
        <h5>Edit Book</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('/books/update/' . $book['id']) ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="isbn" class="form-label">ISBN (Optional)</label>
                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>" placeholder="Enter ISBN">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="<?= $book['quantity'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($book['category'] ?? '') ?>" placeholder="e.g., Fiction, Science, History">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="publisher" class="form-label">Publisher</label>
                        <input type="text" class="form-control" id="publisher" name="publisher" value="<?= htmlspecialchars($book['publisher'] ?? '') ?>" placeholder="e.g., Penguin Random House">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="published_year" class="form-label">Published Year</label>
                        <input type="number" class="form-control" id="published_year" name="published_year" min="1000" max="2099" value="<?= htmlspecialchars($book['published_year'] ?? '') ?>" placeholder="YYYY">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <small class="text-muted">Enter the year the book was published (e.g., 2023)</small>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary me-2">Update Book</button>
                <a href="<?= url('/books') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>