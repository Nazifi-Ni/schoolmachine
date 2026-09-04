<div class="row mb-3">
    <div class="col-md-6">
        <form action="/students" method="GET" class="d-flex align-items-center">
            <select name="class_id" class="form-select me-2" style="max-width: 250px;" onchange="this.form.submit()">
                <option value="">All Classes</option>
                <?php foreach($classes as $c): ?>
                    <option value="<?= $c->id ?>" <?= ((isset($selected_class) ? $selected_class : '') == $c->id) ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="col-md-6 d-flex justify-content-end">
        <a href="/students/create" class="btn btn-primary"><i class="fas fa-plus"></i> Register Student</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Students</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
            <thead class="table-light">
                <tr>
                    <th>Reg Number</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="6" class="text-center">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student->registration_number) ?></td>
                            <td>
                                <?= htmlspecialchars($student->surname . ' ' . $student->first_name . ' ' . $student->middle_name) ?>
                            </td>
                            <td><?= htmlspecialchars($student->class_name) ?></td>
                            <td><?= htmlspecialchars($student->gender) ?></td>
                            <td>
                                <?php if ($student->status === 'active'): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php elseif ($student->status === 'graduated'): ?>
                                    <span class="badge text-bg-info">Graduated</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary"><?= ucfirst($student->status) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/students/edit/<?= $student->id ?>" class="btn btn-sm btn-info text-white me-1"><i class="fas fa-edit"></i> Edit</a>
                                <form action="/students/delete/<?= $student->id ?>" method="POST" style="display:inline-block;" onsubmit="confirmDelete(event, this, 'Are you sure you want to permanently delete this student? All their results and records will be lost.');">
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
