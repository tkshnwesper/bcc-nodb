<?php

	define("msg_seperator", "@(mN@n9");
	define("data_sep", "g5eh32^");

	$response = array();
	
?>


<?php
	
	if(file_exists("messages")) {
		$file_contents = file_get_contents("messages");
	}
	else {
		exit();
	}
	$msg_array = explode(msg_seperator, $file_contents);
	
	foreach($msg_array as $msg) {
		$raw_data = explode(data_sep, $msg);
		$msg_data["md5"] = $raw_data[0];
		$msg_data["time"] = $raw_data[1];
		$msg_data["nickname"] = $raw_data[2];
		$msg_data["message"] = $raw_data[3];
		$response[] = $msg_data;
	}
	
	echo json_encode($response);
	
?>
