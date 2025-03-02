<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$short_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $guest = $user_id ? 0 : 1; // Mark as guest if not logged in

        $query = "INSERT INTO urls (user_id, original_url, short_code, guest) VALUES ('$user_id', '$original_url', '$short_code', '$guest')";
        if (mysqli_query($conn, $query)) {
            $short_url = getShortUrl($short_code);
        } else {
            $error = 'Error shortening URL.';
        }
    }
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
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="url">Enter URL to shorten:</label>
        <input type="url" name="url" id="url" required>
        <button type="submit">Shorten</button>
    </form>

    <?php if ($short_url): ?>
        <p>Short URL: <a href="<?php echo $short_url; ?>" target="_blank"><?php echo $short_url; ?></a></p>
    <?php endif; ?>

    <p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Manage Links</a> | <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="signup.php">Signup</a> to manage your links.
        <?php endif; ?>
    </p>
</body>
</html>