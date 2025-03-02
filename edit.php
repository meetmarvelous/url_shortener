<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$url_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM urls WHERE id = '$url_id' AND user_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);
$url = mysqli_fetch_assoc($result);

if (!$url) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = mysqli_real_escape_string($conn, $_POST['url']);
    $short_code = mysqli_real_escape_string($conn, $_POST['short_code']);

    $check_query = "SELECT * FROM urls WHERE short_code = '$short_code' AND id != '$url_id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = 'The short code already exists. Please choose a different one.';
    } else {
        $update_query = "UPDATE urls SET original_url = '$original_url', short_code = '$short_code' WHERE id = '$url_id' AND user_id = '{$_SESSION['user_id']}'";
        if (mysqli_query($conn, $update_query)) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Error updating URL.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit URL</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">URL Shortener</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Edit URL</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label class="form-label">Current Short URL:</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo BASE_URL . $url['short_code']; ?></span>
                            </div>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="original_url" class="form-label">Original URL</label>
                                <input type="url" name="url" id="original_url" 
                                       class="form-control" value="<?php echo $url['original_url']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="short_code" class="form-label">New Short Code</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo BASE_URL; ?></span>
                                    <input type="text" name="short_code" id="short_code" 
                                           class="form-control" value="<?php echo $url['short_code']; ?>" required>
                                </div>
                                <small class="form-text text-muted">Only letters, numbers, and hyphens allowed</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
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
</body>
</html>