<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

// Fetch user's URLs
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM urls WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$urls = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body>
    <h1>Dashboard</h1>
    <a href="logout.php">Logout</a>

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
</body>
</html>