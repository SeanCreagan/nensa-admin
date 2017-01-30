<?php
/**
 * Llorix One Lite functions and definitions
 *
 * @package llorix-one-lite
 */

function import_results() {

global $wpdb1;
?>
	</br><strong>Load 2016/2017 Event Data</strong></br>
	</br>

	<form name="import" method="post" enctype="multipart/form-data">
		<table class="form-table"> 
      <tr valign="top"><th scope="row"><?php _e('Select Season:','nensa_admin'); ?></th>
        <td>
			    <select id="event_select" name="event_select" value="">
			        <option name="" value=""></option>
			        
			        <?php  // Get all db table names
			        global $wpdb1;
			        $sql = "SELECT  event_name  FROM RACE_EVENT WHERE season=2017 AND parent_event_id<>0;";
			        $results = $wpdb1->get_results($sql);
			        $repop_table = isset($_POST['event_select']) ? $_POST['event_select'] : null;
			        
			        foreach($results as $index => $value) {
			            foreach($value as $eventName) {
			                ?><option name="<?php echo $eventName ?>" value="<?php echo $eventName ?>" <?php if($repop_table === $eventName) { echo 'selected="selected"'; } ?>><?php echo $eventName ?></option><?php
			            }
			        }
			        ?>
			    </select>
			  </td>
			</tr>
			 <tr valign="top"><th scope="row"><?php _e('Select CSV File:','nensa_admin'); ?></th>
			  <td>
			  	<input type="file" name="file" />
			  </td>
			</tr>
		</table>
		<p class="submit">
	    <input type="submit" name="submit" class="button-primary" value="<?php _e('Import Event', 'nensa_admin') ?>" />
	  </p>
  </form>


<?php
	include ("connection.php");
  ini_set('auto_detect_line_endings', true);
	if(isset($_POST["submit"]))
	{

		$event_name = $_POST['event_select'];

		$result = $conn->query("SELECT event_id FROM RACE_EVENT WHERE event_name='$event_name'");
			
	  if ($result->num_rows == 1) {
			$row = $result->fetch_assoc();
	    $event_id = (int)$row['event_id'];
	  } else {
	  	echo 'ERROR: Event Not Matched in Database. ';
			exit;
	    $event_id = NULL;
	  }

		$file = $_FILES['file']['tmp_name'];

		if ($_FILES['file']['type'] != 'text/csv') {
			echo 'ERROR: The import format must be CSV. ';
			exit;
		}

		$handle = fopen($file, "r");
		$c = 0;
		$e = 0;
		$d = 0;
		$m = 0;
		$sql = null;
		$u16 = false;
	  $correct_header = false;
		while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
		{
			if ($correct_header == false && (((string)$filesop[0] == 'FinishPlace') || (string)$filesop[1] == 'FinishPlace')) {
				if ((string)$filesop[1] == 'FinishPlace') { $u16 = true; }
				$correct_header = true;
				continue;
			} elseif ($correct_header == false) {
				continue;
			}

			if ($u16 == false) {
				$wcp = 0;
				$finish_place = $filesop[0];
				$athlete_id = $filesop[1];  // column name in the csv file
				$full_name = $filesop[2];
				$birth_year = $filesop[3];
				$division = (string)$filesop[4];
				$race_time = (string)$filesop[5];
				$points = $filesop[6];
				$ussa_results = $filesop[7];
                                    } else {
				$wcp = $filesop[0];
				$finish_place = $filesop[1];
				$athlete_id = $filesop[2];  // column name in the csv file
				$full_name = $filesop[3];
				$birth_year = $filesop[4];
				$division = (string)$filesop[5];
				$race_time = (string)$filesop[6];
				$points = $filesop[7];
				$ussa_results = $filesop[8];
			}
			//$event_id = 1951;

			$result = $conn->query("SELECT member_id FROM MEMBER_SKIER WHERE ussa_num='$athlete_id'");
			
      if ($result->num_rows > 0) {
      	// output data of each row
          while($row = $result->fetch_assoc()) {
              $member_id = (int)$row['member_id'];
          }
      } else {
      	$text = $conn->error;
        $member_id = NULL;  // #1 set member_season_id to NULL, or #2 set member_season_id to 990
      }

      if ($member_id != NULL) {
        $result = $conn->query("SELECT id FROM MEMBER_SEASON WHERE member_id='$member_id'");
	
        if ($result->num_rows > 0) {
        	// output data of each row
        	while($row = $result->fetch_assoc()) {
            	$member_season_id = (int)$row['id'];
            }
        } else {
        	$text = $conn->error;
          $member_season_id = NULL;  // #1 set member_season_id to NULL, or #2 set member_season_id to 990
        }
      } else {
      	$member_season_id = NULL;
      }

      // Skip over duplicate entry
      $result = $conn->query("SELECT * FROM RACE_RESULTS WHERE event_id='$event_id' AND ussa_num='$athlete_id'");
      $count = mysqli_num_rows($result);
      if ($count > 0) { 
      	$d += 1;
      	continue; 
      }
			
			$sql = mysqli_query($conn, "INSERT INTO RACE_RESULTS (world_cup_points, member_season_id, ussa_num, Finish_Place, Full_Name, Birth_Year, Race_Points, USSA_Result, event_id, Division, Race_Time) VALUES (NULLIF('$wcp',0), NULLIF('$member_season_id',0), '$athlete_id', '$finish_place', '$full_name', '$birth_year', '$points','$ussa_results', '$event_id', '$division', '$race_time')");
      
      if ($sql == 0) {
		    $text = $conn->error;
	    	if (strpos($text, 'Duplicate entry') !== false) {
				  $m += 1;
				} else {
					$e += 1;
				}
		  } else {
		  	$c += 1;
		  } 
		}

		echo "You have imported ". $c ." results. You skipped over ". $d ." duplicate records. There were ". $m ." member_season_id conflicts. There were ". $e ." errors.";

	}
}


function import_member_skier () {

	?>
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
}


function import_member_season () {

	?>
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
}
?>