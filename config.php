<?php
$host = 'localhost';
$user='root';
$pass= '';
$db='shop_db';
$conn = mysqli_connect($host, $user, $pass, $db);
if($conn->connect_error){
    die("connection failed". $conn->connect_error);

}

?>