<?php

	header("Access-Control-Allow-Origin: *");

	define("user_sep", ">");
	define("data_sep", "<");
	
	(isset($_GET["oldnick"]) and isset($_GET["newnick"])) or die("Error in fetching message");
	$oldnick = $_GET["oldnick"];
	$newnick = $_GET["newnick"];
	
	foreach(array($oldnick, $newnick) as $i) {
		if(preg_match("/>/", $i) || preg_match("/</", $i)) {
			exit("Invalid input");
		}
	}

	if(!file_exists("online.lock")) {
		file_put_contents("online.lock", "false");
	}
	if(!file_exists("online")) {
		file_put_contents("online", "");
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
	
?>

<?php
	
	$file_contents = file_get_contents("online");
	if($file_contents === "") {
		file_put_contents("online", $newnick . data_sep . date("Y-m-d H:i:s"));
		file_put_contents("online.lock", "false");
		echo json_encode(array("nickname" => $newnick, "success" => "true"));
		exit();
	}
	
	$finish = array();
	
	$user_array = explode(user_sep, $file_contents);
	foreach($user_array as $raw_data) {
		$data = explode(data_sep, $raw_data);
		if($data[0] === $newnick) {
			echo json_encode(array("success" => "false"));
			exit();
		}
		if($data[0] === $oldnick) {
			$data[0] = $newnick;
		}
		$finish[] = implode(data_sep, $data);
	}
	
	file_put_contents("online", implode(user_sep, $finish));
	file_put_contents("online.lock", "false");
	
	echo json_encode(array("nickname" => $newnick, "success" => "true"));
	
?>
