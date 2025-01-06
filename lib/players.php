<?php
require_once "./lib/game.php";

function find_game($input) {
	if(!isset($input['username']) || $input['username']==''){
		header('HTTP/1.1 400 Bad Request');
		print json_encode(['errormesg'=>'No Username given.']);
		exit;
	}
	
	$username=$input['username'];
	global $mysqli;
	
	$status=get_game()['status'];	

    if($status== 'STARTED'){ // There is already a ongoing game in the room
        header('HTTP/1.1 503 Unavailable');
		print json_encode(['errormesg'=>'Game room busy, please try again later.']);
		exit;
    }
	if($status=='ENDED' || $status=='ABORTED'){	// The game is over
		$sql = 'CALL new_game()';
		$st = $mysqli->prepare($sql);
		$st->execute();
	}

	// Pick a color randomly

	$sql = "SELECT piece_color FROM player";
	$st = $mysqli->prepare($sql);
	$st->execute();
	$result = $st->get_result();

	$usedColors = [];
	while ($row = $result->fetch_assoc()) {
		$usedColors[] = $row['piece_color'];
	}
	
	global $colors;

	$availableColors = array_diff($colors, $usedColors);

	if (count($availableColors) === 0) {
		header('HTTP/1.1 503 Unavailable');
		echo json_encode(['errormesg' => 'No available colors.']);
		exit;
	}

	$availableColors = array_values($availableColors);

	// Randomly select a color
	$randomKey = random_int(0, count($availableColors) - 1); // Generate a random index
	$color = $availableColors[$randomKey];

	$currentTime = microtime(true);
	$token = md5(string: ($currentTime . $username));

	$sql = 'INSERT INTO `player` (`username`, `piece_color`, `last_action`, `player_token`) VALUES(?,?,NULL,?);';
	$st = $mysqli->prepare($sql);
	$st->bind_param('sss',$username,$color,$token);
	$st->execute();	
	
	$sql = "UPDATE game_status SET status='INITIALIZED';";
	$st = $mysqli->prepare($sql);
	$st->execute();
	
	$players = get_players();
	if(count($players) >= 2){
		$sql = "UPDATE game_status SET status='STARTED';";
		$st = $mysqli->prepare($sql);
		$st->execute();
	}
	#return the token to the client to be used as input in all the functions
	
	header('Content-type: application/json');
	print json_encode($token);
	header("HTTP/1.1 200 OK.");
	
}

// Return the players as an associative array
function get_players(){
	global $mysqli;
	
	$sql = 'SELECT * FROM player';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_all(MYSQLI_ASSOC);
	
	return $r;
}

function check_activity(){
	global $mysqli;
	
	$sql = 'SELECT * FROM `players` WHERE `last_action` < (NOW() - INTERVAL 100 MINUTE) ORDER BY `last_action`';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();	
	if(($res->num_rows) == 0){
		return null;
	}
	$r = $res->fetch_all(MYSQLI_ASSOC);
	return $r[0]['piece_color'];			
}

function kick_inactive(){
	global $mysqli;
	$c=check_activity();
	if(!is_null($c)){
		$sql = 'DELETE FROM `players` WHERE `piece_color` = ?';
		$st = $mysqli->prepare($sql);
		$st->bind_param('s',$c);	
		$st->execute();

		return true;
	}
	return false; // No AFK players
}
?>