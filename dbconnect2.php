<?php
$host='localhost';
$db = 'blokusdb';
require_once "db_upass.php";

$user=$DB_USER;
$pass=$DB_PASS;


if(gethostname()=='users.iee.ihu.gr') {
	$mysqli = new mysqli($host, $user, $pass, $db,null,'/home/student/iee/2019/iee2019151/mysql/run/mysql.sock');
} else {
        $mysqli = new mysqli('localhost', 'root', '', 'blokus');
}

if ($mysqli->connect_errno) {
    $error = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $error]);
    http_response_code(500);
    
}?>