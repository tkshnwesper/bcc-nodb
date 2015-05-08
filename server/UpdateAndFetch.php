<?php
	
	define("lock_sep", "#");
	define("user_sep", "3k43@dk4sg)KsxDS>");
	define("data_sep", "ck334dsd*$*@&k`");
	define("preSecs", 15);
	
	$response = array();
	
	isset($_GET["nickname"]) or die("Error in fetching message");
	$nickname = $_GET["nickname"];
	
	function msleep($t) {
		usleep($t * 1000);
	}
	
?>

<?php
	
	$datetime = date("Y-m-d H:i:s");
	
	if(!file_exists("online.lock")) {
		file_put_contents("online.lock", "false" . lock_sep . $datatime);
	}
	if(!file_exists("online")) {
		file_put_contents("online", "");
	}
	
	while(true) {
		$lock_contents = explode(lock_sep, file_get_contents("online.lock"));
		if($lock_contents[0] === "true") {
			msleep(100);
		}
		else {
			$lock_contents[0] = "true";
			file_put_contents("online.lock", implode(lock_sep, $lock_contents));
			break;
		}
	}

<?php
	
	$preTime = time() - preSecs;
	$predatetime = date("Y-m-d H:i:s", $preTime);
	
	$user_array = explode(user_sep, file_get_contents("online"));
	$final_array = array();
	
	function add_to_arrays($u) {
		$response[] = $u["name"];
		$final_array[] = implode(data_sep, $u);
	}
	
	$user_found = false;
	
	foreach($user_array as $raw_user_data) {
		$raw_data = explode(data_sep, $user);
		$user["name"] = $raw_data[0];
		$user["datetime"] = $raw_data[1];
		
		if($user["name"] === $nickname) {
			$user["datetime"] = $datetime;
			add_to_arrays($user);
			$user_found = true;
		}
		elseif(date_create($user["datetime"]) > date_create($predatetime)) {
			add_to_arrays($user);
		}
	}
	
	if(!$user_found) {
		add_to_arrays(array("name" => $nickname, "datetime" => $datetime));
	}
	
	file_put_contents("online", implode(user_sep, $final_array);
	file_put_contents("online.lock", "false" . lock_sep . $datatime);
	
	echo json_encode($response);
		
?>
