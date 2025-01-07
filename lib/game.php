<?php

// Return the board state as JSON
function show_board() {
	
    $board = get_board();
    header('Content-type: application/json');
    print json_encode($board);		
}

// Return the players' blocks as JSON
function show_blocks(){
    $blocks = get_blocks();
    header('Content-type: application/json');
    print json_encode($blocks);	
}


// Return the game status as JSON
function inspect_game(){
	global $mysqli;

    if(kick_inactive()){
        $sql = "UPDATE `game_status` SET `status` = 'ABORTED'";
        $st = $mysqli->prepare($sql);
        $st->execute();
    }
	
	$sql = 'SELECT * FROM game_status';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_all(MYSQLI_ASSOC);
	
	header('Content-type: application/json');
	print json_encode($r, JSON_PRETTY_PRINT);
}

// Return the board as a 2D array to use in other functions
function get_board(){
    global $mysqli;
	
		$sql = 'SELECT * FROM board';
		$st = $mysqli->prepare($sql);
		$st->execute();
		$res = $st->get_result();

        $board = [];

        if ($res->num_rows > 0) {
            // Loop through the result set and populate the 2D array
            while ($row = $res->fetch_assoc()) {
                $x = $row['x'] - 1;
                $y = $row['y'] - 1;
                
                // Ensure the x dimension exists
                if (!isset($board[$x])) {
                    $board[$x] = [];
                }
                
                // Assign the data for the specific cell
                $board[$x][$y] = [
                    'piece_color' => $row['piece_color'],
                    'piece' => $row['piece']
                ];
            }
        }

        return $board;
}

// Get the blocks of the players
function get_blocks(){
    global $mysqli;
	
		$sql = 'SELECT * FROM blocks';
		$st = $mysqli->prepare($sql);
		$st->execute();
		$res = $st->get_result();

        $blocks = [];

        if ($res->num_rows > 0) {
            // Fetch rows and group them by color
            while ($row = $res->fetch_assoc()) {
                $color = $row['color'];
                $piece = $row['piece'];
    
                // Ensure the color key exists in the dictionary
                if (!isset($blocks[$color])) {
                    $blocks[$color] = [];
                }
    
                // Add the piece to the array for the corresponding color
                $blocks[$color][] = $piece;
            }
        }

        return $blocks;
}

// function to get (and refresh) the state of the game
function get_game(){
	global $mysqli;
	
	$sql = 'SELECT * FROM game_status';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_all(MYSQLI_ASSOC);

	
	$state = array(
		"status" => $r[0]['status'],
		"player" => $r[0]['player'],
	);
	
	return $state;
}

function inspect_placement($input){
    
    // Prepare response
    header('Content-Type: application/json');
    if (validate_placement($input)) {
        echo json_encode(['status' => 'success', 'message' => 'Valid move']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid move']);
        http_response_code(400);
    }
}

function place_piece($input){
    global $mysqli;

    $piece = $input['piece']; 
    $color = $input['color']; 
    $token = $input['token'];

    $status = get_game()['status'];
    if($status !== 'STARTED'){  // The game is not started yet Bad Request
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Game has not started']);
        http_response_code(400);
        return;
    }

    if (!(check_token($color, $token))){    // The player has wrong token, Unauthorised
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Wrong token']);
        http_response_code(401);
        return;
    }

    $turn=get_game()['player'];	
    if ($turn !== $color){    // It is not the player turn, Forbidden
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Wait your turn']);
        http_response_code(403);
        return;
    }

    $blocks = get_blocks();
    if(!in_array($piece, $blocks[$color])){   // The player doesn't have that block, Bad request
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Block missing']);
        http_response_code(400);
        return;
    }

    $changes = validate_placement($input);
    if (empty($changes)) {  // Move not allowed, bad request
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid move']);
        http_response_code(400);
        return;
    }
       

    foreach ($changes as [$x, $y]) {
        $x++;
        $y++;
        $sql = 'UPDATE `board`
                SET `piece_color` = ?, `piece` = ?
                WHERE `x` = ? AND `y` = ?;';
        $st = $mysqli->prepare($sql);
        $st->bind_param('siii',$color,$piece,$x,$y);
        $st->execute();	
    }  
    
    $sql = "UPDATE `player` SET `passed`=FALSE WHERE `piece_color` = ?;";
	$st = $mysqli->prepare($sql);
    $st->bind_param('s',$color);
	$st->execute();

    check_winner();

    change_player();
}

function pass_turn($input){
    $color = $input['color']; 
    $token = $input['token'];

    $status = get_game()['status'];
    if($status !== 'STARTED'){  // The game is not started yet Bad Request
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Game has not started']);
        http_response_code(400);
        return;
    }

    if (!(check_token($color, $token))){    // The player has wrong token, Unauthorised
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Wrong token']);
        http_response_code(401);
        return;
    }

    $turn=get_game()['player'];	
    if ($turn !== $color){    // It is not the player turn, Forbidden
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Wait your turn']);
        http_response_code(403);
        return;
    }

    global $mysqli;
    $sql = "UPDATE `player` SET `passed`=TRUE WHERE `piece_color` = ?;";
	$st = $mysqli->prepare($sql);
    $st->bind_param('s',$color);
	$st->execute();

    check_winner();

    change_player();
}
function validate_placement($input){
    // Validate input
    if (!isset($input['x'], $input['y'], $input['piece'], $input['color'])) { // Check the necessary parameters
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Parameters missing']);
        http_response_code(400);
        return [];
    }

    // Extract input parameters
    $x = $input['x'];
    $y = $input['y'];
    $piece = $input['piece']; 
    $color = $input['color'];
    if (!isset($input['rotation'])) {
        $rotation = 0;
    }
    else {
        $rotation = $input['rotation'];
    }

    global $colors;
    if (!in_array($color, $colors)){    // Check color
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid color']);
        http_response_code(400);
        return [];
    }

    global $block_types;
    if ($piece < 0 || $piece > count($block_types)){    // Check piece type
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid piece']);
        http_response_code(400);
        return [];
    }

    $block = $block_types[$piece];

    $board = get_board();
    if ( $x > count($board) || $x < 1 || $y > count($board) || $y < 1){    // Check piece type
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Out of bounds']);
        http_response_code(400);
        return [];
    }
    // Call check_placement function
    return check_placement($x - 1, $y - 1, $block, $color, $rotation);
}
function check_placement($x, $y, $piece, $color, $rotation){
    $block = $piece;

    // Rotate the block as needed before running the checks
    for ($i = 0; $i < $rotation; $i++){
        $block = rotateTableClockwise($block);
    }

    $board = get_board();

    $blockRows = count($block);
    $blockCols = count($block[0]);
    $boardRows = count($board);
    $boardCols = count($board[0]);

    $adjacentColor = false;

    // All the direction to check for color
    $directions = [
        [-1, -1],   [-1, 0],    [-1, 1],
        [0, -1],                [0, 1],
        [1, -1],    [1, 0],     [1, 1]
    ];

    $changes = [];

    for ($i = 0; $i < $blockRows; $i++) {
        for ($j = 0; $j < $blockCols; $j++) {
            if ($block[$i][$j] === 1) { // A tile on the block
                // Match with the coordinates on the board
                $boardX = $x + $i;
                $boardY = $y + $j;

                $changes[] = [$boardX, $boardY];

                // Check if the block goes out of bounds
                if ($boardX < 0 || $boardX >= $boardRows || $boardY < 0 || $boardY >= $boardCols) {
                    return []; 
                }

                // Check if the block covers another block
                if ($board[$boardX][$boardY]['piece_color'] !== null){
                    return [];
                }

                foreach ($directions as [$dx, $dy]) {
                    $neighborX = $boardX + $dx;
                    $neighborY = $boardY + $dy;

                    if (
                        $neighborX >= 0 && $neighborX < $boardRows && 
                        $neighborY >= 0 && $neighborY < $boardCols &&
                        $board[$neighborX][$neighborY]['piece_color'] === $color
                    ) {
                        $adjacentColor = true;
                    }
                }
            }
        }
    }

    if (!$adjacentColor) {  // No adjacent tile, Check the four corners for the first move
        $corners = [
            [0, 0],                 [0, $boardCols - 1],
            [$boardRows - 1, 0],    [$boardRows - 1, $boardCols - 1]
        ];
        
        foreach ($corners as $corner) {
            if (in_array($corner, $changes)) {
                return $changes;
            }
        }
        return [];
    }

    return $changes; 
}

// Rotate a table 90 degrees
function rotateTableClockwise($table) {
    $rotated = [];
    $rows = count($table);    // Number of rows
    $cols = count($table[0]); // Number of columns

    for ($i = 0; $i < $cols; $i++) {
        $newRow = [];
        for ($j = $rows - 1; $j >= 0; $j--) {
            $newRow[] = $table[$j][$i];
        }
        $rotated[] = $newRow;
    }

    return $rotated;
}

function change_player(){
    $turn = get_game()['player'];
    global $colors;
    global $mysqli;

    $index = array_search($turn, $colors);

    $index++;
    if ($index >= count($colors)) {
        $index = 0;
    }
    $player = $colors[$index];
    $sql = 'UPDATE `game_status` SET `player` = ?;';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s',$player);
    $st->execute();	

    $sql = "UPDATE `player` SET `last_action`=NOW() WHERE `piece_color` = ?;";
	$st = $mysqli->prepare($sql);
    $st->bind_param('s',$player);
	$st->execute();
}

function check_winner(){
    global $mysqli;
    $sql = "SELECT `color`, COUNT(*) AS count FROM `blocks` GROUP BY `color`;";
	$st = $mysqli->prepare($sql);
	$st->execute();
    $res = $st->get_result();
	$r = $res->fetch_all(MYSQLI_ASSOC);

    $lowestCount = null;
    $lowestColor = null;

    foreach ($r as $row) {
        if ($lowestCount === null || $row['count'] < $lowestCount) {
            $lowestCount = $row['count'];
            $lowestColor = $row['color'];
        }
    }

    if ($lowestCount == 0){
        declare_winner($lowestColor);
    }
    else {
        $sql = "SELECT COUNT(*) as count FROM `player` WHERE `passed`=TRUE;";
        $st = $mysqli->prepare($sql);
        $st->execute();
        $res = $st->get_result();

        $count = 0;
        while ($row = $res->fetch_assoc()) {
            $count = $row["count"];
        }

        if ($count == 0){
            declare_winner($lowestColor);
        }
    }
}

function declare_winner($color){
    global $mysqli;

    $sql = "UPDATE `game_status`
            SET `result` = ?, `status` = 'ENDED';";
    $st = $mysqli->prepare($sql);
    $st->bind_param('s',$color);
    $st->execute();	
}
function check_moves(){

}