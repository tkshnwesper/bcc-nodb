<?php

	header("Access-Control-Allow-Origin: *");
	
	echo json_encode(array("newnick" => uniqid()));
	
?>
