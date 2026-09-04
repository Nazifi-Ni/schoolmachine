
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-info text-white border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 position-relative d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start gap-3">
                <i class="fas fa-users position-absolute" style="font-size: 8rem; right: -20px; bottom: -20px; opacity: 0.2;"></i>
                <div>
                    <h2 class="mb-1">My Class: <?= htmlspecialchars($class->name) ?></h2>
                    <p class="mb-0 text-white-50">Level: <?= htmlspecialchars($class->level) ?></p>
                </div>
                <div style="z-index: 1;" class="d-flex flex-column flex-sm-row gap-2">
                    <a href="/results/subjects" class="btn btn-light text-info fw-bold rounded-pill px-4 shadow-sm w-100">
                        <i class="fas fa-book me-2"></i> Manage Class Subjects
                    </a>
                    <a href="#" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm w-100" onclick="printWithCustomTotal(event, '/results/print-all')">
                        <i class="fas fa-download me-2"></i> Download All Results
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2 text-info"></i> Students List</h5>
        <div class="d-flex gap-2">
            <a href="/my-class" class="btn btn-sm <?= !($filter_pending ?? false) ? 'btn-primary' : 'btn-outline-primary' ?>">All Students</a>
            <a href="/my-class?filter=pending" class="btn btn-sm <?= ($filter_pending ?? false) ? 'btn-warning' : 'btn-outline-warning' ?>">
                Pending Results <span class="badge bg-danger ms-1"><?= $pending_count ?? 0 ?></span>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($students)): ?>
            <div class="text-center p-5 text-muted">
                <i class="fas fa-user-slash mb-3" style="font-size: 3rem;"></i>
                <p>No active students found in this class.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Reg Number</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($students as $student): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $i++ ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($student->registration_number) ?></td>
                                <td>
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($student->surname . ' ' . $student->first_name . ' ' . $student->middle_name) ?></div>
                                </td>
                                <td><?= htmlspecialchars($student->gender) ?></td>
                                <td>
                                    <?php if (isset($student->is_pending) && $student->is_pending): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        <a href="/results/student/<?= $student->id ?>" class="btn btn-sm btn-info text-white" title="Enter Results">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-secondary" onclick="printWithCustomTotal(event, '/results/print/<?= $student->id ?>')" title="Print Result">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <form action="/results/delete/<?= $student->id ?>" method="POST" class="d-inline-block" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this student\'s result? All entered scores will be permanently deleted.');">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Delete Result">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// No layout inclusion needed, handled by Controller::view()
?>
<script>
function printWithCustomTotal(event, url) {
    event.preventDefault();
    Swal.fire({
        title: 'Total Students',
        text: 'Enter Total Students for result printing (leave blank for actual count):',
        input: 'number',
        inputAttributes: {
            min: 1,
            step: 1
        },
        showCancelButton: true,
        confirmButtonText: 'Print',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            let finalUrl = url;
            if (result.value) {
                finalUrl += '?ts=' + encodeURIComponent(result.value);
            }
            window.open(finalUrl, '_blank');
        }
    });
}
</script>
