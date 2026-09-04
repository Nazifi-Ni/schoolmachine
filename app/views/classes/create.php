<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Create New Class</h3>
    </div>
    <form action="/classes/store" method="POST">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Primary 1" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                    <select class="form-select" id="level" name="level" required>
                        <option value="">Select Level</option>
                        <option value="Nursery">Nursery</option>
                        <option value="Primary">Primary</option>
                        <option value="Junior Secondary">Junior Secondary</option>
                        <option value="Senior Secondary">Senior Secondary</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="teacher_id" class="form-label">Assign Class Teacher</label>
                    <select class="form-select" id="teacher_id" name="teacher_id">
                        <option value="">No Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher->id ?>"><?= htmlspecialchars($teacher->first_name . ' ' . $teacher->last_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> After creating the class, you can edit it to add subjects.
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Class</button>
            <a href="/classes" class="btn btn-default float-end">Cancel</a>
        </div>
    </form>
</div>
