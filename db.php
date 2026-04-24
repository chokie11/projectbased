<?php
$host="10.10.0.252";
$user="root";
$pass="foobar123";
$db="TestDB";

$conn = new mysqli($host,$user,$pass,$db);

if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}
?>

