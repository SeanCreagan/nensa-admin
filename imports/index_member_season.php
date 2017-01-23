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

	</br><strong>Load Member Season CSV Data</strong></br>
  </br><strong>Default setting clobbers the table and reloads from scratch!  Adjust setting in code.</strong></br>


	<form name="import" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /><br />
    <input type="submit" name="submit" value="Submit Member Season Data" />
  </form>

	<?php
		include ("connection.php");
		include ("age_group.php");
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
			$correct_header = false;

	    $reload = false;

	    if ($reload == false) {
			  $sql_string = "DELETE FROM MEMBER_SEASON";  // Choose or not to delete table first
			  $sql = mysqli_query($conn, $sql_string);
			}
			while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
			{

				if ($correct_header == false && (string)$filesop[0] == 'Account ID' && (string)$filesop[11] == 'USSA Number (C)') {
					$correct_header = true;
					continue;
				} elseif ($correct_header == false) {
					continue;
				}

				$age_season = NULL;
				$nensa_num = (int)$filesop[0]; // Account ID
				$dob_year = (int)$filesop[4]; // DOB Year
				$state = $filesop[5]; // State
				$member_level = $filesop[6];  // Membership Name
				$age_group = $filesop[14]; // Age Group (C)
				$season = 2017;
				$member_status = 'Active';

				$result = $conn->query("SELECT member_id, club_name FROM MEMBER_SKIER WHERE nensa_num='$nensa_num'");
				
        if ($result->num_rows > 0) {
        	// output data of each row
          while($row = $result->fetch_assoc()) {
            $member_id = (int)$row['member_id'];
            $club_name = (string)$row['club_name'];
          }
        } else {
          continue;
        }

				if ($age_group == '') {
					$age_group = getAgeGroup($dob_year);
				}

				if (!is_null($dob_year) && (int)$dob_year > 1900) {
  				$age_season = date("Y") - (int)$dob_year;
  			} elseif ((int)$dob_year < 100 && (int)$dob_year > date("y")) {
  				$age_season = date("Y") - (int)$dob_year - 1900;
  			} elseif (!is_null($dob_year) && (int)$dob_year < (date("y")+1)) {
  				$age_season = date("Y") - (int)$dob_year - 2000;
  			}

				if ($reload == true) {
        	// If reloading file, skip over exisiting nensa_num entries.  Don't bother counting.
          $result = $conn->query("SELECT COUNT(nensa_num) FROM MEMBER_SEASON WHERE nensa_num='$nensa_num'");
          if ($result->num_rows > 0) {
					  continue;
          }
        }

				$sql = mysqli_query($conn, "INSERT INTO MEMBER_SEASON (member_id, nensa_num, season, member_status, member_level, age_group, age_season, club_name) VALUES ('$member_id','$nensa_num', '$season', '$member_status', '$member_level','$age_group', '$age_season', '$club_name')");

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

			if ($c == 0 && $correct_header == false) {
					echo "ERROR: Wrong row or column format. ";
			}

			echo "You imported ". $c ." records successfully. There were ". $e ." errors and ". $d ." duplicates";

		}
	?>

    </div>
    <hr style="margin-top:300px;" />

    <div align="center" style="font-size:18px;"><a href="http://www.nensa.net">&copy; New England Nordic Ski Association</a></div>

</body>
</html>
