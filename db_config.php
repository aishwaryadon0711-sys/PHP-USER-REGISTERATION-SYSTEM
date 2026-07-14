<?php
// ---------------------------------------------------------
// Database connection settings for XAMPP (default MySQL setup)
// ---------------------------------------------------------
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';          // default XAMPP MySQL password is empty
$db_name = 'registration_demo';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}