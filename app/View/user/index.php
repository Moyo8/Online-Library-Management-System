<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>User Management</h5>
        <div>
            <a href="<?= url('/users/create') ?>" class="btn btn-sm btn-outline-primary me-2">Add New User</a>
            <form method="GET" action="<?= url('/users') ?>" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                <select name="role" class="form-select form-select-sm me-2">
                    <option value="">All Roles</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="librarian" <?= $role === 'librarian' ? 'selected' : '' ?>>Librarian</option>
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                <?php if (!empty($search) || !empty($role)): ?>
                    <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-danger ms-1">Clear</a>
                <?php endif; ?>
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

        <?php if (!empty($stats)): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Total Users</h6>
                            <p class="card-text display-6"><?= $stats['total_users'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Admins</h6>
                            <p class="card-text display-6"><?=
                                isset($stats['users_by_role']) ?
                                array_reduce($stats['users_by_role'], function($carry, $item) {
                                    return $carry + (($item['role'] ?? '') === 'admin' ? $item['count'] : 0);
                                }, 0) : 0
                            ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Librarians</h6>
                            <p class="card-text display-6"><?=
                                isset($stats['users_by_role']) ?
                                array_reduce($stats['users_by_role'], function($carry, $item) {
                                    return $carry + (($item['role'] ?? '') === 'librarian' ? $item['count'] : 0);
                                }, 0) : 0
                            ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Regular Users</h6>
                            <p class="card-text display-6"><?=
                                isset($stats['users_by_role']) ?
                                array_reduce($stats['users_by_role'], function($carry, $item) {
                                    return $carry + (($item['role'] ?? '') === 'user' ? $item['count'] : 0);
                                }, 0) : 0
                            ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <div class="text-center py-4">
                <p>No users found.</p>
                <?php if (!empty($search) || !empty($role)): ?>
                    <p class="text-muted">Try adjusting your search filters.</p>
                <?php endif; ?>
                <a href="<?= url('/users/create') ?>" class="btn btn-primary">Add First User</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="bg-<?=
                                    ($user['role'] === 'admin') ? 'danger' :
                                    (($user['role'] === 'librarian') ? 'info' : 'success')
                                ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="<?= $user['id'] ?>"
                                                data-name="<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>"
                                                data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>"
                                                data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES) ?>">
                                            Edit
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">(You)</span>
                                    <?php endif; ?>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="<?= url('/users/delete/' . $user['id']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if (!empty($pagination)): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Preserve current GET parameters except page
                        $queryParams = $_GET;
                        unset($queryParams['page']);
                        $baseUrl = url('/users') . '?' . http_build_query($queryParams);
                        ?>
                        <?php if ($pagination['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl . '&page=' . ($pagination['page'] - 1) ?>" aria-label="Previous">
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
                                <li class="page-item"><a class="page-link" href="<?= $baseUrl . '&page=' . $i ?>"><?= $i ?></a></li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl . '&page=' . ($pagination['page'] + 1) ?>" aria-label="Next">
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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/users/update/0') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role">
                            <option value="admin">Admin</option>
                            <option value="librarian">Librarian</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPassword" class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="editPassword" name="password" placeholder="Enter new password">
                        <div class="form-text">Minimum 6 characters if provided</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Fill edit user modal when shown
    const editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        document.getElementById('editUserId').value = button.getAttribute('data-id');
        document.getElementById('editName').value = button.getAttribute('data-name');
        document.getElementById('editEmail').value = button.getAttribute('data-email');
        document.getElementById('editRole').value = button.getAttribute('data-role');

        // Update form action with correct ID
        const form = editUserModal.querySelector('form');
        form.action = '<?= url('/users/update/') ?>' + button.getAttribute('data-id');
    });
</script>