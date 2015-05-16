<?php

	header("Access-Control-Allow-Origin: *");
	
	define("user_sep", ">");
	define("data_sep", "<");
	define("preSecs", 15);
	
	$response = array();
	
	isset($_GET["nickname"]) or die("Error in fetching message");
	$nickname = $_GET["nickname"];
	
	if(preg_match("/>/", $nickname) || preg_match("/</", $nickname)) {
		exit("Invalid input");
	}
	
	function msleep($t) {
		usleep($t * 1000);
	}
	
?>

<?php
	
	$datetime = date("Y-m-d H:i:s");
	
	if(!file_exists("online.lock")) {
		file_put_contents("online.lock", "false");
	}
	if(!file_exists("online")) {
		file_put_contents("online", "");
	}
	
	$file_contents = file_get_contents("online");
	
	if($file_contents === "") {
		file_put_contents("online", $nickname . data_sep . $datetime);
		file_put_contents("online.lock", "false");
		echo json_encode(array($nickname));
		exit();		
	}
	
	while(true) {
		$lock_contents = file_get_contents("online.lock");
		if($lock_contents === "true") {
			msleep(100);
		}
		else {
			file_put_contents("online.lock", "true");
			break;
		}
	}
	
	$final_array = array();
	$user_found = false;
		
	function add_to_arrays($u) {
		global $response, $final_array;
		$response[] = $u["name"];
		$final_array[] = implode(data_sep, $u);
	}
	

		
	$preTime = time() - preSecs;
	$predatetime = date("Y-m-d H:i:s", $preTime);
	
	$user_array = explode(user_sep, $file_contents);
	
	foreach($user_array as $raw_user_data) {
		$raw_data = explode(data_sep, $raw_user_data);
		$user["name"] = $raw_data[0];
		$user["datetime"] = $raw_data[1];
		
		if($user["name"] === $nickname) {
			$user["datetime"] = $datetime;
			add_to_arrays($user);
			$user_found = true;
		}
		else {
			if(date_create($user["datetime"]) > date_create($predatetime)) {
				add_to_arrays($user);
			}
		}
	}
		
	if(!$user_found) {
		add_to_arrays(array("name" => $nickname, "datetime" => $datetime));
	}
	
	if(sizeof($final_array) === 1) {
		file_put_contents("online", $final_array[0]);
	}
	else {
		file_put_contents("online", implode(user_sep, $final_array));
	}
	
	file_put_contents("online.lock", "false");
	
	echo json_encode($response);
		
?>
