<?php 


function load_member_season ($membership_row) {
  include ("connection.php");

  if ($membership_row['Membership Expiration Date'] < date("Y-m-d")) {
    return false;
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

  if (date("m") > 7) {
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
    return false;
  }

  $result = $conn->query("SELECT * FROM MEMBER_SEASON WHERE nensa_num='$nensa_num'");
  $num_rows = mysqli_num_rows($result);
  if ($num_rows > 0) {
    $sql = mysqli_query($conn, "UPDATE MEMBER_SEASON SET season='$season', member_status='$member_status', member_level='$member_level', age_group='$age_group', age_season='$age_season', club_name='$club_name' WHERE nensa_num='$nensa_num'");
  } else {
    $sql = mysqli_query($conn, "INSERT INTO MEMBER_SEASON (member_id, nensa_num, season, member_status, member_level, age_group, age_season, club_name) VALUES ('$member_id','$nensa_num', '$season', '$member_status', '$member_level','$age_group', '$age_season', '$club_name')");
  }


  //  The most likely failure is a duplicate entry with ussa_num
  if ($sql == 0) {
    print ($conn->error);
    return false;
  }

  return true;
}

function fetch_member_season_data() {

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

  /* Authenticate with the API */
  $loginResult = $neon->login($credentials);

  /* Upon successful authentication, proceed with building the search query */
  if ( isset( $loginResult['operationResult'] ) && $loginResult['operationResult'] == 'SUCCESS' ) {

    /**
     * Search Query 
     * Customer fields use ID.  136 = Age Group, 171 = Age at end of season
     * Use "go2" search to fetch list of custom field (uncomment)
     *************************************************/
    $currentPage = 1;
    $search = array( 
        'method' => 'membership/listMemberships', 
        'columns' => array(
            'standardFields' => array('Account ID', 'Full Name (F)', 'Company Name', 'DOB Month', 'DOB Year', 'State', 'Membership Name', 'Membership Cost','Membership Expiration Date', 'Membership Start Date', 'Membership Enrollment Date' ),
            'customFields' => array(136,171),
        ),
        'page' => array(
            'currentPage' => $currentPage,
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
      $search['criteria'][] = array( 'Account ID', 'NOT_BLANK', '');
    }

    /**
     * Execute search
     **************************************************/
    
    if ( !empty( $search['criteria'] ) ) {
      $load_count = 0; 
      $result = $neon->search($search);
      // Do one fetch as a sanity check.  Yes it's n+1 fetches. 
      if( isset($result['page']['totalResults'] ) && $result['page']['totalResults'] >= 1 ) {
        for ($currentPage = 1; $currentPage < $result['page']['totalPage']; $currentPage++) {
          # reload the search array's current_Page every time
          $search['page']['currentPage'] = $currentPage;
          $result = $neon->search($search);

          // Another for loop - so shoot me
          // We're using 200 per page. Not sure what is really optimal.
          for ($i = 0; $i < 200; $i++) {
            // I'm sure there is a simpler way to not fall off the last page
            // but this works and it's PHP - who really cares anyway
            if (isset($result['searchResults'][$i])) {
              $load = load_member_season($result['searchResults'][$i]);
              if ($load == true) {
                $load_count++;
              }
            }
          }
        }
        $message = 'Last Date Processed:' . date(DATE_RFC2822);
      } 
    } else {
      $message = 'Press SUBMIT to fetch from the NEON CRM and load the Member Season table';
    }

    //else {
    //  $message = 'Click the submit button to begin';
    //}
    
    // Fetch the custom fields if you need to reference them.  Use "print_r" to view look at results
    // $result_1 = $neon->go($go2);

    /* Logout - terminate API session with the server */
    $neon->go( array( 'method' => 'common/logout' ) );

  } else {
      $result = null;
      $message = 'There was a problem connecting to NeonCRM.';
  }

  ?>

  <h1>NENSA Member Season Update From NEON</h1>
  </br>
  <form action=# method="POST" >
    <input type="hidden" name="searchCriteria" value=true/>
    <input type="submit" class="button-primary" value="<?php _e('Load Member Season Table', 'nensa_admin') ?>" /></br>
  </form>
  </br>
  <p><?php echo 'Date Last Loaded: ' . date(DATE_RFC2822); ?></p>
  <hr>

  <?php
  /**
   * Iterate through API results
   *******************************************/
  ?>
  <?php if( isset($result['page']['totalResults'] ) && $result['page']['totalResults'] >= 1 ): ?>
    </br><?php print ($load_count." members were either updated or added into the member_season table"); ?></br>
  <?php else: ?>
      <p><?php echo $message; ?></p>
  <?php endif; ?>

<?php
}

function search_neon_for_racer() {

  /* Include the NeonCRM PHP Library */
  require_once('neon.php');

  /**
   * POST Data
   **********************************************/

  /* Retrieve and sanitize POST data */
  $arguments = array(
      'accountID' => FILTER_SANITIZE_SPECIAL_CHARS,
      'firstName' => FILTER_SANITIZE_SPECIAL_CHARS,
      'lastName'  => FILTER_SANITIZE_SPECIAL_CHARS,
      'email'     => FILTER_SANITIZE_EMAIL,
  );
  $searchCriteria = filter_input_array( INPUT_POST, $arguments );

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

  /* Authenticate with the API */
  $loginResult = $neon->login($credentials);

  /* Upon successful authentication, proceed with building the search query */
  if ( isset( $loginResult['operationResult'] ) && $loginResult['operationResult'] == 'SUCCESS' ) {

    /**
     * Search Query
     *************************************************/

    /* Formulate the search query */
    //membership/listMemberships   User Full Name (F)
    // 'method' => 'account/listAccounts',
    //             'standardFields' => array('Account ID', 'First Name', 'Last Name', 'Gender', 'Email 1', 'City', 'State' ),

    $search = array( 
        'method' => 'account/listAccounts', 
        'columns' => array(
            'standardFields' => array('Account ID', 'First Name', 'Last Name', 'Gender', 'Email 1', 'City', 'State' ),
            'customFields' => array(101,170,108),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Account ID',
            'sortDirection' => 'DESC',
        ),
    );

    $search1 = array( 
        'method' => 'membership/listMemberships', 
        'columns' => array(
            'standardFields' => array('Account ID', 'Full Name (F)', 'Company Name', 'DOB Month', 'DOB Year', 'State', 'Membership Name', 'Membership Cost','Membership Expiration Date', 'Membership Start Date', 'Membership Enrollment Date' ),
            'customFields' => array(136,171),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Account ID',
            'sortDirection' => 'DESC',
        ),
    );

    // Stanard API call "go" with example on how to fetch numbers for customer
    // field mapping.  Swap "Membership"  with "Account"
    $go2 = array( 
      'method' => 'common/listCustomFields', 
      'parameters' => array(
        'searchCriteria.component' => 'Account',
        ),
      );

    // Use the following single line for complete list of accounts
    // Be sure to comment out search array setup 3 lines down
    //$search['criteria'][] = array( 'Account ID', 'NOT_BLANK', '');
    $search1['criteria'][] = array( 'Last Name', 'NOT_BLANK', '');
    
    /* Some search criteria are variable based on our POST data. Add them to the query if applicable */
    
    if ( isset( $searchCriteria['accountID'] ) && !empty( $searchCriteria['accountID'] ) ) {
        $search['criteria'][] = array( 'Account ID', 'EQUAL', $searchCriteria['accountID'] );
    }
    if ( isset( $searchCriteria['firstName'] ) && !empty( $searchCriteria['firstName'] ) && isset( $searchCriteria['lastName'] ) && !empty( $searchCriteria['lastName'] )){
        $search['criteria'][] = array( 'First Name', 'EQUAL', $searchCriteria['firstName'] );
        $search['criteria'][] = array( 'Last Name', 'EQUAL', $searchCriteria['lastName'] );
    }
    

    /**
     * Execute search
     **************************************************/ 

    /* If there are search criteria present, execute the search query */

    $result = $neon->search($search1);
    $result_1 = $neon->go($go2);

    $message = 'Last Date Processed:' . date(DATE_RFC2822);
    

    /* Logout - terminate API session with the server */
    $neon->go( array( 'method' => 'common/logout' ) );

  } else {
      $result = null;
      $message = 'There was a problem connecting to NeonCRM.';
  }

  ?>



  <h1>NENSA Member Season Update From NEON</h1>
  </br>
  <form action=# method="POST" >
    <input type="submit" class="button-primary" value="<?php _e('Load Member Season Table', 'nensa_admin') ?>" /></br>
  </form>
  </br>
  <p><?php echo 'Date Last Loaded: ' . date(DATE_RFC2822); ?></p>
  <hr>

  <?php
  /**
   * Iterate through API results
   *******************************************/
  ?>
  <?php if( isset($result['page']['totalResults'] ) && $result['page']['totalResults'] >= 1 ): ?>
    </br><?php print ($result['page']['totalResults']); ?></br>
    </br><?php print_r(array_keys($result['searchResults'][0])); ?></br>
  </br><?php print_r($result['searchResults'][0]['Membership Expiration Date']); ?></br>
    </br><?php print(sizeof($result['searchResults'])); ?></br>
    </br><?php print_r($result_1); ?></br>
    </br><?php print_r(array_keys($result_1)); ?></br>
    </br><?php //print_r(array_keys($result_1['individualAccount'])); ?></br>
    </br><?php //print_r($result_1['individualAccount']['lastModifiedDateTime']); ?></br>
    </br><?php //print_r($result_1['individualAccount']['accountId']); ?></br>
    </br><?php //print_r(array_keys($result_1['individualAccount']['primaryContact'])); ?></br>
  <?php else: ?>
      <p><?php echo $message; ?></p>
  <?php endif; ?>

<?php
}
?>