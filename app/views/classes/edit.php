<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Edit Class: <?= htmlspecialchars($class->name) ?></h3>
            </div>
            <form action="/classes/update/<?= $class->id ?>" method="POST">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($class->name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="Nursery" <?= $class->level == 'Nursery' ? 'selected' : '' ?>>Nursery</option>
                            <option value="Primary" <?= $class->level == 'Primary' ? 'selected' : '' ?>>Primary</option>
                            <option value="Junior Secondary" <?= $class->level == 'Junior Secondary' ? 'selected' : '' ?>>Junior Secondary</option>
                            <option value="Senior Secondary" <?= $class->level == 'Senior Secondary' ? 'selected' : '' ?>>Senior Secondary</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Assign Class Teacher</label>
                        <select class="form-select" id="teacher_id" name="teacher_id">
                            <option value="">No Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher->id ?>" <?= $class->teacher_id == $teacher->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($teacher->first_name . ' ' . $teacher->last_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">Update Class</button>
                    <a href="/classes" class="btn btn-default float-end">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
