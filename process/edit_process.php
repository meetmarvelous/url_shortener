<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url_id = mysqli_real_escape_string($conn, $_POST['url_id']);
    $original_url = mysqli_real_escape_string($conn, $_POST['url']);

    $query = "UPDATE urls SET original_url = '$original_url' WHERE id = '$url_id' AND user_id = '{$_SESSION['user_id']}'";
    if (mysqli_query($conn, $query)) {
        header('Location: index.php');
        exit();
    } else {
        die("Error: " . mysqli_error($conn));
    }
}
?>