<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$short_url = '';

// Handle URL shortening
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
        // Insert the new URL
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL'; // Use NULL for guest users
        $guest = $user_id === 'NULL' ? 1 : 0; // Mark as guest if user_id is NULL

        $query = "INSERT INTO urls (user_id, original_url, short_code, guest) VALUES ($user_id, '$original_url', '$short_code', '$guest')";
        if (mysqli_query($conn, $query)) {
            $short_url = getShortUrl($short_code);
        } else {
            $error = 'Error shortening URL.';
        }
    }
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body>
    <h1>URL Shortener</h1>

    <!-- URL Shortening Form -->
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="url">Enter URL to shorten:</label>
        <input type="url" name="url" id="url" required>
        <button type="submit">Shorten</button>
    </form>

    <!-- Display Short URL -->
    <?php if ($short_url): ?>
        <p>Short URL: <a href="<?php echo $short_url; ?>" target="_blank"><?php echo $short_url; ?></a></p>
    <?php endif; ?>

    <!-- Login/Signup or Logout Links -->
    <p>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="signup.php">Signup</a> to manage your links.
        <?php endif; ?>
    </p>

    <!-- Dashboard Table (Visible Only When Logged In) -->
    <?php if (isLoggedIn() && !empty($urls)): ?>
        <h2>Your Links</h2>
        <table>
            <thead>
                <tr>
                    <th>Original URL</th>
                    <th>Short URL</th>
                    <th>Hits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urls as $url): ?>
                <tr>
                    <td><?php echo $url['original_url']; ?></td>
                    <td><a href="<?php echo getShortUrl($url['short_code']); ?>" target="_blank"><?php echo getShortUrl($url['short_code']); ?></a></td>
                    <td><?php echo $url['hits']; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $url['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>