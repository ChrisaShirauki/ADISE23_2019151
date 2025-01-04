<?php
$board = array_fill(0, 20, array_fill(0, 20, 0));

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
	
	$sql = 'SELECT * FROM gamestate';
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
                $x = $row['x'];
                $y = $row['y'];
                
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
