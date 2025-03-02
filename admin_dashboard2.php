<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireAdmin(); // Ensure only admins can access this page

// Fetch stats
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalLinks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM urls"))['count'];
$recentUsers = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"), MYSQLI_ASSOC);
$recentLinks = mysqli_fetch_all(mysqli_query($conn, "
    SELECT urls.*, users.username 
    FROM urls 
    LEFT JOIN users ON urls.user_id = users.id 
    ORDER BY urls.created_at DESC 
    LIMIT 5
"), MYSQLI_ASSOC);

// Handle admin username update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    $newUsername = mysqli_real_escape_string($conn, $_POST['new_username']);
    $adminId = $_SESSION['user_id'];

    // Check if the new username already exists
    $checkQuery = "SELECT * FROM users WHERE username = '$newUsername' AND id != $adminId";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $usernameError = "Username already exists.";
    } else {
        $updateQuery = "UPDATE users SET username = '$newUsername' WHERE id = $adminId";
        if (mysqli_query($conn, $updateQuery)) {
            $_SESSION['username'] = $newUsername;
            $usernameSuccess = "Username updated successfully.";
        } else {
            $usernameError = "Error updating username.";
        }
    }
}

// Handle admin password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $newPassword = $_POST['new_password'];
    $verifyPassword = $_POST['verify_password'];
    $adminId = $_SESSION['user_id'];

    // Check if the new password and verify password match
    if ($newPassword !== $verifyPassword) {
        $passwordError = "New password and verify password do not match.";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the password in the database
        $updateQuery = "UPDATE users SET password = '$hashedPassword' WHERE id = $adminId";
        if (mysqli_query($conn, $updateQuery)) {
            $passwordSuccess = "Password updated successfully.";
        } else {
            $passwordError = "Error updating password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">URL Shortener</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">User Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="display-4 mb-0"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Links</h5>
                        <p class="display-4 mb-0"><?php echo $totalLinks; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Admin Username Form -->
        <div class="card shadow mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Update Admin Username</h5>
            </div>
            <div class="card-body">
                <?php if (isset($usernameError)): ?>
                    <div class="alert alert-danger"><?php echo $usernameError; ?></div>
                <?php elseif (isset($usernameSuccess)): ?>
                    <div class="alert alert-success"><?php echo $usernameSuccess; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="new_username" class="form-label">New Username:</label>
                        <input type="text" name="new_username" id="new_username" class="form-control" required>
                    </div>
                    <button type="submit" name="update_username" class="btn btn-warning">Update Username</button>
                </form>
            </div>
        </div>

        <!-- Update Admin Password Form -->
        <div class="card shadow mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Update Admin Password</h5>
            </div>
            <div class="card-body">
                <?php if (isset($passwordError)): ?>
                    <div class="alert alert-danger"><?php echo $passwordError; ?></div>
                <?php elseif (isset($passwordSuccess)): ?>
                    <div class="alert alert-success"><?php echo $passwordSuccess; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password:</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="verify_password" class="form-label">Verify New Password:</label>
                        <input type="password" name="verify_password" id="verify_password" class="form-control" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-danger">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="card shadow mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Recent Users</h5>
            </div>
            <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td><?php echo $user['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Recent Links Table -->
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Recent Links</h5>
            </div>
            <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="linksTable">
                    <thead>
                        <tr>
                            <th>Original URL</th>
                            <th>Short Code</th>
                            <th>Created By</th>
                            <th>Hits</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLinks as $link): ?>
                            <tr>
                                <td class="text-truncate" style="max-width: 200px;" title="<?php echo $link['original_url']; ?>"><?php echo $link['original_url']; ?></td>
                                <td><?php echo $link['short_code']; ?></td>
                                <td><?php echo $link['username'] ?? 'Guest'; ?></td>
                                <td><?php echo $link['hits']; ?></td>
                                <td><?php echo $link['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-4 bg-dark text-white">
        <div class="container text-center">
            <div class="mb-3">
                <span class="fs-5">
                    This link shortener is made by
                    <a href="https://marvelbyte.vercel.app/" target="_blank" class="text-decoration-none text-warning hover-glow">
                        Marvelous
                    </a>
                </span>
            </div>
            <div>
                <a href="https://github.com/meetmarvelous" target="_blank" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-github"></i> Visit My GitHub
                </a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                responsive: true,
                order: [
                    [3, 'desc']
                ]
            });

            $('#linksTable').DataTable({
                responsive: true,
                order: [
                    [4, 'desc']
                ]
            });
        });
    </script>
</body>

</html>