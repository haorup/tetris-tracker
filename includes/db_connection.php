<?php
// database connection configuration
$servername = "localhost";
$username = "root"; // mysql username
$password = "root"; // mysql password
$dbname = "tetris_tracking";

// create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// check connection
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>