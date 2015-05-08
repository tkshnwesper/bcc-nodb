<?php
	
	(isset($_GET["oldnick"]) and isset($_GET["newnick"]) or die("Error in fetching message");
	
?>

<?php
	
	function execQuery($sql) {
		global $conn;
		$res = mysql_query($sql, $conn);
		return $res;
	}
	
?>

<?php
	
	$_SESSION["nickname"] = $_POST["nickname"];
	
	$sql = "update onlineusers set nickname = \"". $_POST["nickname"] . "\" where session_id = \"". session_id() . "\";";
	$res = execQuery($sql);
	
	$response = array();
	
	if(!$res) {
		$response["success"] = false;
	} else { $response["success"] = true; }
	
	echo json_encode($response);
	
	mysql_close($conn);
	
?>
