<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Edit Grade Level</h3>
    </div>
    <form action="/grading/update/<?= $grade->id ?>" method="POST">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="min_score" class="form-label">Min Score <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="min_score" name="min_score" value="<?= htmlspecialchars($grade->min_score) ?>" min="0" max="100" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="max_score" class="form-label">Max Score <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="max_score" name="max_score" value="<?= htmlspecialchars($grade->max_score) ?>" min="0" max="100" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="grade" class="form-label">Grade (e.g. A1) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="grade" name="grade" value="<?= htmlspecialchars($grade->grade) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="remark" class="form-label">Remark (e.g. Excellent)</label>
                    <input type="text" class="form-control" id="remark" name="remark" value="<?= htmlspecialchars($grade->remark) ?>">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">Update Grade</button>
            <a href="/grading" class="btn btn-default float-end">Cancel</a>
        </div>
    </form>
</div>
