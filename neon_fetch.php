<?php 

// Counts
function load_member_season ($membership_row) {
  include ("connection.php");

  $now = time();
  $may_31 = strtotime("31st May");

  if ($now > $may_31)
      $exp_date = date("Y-m-d", strtotime('+1 year', $may_31));
  else 
      $exp_date = date("Y-m-d", $may_31);

  // Expiration date is always on May 31st
  if (strpos($membership_row['Membership Name'], 'LIFE') !== false) {
    // Above is a standard pattern for string search - I'm not going to 
    // mess with the logic to get a negation here.  
    // Just skip the expiration check - easy enough. Have a nice day!
  } else if ($membership_row['Membership Expiration Date'] != $exp_date) {
    return 0;
  } 
   
  $dob_year = $membership_row['DOB Year'];

  if ($dob_year < 100 && $dob_year > date("y")) {
    $dob_year = $dob_year + 1900;
  } elseif (!is_null($dob_year) && (int)$dob_year < (date("y")+1)) {
    $dob_year + 2000;
  }
  
  $cur_year = date("Y");
  $age_season = $cur_year - (int)$dob_year; 
  
  // $age_season = (int)$membership_row['Age end of 2016'];
  $age_group = $membership_row['Age Group'];
  $club_name = $membership_row['Company Name']; 
  $nensa_num = (int)$membership_row['Account ID'];
  $member_level =  $membership_row['Membership Name']; 

  # In June
  if (date("m") > 5) {
    $season = date("Y")+1;
  } else {
    $season = date("Y");
  }
  $member_status = 'Active';
  $member_id = 0;
    
  // $membership_row['Membership Enrollment Date'];  
  // $membership_row['Membership Start Date']; 
  // $membership_row['Membership Cost']; 

  $result = $conn->query("SELECT member_id FROM MEMBER_SKIER WHERE nensa_num='$nensa_num'");
  
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $member_id = (int)$row['member_id'];
    }
  } else {
    return 0;
  }

  $result = $conn->query("SELECT * FROM MEMBER_SEASON WHERE nensa_num='$nensa_num'");
  $num_rows = mysqli_num_rows($result);
  if ($num_rows > 0) {
    $return = 1;
    $sql = mysqli_query($conn, "UPDATE MEMBER_SEASON SET season='$season', member_status='$member_status', member_level='$member_level', age_group='$age_group', age_season='$age_season', club_name='$club_name' WHERE nensa_num='$nensa_num'");
  } else {
    $return = 2;
    $sql = mysqli_query($conn, "INSERT INTO MEMBER_SEASON (member_id, nensa_num, season, member_status, member_level, age_group, age_season, club_name) VALUES ('$member_id','$nensa_num', '$season', '$member_status', '$member_level','$age_group', '$age_season', '$club_name')");
  }


  //  The most likely failure is a duplicate entry with ussa_num
  if ($sql == 0) {
    write_log($conn->error);
    return 0;
  }

  return $return;
}

function load_member_skier ($membership_row) {
  include ("connection.php");

  // Add to SQL statement NULLIF('$ussa_num',0) and remove
 
  $first = $membership_row['First Name']; 
  $last = $membership_row['Last Name']; 
  $gender = $membership_row['Gender']; 
  $city = $membership_row['City']; 
  $state = $membership_row['State'];
  $dob_day = $membership_row['DOB Day'];
  $dob_month = $membership_row['DOB Month'];
  $dob_year = $membership_row['DOB Year'];
  $country = $membership_row['Country'];
  $ussa_num = (int)$membership_row["USSA Number"];
  $nensa_num = (int)$membership_row['Account ID'];

  if ($ussa_num == '' || !isset($ussa_num)) {
    $ussa_num = 0;
  }

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
  $birth_year = $dob_year;

  $result = $conn->query("SELECT * FROM MEMBER_SKIER WHERE nensa_num='$nensa_num'");
  $num_rows = mysqli_num_rows($result);
  if ($num_rows > 0) {
    $return = 1;
    $sql = mysqli_query($conn, "UPDATE MEMBER_SKIER SET ussa_num=NULLIF('$ussa_num',0),first='$first',last='$last',sex='$gender',city='city',state='$state',country='$country',birthdate='$birthdate',birth_year='$birth_year' WHERE nensa_num='$nensa_num'");
  } else {
    $return = 2;
    $sql = mysqli_query($conn, "INSERT INTO MEMBER_SKIER (nensa_num, ussa_num, first, last, sex, city, state, country, birthdate, birth_year) VALUES ('$nensa_num', NULLIF('$ussa_num',0), '$first', '$last','$gender', '$city', '$state', '$country','$birthdate','$birth_year')");
  }

  //  The most likely failure is a duplicate entry with ussa_num
  if ($sql == 0) {
    write_log($conn->error);
    return 0;
  }

  return $return;
}

function fetch_member_data() {

  /* Include the NeonCRM PHP Library */
  require_once('neon.php');

  /**
   * API Authentication
   *******************************************/

  /* Instantiate the Neon class */
  $neon = new Neon();

  /* Set your API credentials */
  $credentials = array(
      'orgId' => NEON_USER,
      'apiKey' => NEON_APIKEY
  );

  $member_skier_date = get_option('member_skier_date');
  if ($member_skier_date == false) {
    add_option('member_skier_date','Never Processed');
    $member_skier_date = 'Never Processed';
  }

  $member_season_date = get_option('member_season_date');
  if ($member_season_date == false) {
    add_option('member_season_date','Never Processed');
    $member_season_date = 'Never Processed';
  }

  /* Authenticate with the API */
  $loginResult = $neon->login($credentials);

  /* Upon successful authentication, proceed with building the search query */
  if ( isset( $loginResult['operationResult'] ) && $loginResult['operationResult'] == 'SUCCESS' ) {

    /**
     * Search Query 
     * Customer fields use ID.  108 = USSA ID, 136 = Age Group, 171 = Age at end of season
     * Use "go2" search to fetch list of custom field (uncomment)
     *************************************************/
    $search_skier = array( 
        'method' => 'account/listAccounts', 
        'columns' => array(
            'standardFields' => array('Account ID', 'First Name', 'Last Name', 'Gender', 'City', 'State', 'Country', 'DOB Year', 'DOB Day', 'DOB Month' ),
            'customFields' => array(108),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Account ID',
            'sortDirection' => 'DESC',
        ),
    );

    $search_season = array( 
        'method' => 'membership/listMemberships', 
        'columns' => array(
            'standardFields' => array('Account ID', 'Full Name (F)', 'Company Name', 'DOB Month', 'DOB Year', 'State', 'Membership Name', 'Membership Cost','Membership Expiration Date', 'Membership Start Date', 'Membership Enrollment Date' ),
            'customFields' => array(136,171),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Account ID',
            'sortDirection' => 'ASC',
        ),
    );

    // Standard API call "go" with example on how to fetch numbers for custom
    // field mapping.  Swap "Membership"  with "Account"
    /*
    $go2 = array( 
      'method' => 'common/listCustomFields', 
      'parameters' => array(
        'searchCriteria.component' => 'Account',
        ),
      );
    */

    // Use the following single line for complete list of accounts
    if(isset($_POST["searchCriteria"])) {
      $search_skier['criteria'][] = array( 'Account ID', 'NOT_BLANK', '');

      // While the form variable "reload" is set to true when checked, the valuation below
      // can be done as binary.  When not checked, it is not set so continue with the 
      // fetch by changes after the last pull
      if(!isset($_POST["reload"]) && isset($member_skier_date) && $member_skier_date != 'Never Processed') {
        $search_skier['criteria'][] = array( 'Account Last Modified Date', 'GREATER_THAN', $member_skier_date);
      }

      $search_season['criteria'][] = array( 'Account ID', 'NOT_BLANK', '');

      if(!isset($_POST["reload"]) && isset($member_season_date) && $member_season_date != 'Never Processed') {
        $search_season['criteria'][] = array( 'Account Last Modified Date', 'GREATER_THAN', $member_season_date);
      }
    }

    /**
     * Execute search
     **************************************************
     * If you need a reference pull to look at account structure, the follow is it
     * Use the php command print_r and print_r w/ array_keys to view
     * $result_go = $neon->go( array( 'method' => 'account/retrieveIndividualAccount', 'parameters' => array('accountId'=>29607)));
     */
    
    if ( !empty( $search_skier['criteria'] ) ) {
      $skier_load_count = 0; 
      $skier_update_count = 0;
      $skier_new_count = 0;
      $result_skier = $neon->search($search_skier);
      $skier_message = 'No skier results were pulled from NEON';
      $season_message = 'No season results were pulled from NEON';
      // Do one fetch as a sanity check.  Yes it's n+1 fetches. 
      if( isset($result_skier['page']['totalResults'] ) && $result_skier['page']['totalResults'] >= 1 ) {
        for ($currentPage = 1; $currentPage < $result_skier['page']['totalPage']; $currentPage++) {
          # reload the search array's current_Page every time
          $search_skier['page']['currentPage'] = $currentPage;
          $result_skier = $neon->search($search_skier);

          // Another for loop - so shoot me
          // We're using 200 per page. Not sure what is really optimal.
          for ($i = 0; $i < 200; $i++) {
            // I'm sure there is a simpler way to not fall off the last page
            // but this works and it's PHP - who really cares anyway
            if (isset($result_skier['searchResults'][$i])) {
              $load = load_member_skier($result_skier['searchResults'][$i]);
              if ($load > 0) {
                $skier_load_count++;
              }
              if ($load == 1) {
                $skier_update_count += 1;
              } else if ($load == 2) {
                $skier_new_count += 1;
              }
            }
          }
        }
        update_option('member_skier_date', date(DATE_RFC2822));
        $member_skier_date = date(DATE_RFC2822);
      } 

      $season_load_count = 0; 
      $season_update_count = 0;
      $season_new_count = 0;
      $result_season = $neon->search($search_season);
      // Do one fetch as a sanity check.  Yes it's n+1 fetches. 
      if( isset($result_season['page']['totalResults'] ) && $result_season['page']['totalResults'] >= 1 ) {
        for ($currentPage = 1; $currentPage < $result_season['page']['totalPage']; $currentPage++) {
          # reload the search array's current_Page every time
          $search_season['page']['currentPage'] = $currentPage;
          $result_season = $neon->search($search_season);

          // Another for loop - so shoot me
          // We're using 200 per page. Not sure what is really optimal.
          for ($i = 0; $i < 200; $i++) {
            // I'm sure there is a simpler way to not fall off the last page
            // but this works and it's PHP - who really cares anyway
            if (isset($result_season['searchResults'][$i])) {
              $load = load_member_season($result_season['searchResults'][$i]);
              if ($load > 0) {
                $season_load_count++;
              }
              if ($load == 1) {
                $season_update_count += 1;
              } else if ($load == 2) {
                $season_new_count += 1;
              }
            }
          }
        }
        update_option('member_season_date', date(DATE_RFC2822));
        $member_season_date = date(DATE_RFC2822);
      } 
    } else {
      $skier_message = 'Press SUBMIT to fetch from the NEON CRM and load the member tables';
    }
    
    // Fetch the custom fields if you need to reference them.  Use "print_r" to view look at results
    // $result_1 = $neon->go($go2);

    /* Logout - terminate API session with the server */
    $neon->go( array( 'method' => 'common/logout' ) );

  } else {
      $result_skier = null;
      $result_season = null;
      $skier_message = 'There was a problem connecting to NeonCRM.';
  }

  // Test fetches that can be printed out with print_r
  // print_r(array_keys($result_1)
  // print_r(array_keys($result_1['individualAccount'])
  // See NEON API/Developers PHP code example
  // $result_1 = $neon->go($go1);
  // $result_2 = $neon->go($go2);

  ?>

  <h1>NENSA Member Update From NEON</h1>
  </br>
  <form action=# method="POST" >
    <input type="hidden" name="searchCriteria" value=true/>
    <input type="checkbox" name="reload" value=true> Reload All Members</br></br>
    <input type="submit" class="button-primary" value="<?php _e('Load Member Tables', 'nensa_admin') ?>" /></br>
  </form>
  </br>
  <p><?php echo 'Date Last Loaded: ' . $member_skier_date; ?></p>
  <hr>

  <?php
  /**
   * Iterate through API results
   *******************************************/
  ?>
  <?php if( (isset($result_skier['page']['totalResults'] ) && $result_skier['page']['totalResults'] >= 1) || (isset($result_season['page']['totalResults'] ) && $result_season['page']['totalResults'] >= 1) ): ?>
    </br><?php print ($skier_load_count." members were processed for the member_skier table, ");
               print ($skier_update_count." members were updated and ".$skier_new_count." members were added.");?></br>
         <?php print ($season_load_count." members were processed for the member_season table, ");
               print ($season_update_count." members were updated and ".$season_new_count." members were added.");?></br>
  <?php else: ?>
      <p><?php echo $skier_message; ?></p>
  <?php endif; ?>

<?php
}


?>