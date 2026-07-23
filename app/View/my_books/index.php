<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4" id="myBooksTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="borrowed-tab" data-bs-toggle="tab" data-bs-target="#borrowed" type="button" role="tab">Borrowed Books</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button" role="tab">My Reservations</button>
    </li>
</ul>
<div class="tab-content" id="myBooksTabContent">
    <div class="tab-pane fade show active" id="borrowed" role="tabpanel" aria-labelledby="borrowed-tab">
        <?php if (empty($borrowed_books)): ?>
        <div class="alert alert-info">You currently have no borrowed books.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Days Left</th>
                        <th>Fine</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowed_books as $book): ?>
                    <?php
                        $due_date = new DateTime($book['due_date']);
                        $today = new DateTime();
                        $diff = $today->diff($due_date);
                        $days_left = (int)$diff->format('%r%a');
                        $is_overdue = $days_left < 0;
                        $fine_amount = $book['fine'] ?? 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['issue_date']) ?></td>
                        <td><?= htmlspecialchars($book['due_date']) ?></td>
                        <td>
                            <span class="badge bg-<?= $is_overdue ? 'danger' : ($days_left <= 3 ? 'warning' : 'success') ?>">
                                <?= $is_overdue ? 'OVERDUE' : $days_left . ' days' ?>
                            </span>
                        </td>
                        <td>$<?= number_format($fine_amount, 2) ?></td>
                        <td>
                            <form method="POST" action="<?= url('/my-books/return') ?>" class="d-inline">
                                    <input type="hidden" name="transaction_id" value="<?= $book['transaction_id'] ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Return Book</button>
                                </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="tab-pane fade" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
        <?php if (empty($reservations)): ?>
        <div class="alert alert-info">You have no active reservations.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Reservation Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= htmlspecialchars($reservation['book_title']) ?></td>
                        <td><?= htmlspecialchars($reservation['reservation_date']) ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?= ucfirst($reservation['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($reservation['status'] === 'pending'): ?>
                            <a href="<?= url('/user/reservation/cancel/' . $reservation['id']) ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>