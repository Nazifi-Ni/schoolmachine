<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Edit Teacher: <?= htmlspecialchars($teacher->first_name . ' ' . $teacher->last_name) ?></h3>
    </div>
    <form action="/teachers/update/<?= $teacher->id ?>" method="POST">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($teacher->first_name) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($teacher->last_name) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($teacher->email) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($teacher->phone) ?>">
                </div>
            </div>

            <hr>
            <h5 class="mb-3">Login Credentials & Status</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($teacher->username) ?>" readonly disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">New Password <small class="text-muted">(Leave blank to keep current)</small></label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Account Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= $teacher->user_status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $teacher->user_status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">Update Teacher</button>
            <a href="/teachers" class="btn btn-default float-end">Cancel</a>
        </div>
    </form>
</div>
