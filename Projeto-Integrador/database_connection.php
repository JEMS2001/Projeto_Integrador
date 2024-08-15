<?php
$hostname = "localhost";
$username = "root";
$password = "senha_da_nasa";
$database = "projeto";

$con = mysqli_connect($hostname, $username, $password, $database);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>