<?php

	define("msg_seperator", "@(mN@n9");
	define("data_sep", "g5eh32^");
	define("max_stack_size", 20);

	function msleep($t) {
		usleep($t * 1000);
	}
	
	if(isset($_POST["message"]) && isset($_POST["nickname"])) {
		$message = $_POST["message"];
		$nickname = $_POST["nickname"];
	}
	else {
		die("Error in fetching message");
	}
	
	if(!file_exists("messages")) {
		file_put_contents("messages", "");
	}
	
	$datetime = date("Y-m-d H:i:s");
	
	$complete_file = file_get_contents("messages");
	if($complete_file === "") {
		file_put_contents("messages", implode(data_sep, array(md5($message . msg_seperator . data_sep), $datetime, $nickname, $message)));
		file_put_contents("messages.lock", "false");
		echo json_encode(array("success" => true));
		exit();
	}
	
	
	while(true) {
		$check_lock = file_get_contents("messages.lock");
		if($check_lock === "true") {
			msleep(100);
		}
		else {
			file_put_contents("messages.lock", "true");
			break;
		}
	}
	
?>


<?php


	$md5 = uniqid();
	$msg_array_raw = explode(msg_seperator, $complete_file);
	$msg_array = array();
	
	foreach($msg_array_raw as $msg) {
		$raw_data = explode(data_sep, $msg);
		$msg_data["md5"] = $raw_data[0];
		$msg_data["time"] = $raw_data[1];
		$msg_data["nickname"] = $raw_data[2];
		$msg_data["message"] = $raw_data[3];
		$msg_array[] = $msg_data;
	}
	
	if(sizeof($msg_array) >= max_stack_size) {
		array_pop($msg_array);
	}
	$msg_string_array = array("md5" => $md5, "time" => $datetime, "nickname" => $nickname, "message" => $message);
	array_unshift($msg_array, $msg_string_array);
	
	$final_string_array = array();
	foreach($msg_array as $raw_data) {
		$final_string_array[] = implode(data_sep, $raw_data);
	}
	$final_string = implode(msg_seperator, $final_string_array);
	
	file_put_contents("messages", $final_string);
	file_put_contents("messages.lock", "false");
	
	//~ echo json_encode(array("success" => true));
	echo json_encode($msg_array);
	
?>
