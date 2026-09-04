<div class="row">
    <!-- Sessions -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Academic Sessions</h3>
            </div>
            <div class="card-body">
                <form action="/sessions/store" method="POST" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" placeholder="e.g. 2025/2026" required>
                        <button type="submit" class="btn btn-primary">Add Session</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Session Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sessions as $session): ?>
                            <tr>
                                <td><?= htmlspecialchars($session->name) ?></td>
                                <td>
                                    <?php if ($session->is_current): ?>
                                        <span class="badge text-bg-success">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$session->is_current): ?>
                                        <form action="/sessions/set-current/<?= $session->id ?>" method="POST" style="display:inline-block;">
                                            <button type="submit" class="btn btn-sm btn-info">Set Current</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="/sessions/delete/<?= $session->id ?>" method="POST" style="display:inline-block;" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this session? All related terms will be deleted.');">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms -->
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Terms</h3>
            </div>
            <div class="card-body">
                <form action="/terms/store" method="POST" class="mb-4">
                    <div class="row">
                        <div class="col-5">
                            <input type="text" name="name" class="form-control" placeholder="e.g. 1st Term" required>
                        </div>
                        <div class="col-4">
                            <select name="session_id" class="form-select" required>
                                <option value="">Select Session</option>
                                <?php foreach($sessions as $session): ?>
                                    <option value="<?= $session->id ?>"><?= htmlspecialchars($session->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-info w-100">Add Term</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Term Name</th>
                            <th>Session</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($terms as $term): ?>
                            <tr>
                                <td><?= htmlspecialchars($term->name) ?></td>
                                <td><?= htmlspecialchars($term->session_name) ?></td>
                                <td>
                                    <?php if ($term->is_current): ?>
                                        <span class="badge text-bg-success">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$term->is_current): ?>
                                        <form action="/terms/set-current/<?= $term->id ?>" method="POST">
                                            <button type="submit" class="btn btn-sm btn-info">Set Current</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
