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

		</br><strong>Load Inidividual Event CSV Data</strong></br>
    </br>
	    <strong>
		    Default setting appends row to the table.  
		    Auto delete not coded yet. Select list for indexes conforms to my personal list.  
		    Adjust accordingly in the code
	  	</strong>
  	</br>

		<form name="import" method="post" enctype="multipart/form-data">
			<select name="EventID">
				<option value=1951>1951</option>
				<option value=1952>1952</option>
				<option value=1953>1953</option>
				<option value=1954>1954</option>
				<option value=1955>1955</option>
				<option value=1956>1956</option>
				<option value=1957>1957</option>
				<option value=1961>1961</option>
				<option value=1962>1962</option>
				<option value=1963>1963</option>
				<option value=1964>1964</option>
				<option value=1965>1965</option>
				<option value=1966>1966</option>
			</select>
	  	<input type="file" name="file" /><br />
	    <input type="submit" name="submit" value="Submit Race Results" />
	  </form>

		<?php
			include ("connection.php");
		  ini_set('auto_detect_line_endings', true);
			if(isset($_POST["submit"]))
			{
				$event_id = (int)$_POST['EventID']; 
				$file = $_FILES['file']['tmp_name'];

				if ($_FILES['file']['type'] != 'text/csv') {
					echo 'ERROR: The import format must be CSV. ';
					exit;
				}

				$handle = fopen($file, "r");
				$c = 0;
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

					// rules will go here
					$sql = mysqli_query($conn, "INSERT INTO RACE_RESULTS (world_cup_points, member_season_id, ussa_num, Finish_Place, Full_Name, Birth_Year, Race_Points, USSA_Result, event_id, Division, Race_Time) VALUES (NULLIF('$wcp',0), NULLIF('$member_season_id',0), '$athlete_id', '$finish_place', '$full_name', '$birth_year', '$points','$ussa_results', '$event_id', '$division', '$race_time')");
		      
		      if ($sql == 0) {
				    $text = "member_season_id: ".$member_season_id." error: ".$conn->error;
				    echo $text;
				  }

		      $c = $c + 1;
				}

					if($sql){
						echo "You database has imported successfully. You have inserted ". $c ." records";
					}else{
						echo "Sorry! ".$c." There is some problem with ".$file;
					}

			}
		?>

    </div>
    <hr style="margin-top:300px;" />

    <div align="center" style="font-size:18px;"><a href="http://www.nensa.net">&copy; New England Nordic Ski Association</a></div>

</body>
</html>
