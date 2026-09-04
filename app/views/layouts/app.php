<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IAMS-ARMS | <?= htmlspecialchars($title ?? 'Dashboard') ?></title>

    <!-- Google Font: Outfit for a modern SaaS look -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5.3 / AdminLTE 4 Beta -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- Modern Header -->
        <nav class="app-header navbar navbar-expand bg-white border-bottom-0 shadow-sm" style="padding: 0.75rem 1rem;">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-secondary" data-lte-toggle="sidebar" href="#" role="button"><i class="fas fa-bars fa-lg"></i></a>
                    </li>
                    <li class="nav-item d-none d-md-block ms-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control bg-light border-0" placeholder="Search records...">
                        </div>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Notifications Dropdown Menu -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link text-secondary position-relative" data-bs-toggle="dropdown" href="#">
                            <i class="far fa-bell fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.6rem;">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0 mt-3 rounded-3">
                            <span class="dropdown-item dropdown-header fw-bold text-center border-bottom pb-2">0 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item text-center text-muted py-3">
                                No new notifications
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer text-center text-primary pt-2 pb-1">See All Notifications</a>
                        </div>
                    </li>
                    <!-- User Menu -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" style="padding: 0;">
                            <i class="fas fa-user-circle fa-2x me-2 text-secondary" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;"></i>
                            <span class="d-none d-md-inline fw-medium text-dark"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                            <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-3" style="min-width: 200px;">
                            <li class="px-3 py-2 text-muted border-bottom">
                                <small class="fw-bold text-uppercase d-block mb-1">Role</small>
                                <span class="text-dark fw-medium"><?= htmlspecialchars($_SESSION['role'] ?? 'Role') ?></span>
                            </li>
                            <li><a href="#" class="dropdown-item py-2 mt-1"><i class="far fa-user me-2 text-primary"></i> My Profile</a></li>
                            <li><a href="#" class="dropdown-item py-2"><i class="fas fa-cog me-2 text-primary"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="/logout" class="dropdown-item py-2 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Sign out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="/dashboard" class="brand-link">
                    <span class="brand-text fw-light">IAMS-ARMS</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link <?= ($activeMenu ?? '') == 'dashboard' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Head Teacher'): ?>
                        <li class="nav-header">ADMINISTRATION</li>
                        <li class="nav-item">
                            <a href="/teachers" class="nav-link <?= ($activeMenu ?? '') == 'teachers' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Teachers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/classes" class="nav-link <?= ($activeMenu ?? '') == 'classes' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chalkboard"></i>
                                <p>Classes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/students" class="nav-link <?= ($activeMenu ?? '') == 'students' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-user-graduate"></i>
                                <p>Students</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sessions" class="nav-link <?= ($activeMenu ?? '') == 'sessions' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Sessions & Terms</p>
                            </a>
                        </li>

                        <?php endif; ?>

                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Class Teacher'): ?>
                        <li class="nav-header">CLASS MANAGEMENT</li>
                        <li class="nav-item">
                            <a href="/my-class" class="nav-link <?= ($activeMenu ?? '') == 'my-class' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>My Class</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/my-class?filter=pending" class="nav-link <?= ($activeMenu ?? '') == 'results-entry' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>Result Entry</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0"><?= htmlspecialchars($title ?? 'Dashboard') ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <?php if (isset($content)) { echo $content; } ?>
                </div>
            </div>
        </main>

        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">
                Version 1.0.0
            </div>
            <strong>Copyright &copy; <?= date('Y') ?> <a href="#">IAMS</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/custom.js"></script>
    
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        <?php if (isset($_SESSION['success_msg'])): ?>
            Toast.fire({
                icon: 'success',
                title: <?= json_encode($_SESSION['success_msg']) ?>
            });
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            Toast.fire({
                icon: 'error',
                title: <?= json_encode($_SESSION['error_msg']) ?>
            });
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        function confirmDelete(event, form, message) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

    <?= $extraJs ?? '' ?>
</body>
</html>
