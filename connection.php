<?php

	$hostname = "localhost";
	$username = "nensa";
	$password = "nensa";
	$database = "nensa_results_db";


	$conn = mysqli_connect("$hostname","$username","$password","$database") or die(mysqli_error());
	//mysqli_select_db("$database", $conn) or die(mysqli_error());

?>