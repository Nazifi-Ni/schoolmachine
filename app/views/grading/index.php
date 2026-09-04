<div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="/grading/create" class="btn btn-primary"><i class="fas fa-plus"></i> Add Grade Level</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Grading System Configuration</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
            <thead>
                <tr>
                    <th>Score Range</th>
                    <th>Grade</th>
                    <th>Remark</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($grades)): ?>
                    <tr><td colspan="4" class="text-center">No grading system configured.</td></tr>
                <?php else: ?>
                    <?php foreach ($grades as $g): ?>
                        <tr>
                            <td><?= htmlspecialchars($g->min_score) ?> - <?= htmlspecialchars($g->max_score) ?></td>
                            <td><span class="badge bg-secondary fs-6"><?= htmlspecialchars($g->grade) ?></span></td>
                            <td><?= htmlspecialchars($g->remark) ?></td>
                            <td>
                                <a href="/grading/edit/<?= $g->id ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i> Edit</a>
                                <form action="/grading/delete/<?= $g->id ?>" method="POST" style="display:inline-block;" onsubmit="confirmDelete(event, this, 'Delete this grade level?');">
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
