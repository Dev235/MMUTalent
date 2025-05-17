<?php
$host = 'localhost';
$username = 'root';
$password = '';
$databaseName = 'db_mmu_talent';

$conn = mysqli_connect($host, $username, $password, $databaseName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Connected successfully";
}
