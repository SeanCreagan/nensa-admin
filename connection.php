<?php

	$hostname = RESULTS_DB_HOST;
	$username = RESULTS_DB_USER;
	$password = RESULTS_DB_PASSWORD;
	$database = RESULTS_DB_NAME;

	$conn = mysqli_connect("$hostname","$username","$password","$database") or die(mysqli_error());
	//mysqli_select_db("$database", $conn) or die(mysqli_error());

?>