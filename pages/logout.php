<?php
session_start();
session_destroy();
header('Location: ' . BASE_URL . 'pages/login.php');
exit();
?>