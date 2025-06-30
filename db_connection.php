<?php
$host = "localhost";
$user = "root";
$password = "";
$dbName = "courses";
$conn = new mysqli($host, $user, $password, $dbName);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>