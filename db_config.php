<?php
// db_config.php
// Default XAMPP MySQL credentials: user 'root', empty password.
// Update these if your XAMPP setup differs.

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'registration_demo';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}