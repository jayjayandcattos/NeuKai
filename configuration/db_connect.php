<?php
$host = "localhost";  
$user = "root";       
$pass = "";           
$dbname = "neukai"; 

$conn = new mysqli($host, $user, $pass, $dbname, 3306);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->set_charset("utf8");

?>
