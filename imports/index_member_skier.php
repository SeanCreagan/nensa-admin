<html>
<head>
	<style type="text/css">
	body
	{
		margin: 0;
		padding: 0;
		background-color:#D6F5F5;
		text-align:center;
	}
	.top-bar
		{
			width: 100%;
			height: auto;
			text-align: center;
			background-color:#FFF;
			border-bottom: 1px solid #000;
			margin-bottom: 20px;
		}
	.inside-top-bar
		{
			margin-top: 5px;
			margin-bottom: 5px;
		}
	.link
		{
			font-size: 18px;
			text-decoration: none;
			background-color: #000;
			color: #FFF;
			padding: 5px;
		}
	.link:hover
		{
			background-color: #9688B2;
		}
	</style>


</head>

<body>
  <div style="border:1px dashed #333333; width:500px; margin:0 auto; padding:10px;">

	</br><strong>Load Member Skier CSV Data</strong></br>
  </br><strong>Default setting clobbers the table and reloads from scratch!  Adjust setting in code.</strong></br>

		<form name="import" method="post" enctype="multipart/form-data">
	    <input type="file" name="file" /><br />
	    <input type="submit" name="submit" value="Submit Member Skier Data" />
	  </form>

		<?php
			include ("connection.php");
		  ini_set('auto_detect_line_endings', true);
			if(isset($_POST["submit"]))
			{
				$file = $_FILES['file']['tmp_name'];

				if ($_FILES['file']['type'] != 'text/csv') {
					echo 'ERROR: The import format must be CSV. ';
					exit;
				}

				$handle = fopen($file, "r");
				$c = 0;
				$e = 0;
				$d = 0;
				$sql = null;

		    $reload = false;

		    if ($reload == false) {
				  $sql_string = "DELETE FROM MEMBER_SKIER";  // Choose or not to delete table first
					$sql = mysqli_query($conn, $sql_string);
				}

				$row = 0;
				while(($filesop = fgetcsv($handle, 10000)) !== false)
				{
					if ($row == 0 && (string)$filesop[0] == 'Account ID' && (string)$filesop[5] == 'City' && (string)$filesop[9] == 'Club Name') {
						$row+=1;
						continue;
					} elseif ($row == 0) {
						echo "ERROR: Wrong row or column format. ";
						break;
					}
					$nensa_num = (int)$filesop[0]; // Account ID
					$first = mysqli_real_escape_string($conn, $filesop[2]); // First Name
					$last = mysqli_real_escape_string($conn, $filesop[3]); // Last Name(F)
					$sex = mysqli_real_escape_string($conn, $filesop[4]);
					$city = mysqli_real_escape_string($conn, $filesop[5]); // City
					$state = mysqli_real_escape_string($conn, $filesop[6]); // State				
					$ussa_num = (int)$filesop[7]; // USSA Number(C)
					$club_name = (string)$filesop[9]; // Club Name
					$dob_day = (string)$filesop[10]; // DOB Day
					$dob_month = (string)$filesop[11]; //DOB Month
					$dob_year = (string)$filesop[12]; // DOB Year

	        if (strlen($dob_day) == 1) {
						$dob_day = '0'.$dob_day;
					}
					if (strlen($dob_month) == 1) {
						$dob_month = '0'.$dob_month;
					}
					if ((int)$dob_year < 20) {
						$dob_year = '20'.$dob_year;
					} elseif ((int)$dob_year < 100) {
						$dob_year = '19'.$dob_year;
					}
	        $birthdate = $dob_year.'-'.$dob_month.'-'.$dob_day;

	        if ($reload == true) {
	        	// If reloading file, skip over exisiting nensa_num entries.  Don't bother counting.
	          $result = $conn->query("SELECT COUNT(nensa_num) FROM MEMBER_SKIER WHERE nensa_num='$nensa_num'");
	          if ($result->num_rows > 0) {
						  continue;
	          }
	        }

	        $sql_string = "INSERT INTO MEMBER_SKIER (nensa_num, ussa_num, first, last, sex, birthdate, birth_year, city, state, club_name) VALUES ('$nensa_num', NULLIF('$ussa_num',0), '$first', '$last', '$sex', '$birthdate', '$dob_year', '$city', '$state', '$club_name')";
					$sql = mysqli_query($conn, $sql_string);

					//  The most likely failure is a duplicate entry with ussa_num
					if ($sql == 0) {
		        $text = $conn->error;
						if (strpos($text, 'Duplicate entry') !== false) {
						  $d = $d + 1;
						} else {
							$e = $e + 1;
						}

		        continue;
					}
		      $c = $c + 1;
				}

				echo "You imported ". $c ." records successfully. There were ". $e ." errors and ". $d ." duplicates";

			}
		?>

  </div>
  <hr style="margin-top:300px;" />

  <div align="center" style="font-size:18px;"><a href="http://www.nensa.net">&copy; New England Nordic Ski Association</a></div>

</body>
</html>
