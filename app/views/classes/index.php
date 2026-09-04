<div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="/classes/create" class="btn btn-primary"><i class="fas fa-plus"></i> Add Class</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Classes</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Assigned Teacher</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr><td colspan="5" class="text-center">No classes found.</td></tr>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class->id) ?></td>
                            <td><?= htmlspecialchars($class->name) ?></td>
                            <td><?= htmlspecialchars($class->level) ?></td>
                            <td>
                                <?php if ($class->first_name): ?>
                                    <?= htmlspecialchars($class->first_name . ' ' . $class->last_name) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/classes/edit/<?= $class->id ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i> Edit</a>

                                <form action="/classes/delete/<?= $class->id ?>" method="POST" style="display:inline-block;" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this class? All students and results in this class might be affected.');">
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
