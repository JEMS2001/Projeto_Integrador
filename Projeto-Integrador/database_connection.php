<?php
$hostname = "mysql";
$username = "jfhk";
$password = "jfhk123";
$database = "projeto";

$con = mysqli_connect($hostname, $username, $password, $database);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
