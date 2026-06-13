<?php

//host locally
$host = "localhost";
$user = "root";
$password = "";
$database = "users_db";

//host online
// $host = "sql107.infinityfree.com";
// $user = "if0_42137932";
// $password = "Tharina1221";
// $database = "if0_42137932_users_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error)
{
    die("Connect failed: ". $conn->connect_error);
}

?>
