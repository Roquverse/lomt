<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Get registrations from database
function getRegistrations() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->query("SELECT * FROM lomt5_registrations ORDER BY registration_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching registrations: " . $e->getMessage());
        return [];
    }
}

$registrations = getRegistrations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOMT Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand img {
            height: 40px;
        }
        .dashboard-container {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 2px solid #f8f9fa;
            padding: 20px;
        }
        .table-responsive {
            margin: 0;
            padding: 0;
        }
        .dataTables_wrapper {
            padding: 20px;
        }
        .btn-download {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-download:hover {
            background-color: #0b5ed7;
            color: white;
        }
        .btn-logout {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn-logout:hover {
            background-color: #bb2d3b;
            color: white;
        }
        .welcome-text {
            color: #6c757d;
            margin-bottom: 0;
        }
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 10px;
            }
            .card {
                margin-bottom: 15px;
            }
            .dataTables_wrapper {
                padding: 10px;
            }
            .table th, .table td {
                white-space: nowrap;
            }
            .btn-download, .btn-logout {
                width: 100%;
                margin-bottom: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../assets/images/logos/logo.png" alt="LOMT Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <p class="welcome-text mb-0 me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                    </li>
                    <li class="nav-item">
                        <a href="download.php" class="btn-download">Download CSV</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn-logout">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">LOMT5 Registrations</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="registrationsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Business</th>
                                    <th>Stage</th>
                                    <th>Description</th>
                                    <th>Social Media</th>
                                    <th>Website</th>
                                    <th>Challenges</th>
                                    <th>Expectations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($reg['registration_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($reg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['business_name']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($reg['business_stage'])); ?></td>
                                    <td><?php echo htmlspecialchars($reg['message']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['social_media']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['website']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['challenges']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['expectations']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registrationsTable').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    search: "Search registrations:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ registrations",
                    infoEmpty: "No registrations found",
                    infoFiltered: "(filtered from _MAX_ total registrations)"
                },
                columnDefs: [
                    { responsivePriority: 1, targets: [0, 1, 2] },
                    { responsivePriority: 2, targets: [3, 4, 5] },
                    { responsivePriority: 3, targets: [6, 7, 8, 9, 10] }
                ]
            });
        });
    </script>
</body>
</html> 