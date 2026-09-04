<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3><?= htmlspecialchars($stats['class_name']) ?></h3>
                <p>Assigned Class</p>
            </div>
            <div class="icon">
                <i class="fas fa-chalkboard"></i>
            </div>
            <a href="/my-class" class="small-box-footer">View Class <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-4 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3><?= number_format($stats['total_students']) ?></h3>
                <p>Students in Class</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <a href="/my-class" class="small-box-footer">View Students <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-4 col-12">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3><?= number_format($stats['pending_results']) ?></h3>
                <p>Pending Results</p>
            </div>
            <div class="icon">
                <i class="fas fa-edit"></i>
            </div>
            <a href="/my-class?filter=pending" class="small-box-footer">Result Entry <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="/my-class" class="btn btn-primary"><i class="fas fa-edit"></i> Enter Results</a>
                <a href="/my-class" class="btn btn-secondary ms-2"><i class="fas fa-list"></i> View Class List</a>
            </div>
        </div>
    </div>
</div>
