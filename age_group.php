<?php
/*
U16 (14-15 year olds)
U18 (16-17 year olds)
U20 (18-19 year olds)
U23 age group (20, 21 & 22 year olds)
Senior Age Group (23-29 year olds)

Bill Koch League (BKL):
NENSA BKL Age Groups are classified by year in school.                            
Lollipop:    Ages 0-7
U6: Ages 4-5: Grade PreK – K
U8: Ages 6-7: Grades 1 & 2
Junior 5 / (U10):     Ages 8-9      Grades 3 & 4
Junior 4 / (U12):     Ages 10-11   Grades 5 & 6
Junior 3 / (U14):     Ages 12-13   Grades 7 & 8

NENSA Masters Age Group Breakdown:
Master 1 / M1: 30-34
Master 2 / M2: 35-39
Master 3 / M3: 40-44
Master 4 / M4: 45-49
Master 5 / M5: 50-54
Master 6 / M6: 55-59
Master 7 / M7: 60-64
Master 8 / M8: 65-69
Master 9 / M9: 70-74
Master 10 / M10: 75-79
Master 11/M11: 80-84
Master 12/M12: 85-89
Master 13/M13: 90-94
Master 14/M14: 95-100
*/

# Can't determine grades so juniors not processed here
function getAgeGroup($dob_year) {
	if ($dob_year == 0 || is_null($dob_year)) {
		return '';
	}

	# if before June meaning 2016/2017 season, need to be of age by Dec 31st, 2016
	$this_season = 0;
	if (date("m") < 6) { $this_season = -1; }

	if ($dob_year < 100 && $dob_year > date("y")) {
		$dob_year = $dob_year + 1900 + $this_season;
	} elseif (!is_null($dob_year) && (int)$dob_year < (date("y")+1)) {
		$dob_year + 2000 + $this_season;
	}
	
	$cur_year = date("Y");
  $age = $cur_year - (int)$dob_year; 

  switch (true) {
  	case ($age > 105):
	    return "";  // most likely an error though you never know these days
	  case ($age > 94):
	    return "M14";
	  case ($age > 89 && $age < 95):
	  	return "M13";
	  case ($age > 84 && $age < 90):
	  	return "M12";
	  case ($age > 79 && $age < 85):
	  	return "M11";
	  case ($age > 74 && $age < 80):
	  	return "M10";
	  case ($age > 69 && $age < 75):
	  	return "M9";
	  case ($age > 64 && $age < 70):
	  	return "M8";
	  case ($age > 59 && $age < 65):
	  	return "M7";
	  case ($age > 54 && $age < 60):
	  	return "M6";
	  case ($age > 49 && $age < 55):
	  	return "M5";
	  case ($age > 44 && $age < 50):
	  	return "M4";
	  case ($age > 39 && $age < 45):
	  	return "M3";
	  case ($age > 34 && $age < 40):
	  	return "M2";
	  case ($age > 29 && $age < 35):
	  	return "M1";
	  case ($age > 22 && $age < 30):
	  	return "SR";
	  case ($age > 19 && $age < 23):
	  	return "U23";
	  case ($age > 17 && $age < 20):
	  	return "U20";
	  case ($age > 15 && $age < 18):
	  	return "U18";
	  case ($age > 13 && $age < 16):
	  	return "U16";
	  case ($age > 11 && $age < 14):
	  	return "U14";
	  case ($age > 9 && $age < 12):
	  	return "U12";
	  case ($age > 7 && $age < 10):
	  	return "U10"; 
	  case ($age > 5 && $age < 8):
	  	return "U8";
	  case ($age > 3 && $age < 6):
	  	return "U6"; 		  		  	
	  default:
	  	return "";
	}
}

?>