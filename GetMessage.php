<?php

	define("msg_sep", "@(mN@n9");
	define("msg_data_sep", "g5eh32^");
	define("user_sep", "3sxDS>");
	define("user_data_sep", "ck@&k`");
	define("preSecs", 20);

	$response = array();
	
	isset($_GET["nickname"]) or die("Error in fetching message");
	$nickname = $_GET["nickname"];
	
	$predate = date_create(date("Y-m-d H:i:s", time() - preSecs));
	
?>


<?php
	
	if(!file_exists("messages")) {
		file_put_contents("messages", "");
	}
	if(!file_exists("online")) {
		file_put_contents("online", "");
	}
	
	$file_contents = file_get_contents("messages");
	if($file_contents === "") {
		exit();
	}
	
	$online_contents = file_get_contents("online");
	if($online_contents === "") {
		exit();
	}
	
	$user_date = "";
	$user_found = false;
	
	$online_array = explode(user_sep, $online_contents);
	foreach($online_array as $raw_user) {
		$user = explode(user_data_sep, $raw_user);
		if($user[0] === $nickname) {
			$user_date = date_create($user[1]);
			$user_found = true;
			break;
		}
	}
	if(!$user_found) {
		exit();
	}
	
	$msg_array = explode(msg_sep, $file_contents);
	
	function date_compare($d1, $d2) {
		return $d1 > $d2 ? $d1 : $d2;
	}
	
	$thedate = date_compare($user_date, $predate);
	
	foreach($msg_array as $msg) {
		$raw_data = explode(msg_data_sep, $msg);
		$msg_data["md5"] = $raw_data[0];
		$msg_data["time"] = $raw_data[1];
		$msg_data["nickname"] = $raw_data[2];
		$msg_data["message"] = $raw_data[3];
		if($thedate < date_create($raw_data[1])) {
			$response[] = $msg_data;
		}
	}
	
	echo json_encode($response);
	
?>
