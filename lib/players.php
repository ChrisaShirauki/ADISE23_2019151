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

	$sql = 'INSERT INTO `player` (`username`, `piece_color`, `last_action`, `player_token`,`computer`) VALUES(?,?,NULL,?,FALSE);';
	$st = $mysqli->prepare($sql);
	$st->bind_param('sss',$username,$color,$token);
	$st->execute();	

	unset($availableColors[$randomKey]);
	
	$sql = "UPDATE game_status SET status='INITIALIZED';";
	$st = $mysqli->prepare($sql);
	$st->execute();
	
	$players = get_players();
	if(count($players) >= 2){
		start_game($availableColors);
	}
	#return the token to the client to be used as input in all the functions
	
	header('Content-type: application/json');
	print json_encode($token);
	header("HTTP/1.1 200 OK.");
	
}

function start_game($available){
	global $mysqli;

	global $colors;
	$start = array_rand($colors);	// Start as random player

	$sql = "UPDATE game_status SET `status`='STARTED', `player`=?;";
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$start);
	$st->execute();

	$i = 1;
	foreach($available as $color){
		$username = 'computer' . $i;
		$sql = 'INSERT INTO `player` (`username`, `piece_color`, `last_action`, `player_token`) VALUES(?,?,NULL,NULL,TRUE);';
		$st = $mysqli->prepare($sql);
		$st->bind_param('ss',$username,$color);
		$st->execute();	
		$i++;
	}

	$sql = "UPDATE `player` SET `last_action`=NOW();";
	$st = $mysqli->prepare($sql);
	$st->execute();
}

function leave_game($input){
	global $mysqli;
	$color = $input['color']; 
    $token = $input['token'];

    if (!(check_token($color, $token))){    // The player has wrong token, Unauthorised
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Wrong token']);
        http_response_code(401);
        return;
    }

	$sql = 'DELETE FROM `players` WHERE `piece_color` = ?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$c);	
	$st->execute();

	$status = get_game()['status'];
    if($status == 'STARTED'){  // Abort the game if it's started
        $sql = "UPDATE `game_status` SET `status` = 'ABORTED'";
        $st = $mysqli->prepare($sql);
        $st->execute();
    }
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

// Check if the player is AFK
function check_activity($color){
	global $mysqli;

	$sql = 'SELECT * FROM `players` WHERE `piece_color` = ? AND `last_action` < (NOW() - INTERVAL 100 MINUTE)';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$color);
	$st->execute();
	$res = $st->get_result();	
	if(($res->num_rows) == 0){ // Isn't AFK
		return false;
	}
	$r = $res->fetch_all(MYSQLI_ASSOC);
	return true;			
}

function kick_inactive(){
	global $mysqli;
	$game = get_game();
	$player = $game['player'];
	$status = $game['status'];

	if(check_activity($player) & $status=='STARTED'){	// If AFK after game starts kick
		$sql = 'DELETE FROM `players` WHERE `piece_color` = ?';
		$st = $mysqli->prepare($sql);
		$st->bind_param('s',$player);	
		$st->execute();

		return true;
	}
	return false; // No AFK players
}
function check_token($color, $token){
    global $mysqli;

    $sql = 'SELECT `player_token` FROM `players` WHERE `piece_color` = ?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s',$color);
    $st->execute();
    $res = $st->get_result();

    if ($row = $res->fetch_assoc()) {
        $player_token = $row['player_token'];
    } else {
        return false;
    }

    if($player_token == $token){
        return true;
    }
    else{
        return false;
    }
}
?>