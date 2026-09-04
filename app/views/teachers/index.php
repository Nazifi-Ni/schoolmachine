<div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="/teachers/create" class="btn btn-primary"><i class="fas fa-plus"></i> Add Teacher</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Teachers</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="7" class="text-center">No teachers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td><?= htmlspecialchars($teacher->id) ?></td>
                            <td>
                                <?= htmlspecialchars($teacher->first_name . ' ' . $teacher->last_name) ?>
                            </td>
                            <td><?= htmlspecialchars($teacher->username) ?></td>
                            <td><?= htmlspecialchars($teacher->email) ?></td>
                            <td><?= htmlspecialchars($teacher->phone) ?></td>
                            <td>
                                <?php if ($teacher->user_status === 'active'): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/teachers/edit/<?= $teacher->id ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i> Edit</a>
                                <form action="/teachers/delete/<?= $teacher->id ?>" method="POST" style="display:inline-block;" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this teacher? This will also remove their login access.');">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
