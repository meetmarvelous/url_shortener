<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = mysqli_real_escape_string($conn, $_POST['url']);
    $short_code = generateShortCode();

    // Check if the URL already exists
    $query = "SELECT * FROM urls WHERE original_url = '$original_url'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $url = mysqli_fetch_assoc($result);
        echo getShortUrl($url['short_code']);
    } else {
        // Insert the new URL
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $guest = $user_id ? 0 : 1; // Mark as guest if not logged in

        $query = "INSERT INTO urls (user_id, original_url, short_code, guest) VALUES ('$user_id', '$original_url', '$short_code', '$guest')";
        if (mysqli_query($conn, $query)) {
            echo getShortUrl($short_code);
        } else {
            echo 'Error shortening URL.';
        }
    }
}
?>