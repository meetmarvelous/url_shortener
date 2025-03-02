<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$url_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM urls WHERE id = '$url_id' AND user_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);
$url = mysqli_fetch_assoc($result);

if (!$url) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../process/edit_process.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit URL</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body>
    <h1>Edit URL</h1>
    <form method="POST" action="">
        <input type="hidden" name="url_id" value="<?php echo $url['id']; ?>">
        <label for="url">Original URL:</label>
        <input type="text" name="url" id="url" value="<?php echo $url['original_url']; ?>" required>
        <br>
        <button type="submit">Save Changes</button>
    </form>
    <a href="<?php echo BASE_URL; ?>pages/dashboard.php">Back to Dashboard</a>
</body>
</html>