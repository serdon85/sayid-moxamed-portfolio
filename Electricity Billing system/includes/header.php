<?php
// includes/header.php
require_once '../config/config.php';
require_once '../utils/functions.php';
checkLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ElectricPay</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --secondary-color: #64748b;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-bg: #0f172a;
            --sidebar-bg: #1e293b;
            --surface-bg: #ffffff;
            --body-bg: #f8fafc;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --card-shadow-hover: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        /* Sidebar Upgrade */
        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg);
            background-image: radial-gradient(circle at top right, rgba(99, 102, 241, 0.15), transparent),
                              linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            padding-top: 0;
            position: fixed;
            z-index: 1000;
            transition: var(--transition);
            box-shadow: 10px 0 30px rgba(0,0,0,0.05);
        }

        .sidebar-brand {
            padding: 2rem 1.5rem;
            background: rgba(255,255,255,0.02);
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-align: center;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.85rem 1.5rem;
            margin: 0.3rem 1rem;
            border-radius: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.05);
            transform: translateX(8px);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: var(--primary-gradient);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.5);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 28px;
            text-align: center;
            transition: var(--transition);
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.2) rotate(10deg);
            color: var(--primary-color);
        }

        /* Content Area */
        .main-content {
            padding: 2.5rem;
            margin-left: 16.666667%; /* 2/12 columns */
            transition: var(--transition);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 2.5rem;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        /* Card Masterglass */
        .card {
            background: var(--surface-bg);
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: var(--card-shadow);
            border-radius: 24px;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 24px;
            background: var(--primary-gradient);
            opacity: 0;
            z-index: -1;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: var(--card-shadow-hover);
            border-color: transparent;
        }

        /* Interactive Stats */
        .stat-card-v2 {
            border: none;
            overflow: hidden;
        }

        .stat-card-v2 .stat-bg-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.05;
            transform: rotate(-15deg);
            transition: var(--transition);
        }

        .stat-card-v2:hover .stat-bg-icon {
            transform: rotate(0deg) scale(1.2);
            opacity: 0.1;
        }

        .stat-icon-square {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            transition: var(--transition);
        }

        .card:hover .stat-icon-square {
            transform: rotateY(360deg);
        }

        /* Buttons & Forms */
        .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 14px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
            filter: brightness(1.1);
        }

        .form-control, .form-select {
            padding: 0.8rem 1.2rem;
            border-radius: 14px;
            border: 2px solid #f1f5f9;
            background: #f8fafc;
            transition: var(--transition);
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Soft Colors */
        .soft-indigo { background: #e0e7ff; color: #4338ca; }
        .soft-emerald { background: #d1fae5; color: #059669; }
        .soft-amber { background: #fef3c7; color: #d97706; }
        .soft-rose { background: #ffe4e6; color: #e11d48; }

        /* Table Design */
        .table thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1.25rem;
            border: none;
        }

        .table td {
            padding: 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tr { transition: var(--transition); }
        .table tr:hover { background: #fcfdfe; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky">
                <div class="sidebar-brand d-flex align-items-center">
                    <i class="fas fa-bolt text-primary fa-2x me-2"></i>
                    <h4 class="mb-0 fw-bold">ElectricPay</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                            <i class="fas fa-users"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'readings.php' ? 'active' : ''; ?>" href="readings.php">
                            <i class="fas fa-tachometer-alt"></i> Meter Readings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>" href="billing.php">
                            <i class="fas fa-file-invoice-dollar"></i> Billing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
                    </li>
                    <?php if (hasRole('Admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tariffs.php' ? 'active' : ''; ?>" href="tariffs.php">
                            <i class="fas fa-layer-group"></i> Tariffs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                            <i class="fas fa-user-shield"></i> System Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>" href="logs.php">
                            <i class="fas fa-history"></i> Audit Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
                <div class="mt-auto p-3">
                    <a href="logout.php" class="btn btn-outline-danger w-100 border-0" style="background: rgba(239, 68, 68, 0.1);">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <nav class="navbar navbar-expand-lg navbar-light px-0 py-3 mb-4">
                <div class="container-fluid">
                    <h5 class="mb-0 fw-bold"><?php echo $_SESSION['full_name']; ?> <small class="text-muted fw-normal">(<?php echo $_SESSION['role']; ?>)</small></h5>
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <a class="text-decoration-none text-dark d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=0D6EFD&color=fff" class="rounded-circle me-2" width="35">
                                <i class="fas fa-chevron-down small"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
