<?php 

include_once('em_functions.php');

$host =  $_SERVER['HTTP_HOST'];

if($host == "localhost") {
	$servername = "localhost";
	$username = "root"; 
	$password =  "";
	$dbname = "amour";

}	else if($host == "3.12.170.234") {

	$servername = "localhost";
	$username = "root"; 
	$password =  "Sm@rther2@2@5646";
	$dbname = "amour";

}	else {
	$servername = "localhost";
	$username = "aaroumxm_aaroumxmfix"; 
	$password =  "aarofix@321";
	$dbname = "aaroumxm_aarofix";

}


//date_default_timezone_set('Asia/Kolkata');
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname) or die("Connection failed: " . mysqli_connect_error());
mysqli_set_charset($conn,"utf8mb4");

?>