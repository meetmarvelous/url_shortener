<?php
// Generate a short code
function generateShortCode($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

// Get full short URL
function getShortUrl($short_code) {
    return BASE_URL . $short_code;
}
?>