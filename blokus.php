<?php
require_once "./lib/game.php";
require_once "./lib/blocks.php";
require_once "./lib/players.php";
require_once "./dbconnect2.php";

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

switch ($r=array_shift($request)) {
	case 'game':
		handle_game($method, $input);
		break;
        
    case 'blocks':
	
		handle_blocks($method, $input);
        break;

	case 'shapes':
		
		handle_shapes($method, $input);
		break;
				
	case 'board':
        switch ($b=array_shift($request)) {
			case '':
			case null:	
				handle_board($method,$input);
				break;			
			default: 
				header("HTTP/1.1 404 Not Found");
				break;
		}
		break;	
	
	case 'place':
		handle_placement($method, $input);
		break;

	default:
		header("HTTP/1.1 404 Not Found");
		exit;
}

function handle_game($method,$input) {
	if($method=='GET'){
		inspect_game();
	} 
    else if($method=='POST'){
		find_game($input);
	}
	else {
		header('HTTP/1.1 405 Method Not Allowed');
	}
}

function handle_board($method,$input) {
	if($method=='GET'){
		show_board();
	}  
	else {
		header('HTTP/1.1 405 Method Not Allowed');
	}
}

function handle_blocks($method,$input) {
	if($method=='GET'){
		show_blocks();
	}  
	else {
		header('HTTP/1.1 405 Method Not Allowed');
	}
}

function handle_shapes($method,$input) {
	if($method=='GET'){
		show_shapes();
	}  
	else {
		header('HTTP/1.1 405 Method Not Allowed');
	}
}

function handle_placement($method,$input) {
	if($method=='GET'){
		inspect_placement($input);
	}
	else if($method=='PUT'){
		place_piece($input);
	}
	else {
		header('HTTP/1.1 405 Method Not Allowed');
	}
}

?>