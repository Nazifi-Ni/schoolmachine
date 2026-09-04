<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fas fa-book text-primary me-2"></i> Manage Subjects</h4>
        <a href="/my-class" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Class</a>
    </div>
</div>

<div class="row">
    <!-- Add Subject Form -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">Add Subject</h5>
            </div>
            <div class="card-body">
                <form action="/results/subjects/store" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Mathematics">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Subject</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Subjects List -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">Class Curriculum</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($subjects)): ?>
                    <div class="text-center p-4 text-muted">
                        <i class="fas fa-folder-open mb-3" style="font-size: 2rem;"></i>
                        <p>No subjects added yet. Add a subject to start building the curriculum.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($subjects as $subject): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <i class="fas fa-bookmark text-muted me-2"></i>
                                    <span class="fw-medium text-dark"><?= htmlspecialchars($subject->name) ?></span>
                                </div>
                                <form action="/results/subjects/delete/<?= $subject->id ?>" method="post" class="d-inline" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this subject? Note: this may affect existing scores.');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
