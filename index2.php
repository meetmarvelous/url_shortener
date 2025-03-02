<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Handle short URL redirection
if (isset($_GET['q'])) {
    $short_code = $_GET['q'];
    $query = "SELECT * FROM urls WHERE short_code = '$short_code'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $url = mysqli_fetch_assoc($result);

        // Increment hits
        $hits = $url['hits'] + 1;
        $update_query = "UPDATE urls SET hits = $hits WHERE id = {$url['id']}";
        mysqli_query($conn, $update_query);

        // Redirect to the original URL
        header("Location: {$url['original_url']}");
        exit();
    } else {
        header('HTTP/1.0 404 Not Found');
        echo 'Short URL not found.';
        exit();
    }
}

// Handle URL shortening form submission
$error = '';
$short_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $original_url = mysqli_real_escape_string($conn, $_POST['url']);
    $short_code = generateShortCode();

    // Check if the URL already exists
    $query = "SELECT * FROM urls WHERE original_url = '$original_url'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $url = mysqli_fetch_assoc($result);
        $short_url = getShortUrl($url['short_code']);
    } else {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
        $guest = $user_id === 'NULL' ? 1 : 0;

        $query = "INSERT INTO urls (user_id, original_url, short_code, guest) VALUES ($user_id, '$original_url', '$short_code', '$guest')";
        if (mysqli_query($conn, $query)) {
            $short_url = getShortUrl($short_code);

            // If user is not logged in, save the link in a cookie
            if (!isLoggedIn()) {
                $guest_links = isset($_COOKIE['guest_links']) ? json_decode($_COOKIE['guest_links'], true) : [];
                $guest_links[] = [
                    'original_url' => $original_url,
                    'short_code' => $short_code,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                setcookie('guest_links', json_encode($guest_links), time() + (86400 * 30), "/"); // 30 days
            }
        } else {
            $error = 'Error shortening URL.';
        }
    }

    header('Location: ' . BASE_URL . '?short_url=' . urlencode($short_url));
    exit();
}

// Fetch user's URLs if logged in
$urls = [];
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM urls WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $urls = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.css"/>
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
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="signup.php">Signup</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_login.php">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Shorten a URL</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="url" class="form-label">Enter URL to shorten:</label>
                                <input type="url" name="url" id="url" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Shorten</button>
                        </form>
                    </div>
                </div>

                <?php if (isset($_GET['short_url'])): ?>
                    <div class="card mt-4 shadow">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title mb-0">Short URL</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">Your short URL: <a href="<?php echo $_GET['short_url']; ?>" target="_blank"><?php echo $_GET['short_url']; ?></a></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isLoggedIn() && !empty($urls)): ?>
                    <div class="card mt-4 shadow">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title mb-0">Your Links</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="linksTable">
                                    <thead>
                                        <tr>
                                            <th>Original URL</th>
                                            <th>Short URL</th>
                                            <th>Hits</th>
                                            <th>Actions</th>
                                            <th>Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($urls as $url): ?>
                                            <tr>
                                                <td class="text-truncate" style="max-width: 200px;" title="<?php echo $url['original_url']; ?>"><?php echo $url['original_url']; ?></td>
                                                <td><a href="<?php echo getShortUrl($url['short_code']); ?>" target="_blank"><?php echo getShortUrl($url['short_code']); ?></a></td>
                                                <td><?php echo $url['hits']; ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $url['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary copy-btn" data-short-url="<?php echo getShortUrl($url['short_code']); ?>">
                                                        <i class="bi bi-clipboard"></i> Copy
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary share-btn" data-short-url="<?php echo getShortUrl($url['short_code']); ?>">
                                                        <i class="bi bi-share"></i> Share
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
    <!-- Clipboard.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#linksTable').DataTable({
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: [3, 4] },
                    { className: "text-nowrap", targets: [3, 4] }
                ]
            });

        });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {

           // Copy button functionality
           new ClipboardJS('.copy-btn', {
                text: function(trigger) {
                    return trigger.getAttribute('data-short-url');
                }
            });

            // Share button functionality
            $('.share-btn').on('click', function() {
                const shortUrl = $(this).data('short-url');
                if (navigator.share) {
                    navigator.share({
                        title: 'Short URL',
                        text: 'Check out this short URL:',
                        url: shortUrl
                    });
                } else {
                    alert('Sharing is not supported in your browser.');
                }
            });


    });
</script>
</body>
</html>