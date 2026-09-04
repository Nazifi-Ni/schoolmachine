<div class="row">
    <div class="col-md-5">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add New Subject</h3>
            </div>
            <form action="/classes/<?= $class->id ?>/subjects/store" method="POST">
                <div class="card-body">
                    <p class="text-muted">Adding a subject to <strong><?= htmlspecialchars($class->name) ?></strong></p>
                    <div class="mb-3">
                        <label for="name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Mathematics" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                    <a href="/classes" class="btn btn-default float-end">Back to Classes</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subjects in <?= htmlspecialchars($class->name) ?></h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap align-middle">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr><td colspan="2" class="text-center text-muted">No subjects added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subject->name) ?></td>
                                    <td>
                                        <form action="/classes/<?= $class->id ?>/subjects/delete/<?= $subject->id ?>" method="POST" onsubmit="confirmDelete(event, this, 'Remove this subject?');">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
