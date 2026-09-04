<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Edit Student: <?= htmlspecialchars($student->surname . ' ' . $student->first_name) ?></h3>
    </div>
    <form action="/students/update/<?= $student->id ?>" method="POST">
        <div class="card-body">
            <h5 class="mb-3 border-bottom pb-2">Academic Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="registration_number" class="form-label">Registration Number</label>
                    <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?= htmlspecialchars($student->registration_number ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="current_class_id" class="form-label">Class <span class="text-danger">*</span></label>
                    <select class="form-select" id="current_class_id" name="current_class_id" required>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class->id ?>" <?= $student->current_class_id == $class->id ? 'selected' : '' ?>><?= htmlspecialchars($class->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="active" <?= $student->status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="graduated" <?= $student->status == 'graduated' ? 'selected' : '' ?>>Graduated</option>
                        <option value="withdrawn" <?= $student->status == 'withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
                        <option value="suspended" <?= $student->status == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
            </div>

            <h5 class="mb-3 mt-4 border-bottom pb-2">Personal Information</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="surname" name="surname" value="<?= htmlspecialchars($student->surname) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($student->first_name) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($student->middle_name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="Male" <?= $student->gender == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $student->gender == 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="dob" name="dob" value="<?= htmlspecialchars($student->dob) ?>" required>
                </div>
            </div>

            <h5 class="mb-3 mt-4 border-bottom pb-2">Parent/Guardian Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="parent_name" class="form-label">Parent/Guardian Name</label>
                    <input type="text" class="form-control" id="parent_name" name="parent_name" value="<?= htmlspecialchars($student->parent_name) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($student->phone) ?>">
                </div>
                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label">Home Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($student->address) ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">Update Student</button>
            <a href="/students" class="btn btn-default float-end">Cancel</a>
        </div>
    </form>
</div>
