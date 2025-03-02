<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = mysqli_real_escape_string($conn, $_POST['url']);
    $short_code = generateShortCode();

    $user_id = $_SESSION['user_id'];
    $query = "INSERT INTO urls (user_id, original_url, short_code) VALUES ('$user_id', '$original_url', '$short_code')";

    if (mysqli_query($conn, $query)) {
        header('Location: ' . BASE_URL . 'pages/dashboard.php');
        exit();
    } else {
        die("Error: " . mysqli_error($conn));
    }
}
?>