<?php
require_once __DIR__ . '/../includes/auth.php'; // Include auth.php for session handling
require_once __DIR__ . '/../config.php';       // Include config.php for BASE_URL

session_start(); // Start the session
session_destroy(); // Destroy the session
header('Location: ' . BASE_URL . 'pages/login.php'); // Redirect to login page
exit();
?>