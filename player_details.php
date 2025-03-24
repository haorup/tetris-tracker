<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: players.php");
    exit;
}

$game_id = $_GET['id'];



if($result->num_rows == 0) {
    header("Location: players.php");
    exit;
}
