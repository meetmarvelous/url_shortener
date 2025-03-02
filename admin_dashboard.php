<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Fetch all URLs and users
$urls = mysqli_query($conn, "SELECT * FROM urls");
$users = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <a href="logout.php">Logout</a>

    <h2>All Links</h2>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Original URL</th>
                <th>Short URL</th>
                <th>Hits</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($url = mysqli_fetch_assoc($urls)): ?>
            <tr>
                <td><?php echo $url['user_id']; ?></td>
                <td><?php echo $url['original_url']; ?></td>
                <td><a href="<?php echo getShortUrl($url['short_code']); ?>" target="_blank"><?php echo getShortUrl($url['short_code']); ?></a></td>
                <td><?php echo $url['hits']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>All Users</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['role']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>